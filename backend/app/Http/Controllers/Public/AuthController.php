<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use App\Http\Requests\Public\LoginRequest;
use App\Http\Requests\Public\RegisterRequest;
use App\Http\Resources\Auth\CustomerResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\ApiToken;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email']
        ]);

        event(new Registered($user));

        return response()->json([
            'user' => new UserResource($user),
            'customer' => new CustomerResource($customer),
            'message' => 'Registered. Please verify your email address before logging in.',
        ], 201);
    }

    public function adminLogin(LoginRequest $request){
        return $this->loginByRole($request, 'admin');
    }

    public function employeeLogin(LoginRequest $request){
        return $this->loginByRole($request, 'employee');
    }

    public function customerLogin(LoginRequest $request){
        return $this->loginByRole($request, 'customer');
    }

    private function loginByRole(LoginRequest $request, string $requiredRole)
    {
        $data = $request->validated();

        $user = User::with(['customer', 'employee'])->where('email', $data['email'])->first();

        if (!$user || !$user->password || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if($user->role !== $requiredRole) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Email address is not verified. A new verification link was sent.',
            ], 403);
        }

        $token = ApiToken::issueForUser($user, now()->addDay());

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'display_name' => $user->customer?->name ?? $user->employee?->name ?? 'Admin',
            'message' => 'Logged in',
        ]);
    }

    public function logout(Request $request)
    {
        $apiToken = $request->attributes->get('api_token');
        if ($apiToken) {
            $apiToken->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        if (!$request->hasValidSignature(false)) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'This verification link is invalid or has expired.',
            ], 403);
        }

        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'This verification link is invalid or has expired.',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'already_verified',
                'message' => 'Your email is already verified.',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'status' => 'verified',
            'message' => 'Your email has been verified successfully.',
        ]);
    }

}
