<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BearerTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * Validates the `Authorization: Bearer <token>` header against the
     * api_tokens table and rejects requests with missing, invalid, or
     * expired tokens.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (! str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'message' => 'Unauthenticated. Bearer token required.',
            ], 401);
        }

        $plainToken = substr($authHeader, 7);

        $apiToken = ApiToken::where('token', hash('sha256', $plainToken))->first();

        if (! $apiToken) {
            return response()->json([
                'message' => 'Invalid token.',
            ], 401);
        }

        if ($apiToken->isExpired()) {
            $apiToken->delete();

            return response()->json([
                'message' => 'Token has expired.',
            ], 401);
        }

        $apiToken->update(['last_used_at' => now()]);

        $request->setUserResolver(fn () => $apiToken->user);

        return $next($request);
    }
}
