<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' =>  $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token, 'message' => 'Registered']);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials']);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token, 'message' => 'Logged in']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function guest(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $data = $request->validate([
        'email' => [
            'required',
            'email',
            Rule::unique('users', 'email')->ignore(optional($user)->id),
        ],
        'phone' => [
            'nullable',
            'string',
            Rule::unique('users', 'phone')->ignore(optional($user)->id),
        ],
        ]);

        if (!$user) {
            $user = User::create([
                'name' => 'Guest',
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => null,
            ]);
        }

        $token = $user->createToken('guest-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token, 'message' => 'Guest reservation token']);
    }
}