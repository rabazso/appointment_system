<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResetPasswordTokenController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        return response()->json([
        'token' => $token,
        'email' => $request->query('email'),
    ]);
    }
}
