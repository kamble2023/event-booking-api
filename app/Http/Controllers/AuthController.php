<?php

namespace App\Http\Controllers;

use App\Models\ClientDeviceToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a login request and issue a device token.
     *
     * Fixes: SQLSTATE[23000] Column 'client_id' cannot be null
     * The fix ensures client_id is validated to be non-null on the user record
     * before any token insert is attempted.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (empty($user->client_id)) {
            return response()->json([
                'success' => false,
                'message' => 'User account is not associated with a client. Please contact support.',
            ], 422);
        }

        $token = ClientDeviceToken::create([
            'client_id'   => $user->client_id,
            'user_id'     => $user->id,
            'token'       => hash('sha256', Str::random(40)),
            'device_type' => $request->input('device_type'),
            'status'      => 'Active',
            'expires_at'  => now()->addDay(),
        ]);

        return response()->json([
            'success'      => true,
            'token_type'   => 'bearer',
            'access_token' => $token->token,
            'expires_at'   => $token->expires_at,
            'user'         => [
                'id'        => $user->id,
                'client_id' => $user->client_id,
                'name'      => $user->name,
                'email'     => $user->email,
            ],
        ], 200);
    }
}
