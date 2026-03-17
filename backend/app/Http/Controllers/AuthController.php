<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
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
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Registered. Please verify your email address before logging in.',
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !$user->password || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Email address is not verified. A new verification link was sent.',
            ], 403);
        }

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Logged in',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

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

    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }

    public function guest(GuestRequest $request)
    {
        $data = $request->validated();

        return response()->json([
            'guest' => [
                'name' => $data['name'],
                'email' => $data['email'],
            ],
            'message' => 'Guest details are valid.',
        ]);
    }
}
