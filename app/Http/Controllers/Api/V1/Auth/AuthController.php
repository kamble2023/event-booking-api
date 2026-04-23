<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Number of minutes before the issued token expires.
     */
    private const TOKEN_TTL_MINUTES = 60;

    /**
     * Authenticate a user and issue a bearer token.
     *
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Revoke existing tokens for this user (single active token per user)
        ApiToken::where('user_id', $user->id)->delete();

        $plainToken = bin2hex(random_bytes(32));

        ApiToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
        ]);

        return response()->json([
            'token_type'   => 'Bearer',
            'access_token' => $plainToken,
            'expires_in'   => self::TOKEN_TTL_MINUTES * 60,
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }
}
