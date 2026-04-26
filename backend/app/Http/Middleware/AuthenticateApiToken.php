<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();
        if (!$bearerToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $apiToken = ApiToken::findValidByPlainText($bearerToken);
        if (!$apiToken || !$apiToken->user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->attributes->set('api_token', $apiToken);
        $request->setUserResolver(fn () => $apiToken->user);

        return $next($request);
    }
}
