<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\UserModel;
use App\Models\TokenModel;

/**
 * Handles authentication (login / logout).
 *
 * POST /auth/login  — issues a bearer token on valid credentials.
 * POST /auth/logout — revokes the supplied bearer token (protected route).
 *
 * Bug fix (client_id cannot be null):
 *   The original code called TokenModel::issueToken() with (int)$user['clientId']
 *   without first confirming the field is non-null in the DB row.  If the
 *   `clientuser.clientId` column holds NULL for an account (or the column is
 *   named differently and the lookup silently returns null), casting to int
 *   gives 0 and some PDO versions propagate that as SQL NULL for the
 *   api_tokens.client_id NOT NULL column.
 *
 *   Fix: explicitly verify the clientId is present and positive before
 *   issuing a token, and return a meaningful 422 response otherwise.
 */
final class AuthController
{
    public static function login(Request $req): void
    {
        // --- 1. Validate input ---
        try {
            $username = Validator::requireString($req->body, 'username', 1, 100);
            $password = Validator::requireString($req->body, 'password', 1, 200);
        } catch (\InvalidArgumentException $e) {
            Response::json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        // --- 2. Look up the user ---
        $user = UserModel::findActiveByUsername($username);
        if ($user === null || !UserModel::verifyPasswordLegacy($password, (string) ($user['Password'] ?? ''))) {
            Response::json(['success' => false, 'error' => 'Invalid username or password'], 401);
        }

        // --- 3. Guard: ensure clientId is present and valid ---
        // UserModel::findActiveByUsername aliases the column as 'clientId', so
        // the key is always 'clientId' here.  However the column value may be
        // NULL in the database for accounts that were created before the client
        // relationship was established.  Without this check the INSERT into
        // api_tokens would receive NULL (or 0 cast from null) for the NOT NULL
        // client_id column, producing the integrity-constraint violation.
        $clientId = isset($user['clientId']) && $user['clientId'] !== null
            ? (int) $user['clientId']
            : null;

        if ($clientId === null || $clientId <= 0) {
            Response::json([
                'success' => false,
                'error'   => 'Account configuration error: no client_id associated with this user. Contact your administrator.',
            ], 422);
        }

        // --- 4. Issue bearer token ---
        $config = require __DIR__ . '/../Config/config.php';
        $ttl    = (int) ($config['auth']['token_ttl_seconds'] ?? 86400);

        $issued = TokenModel::issueToken($clientId, (int) $user['Id'], $ttl);

        Response::json([
            'success'      => true,
            'token_type'   => 'Bearer',
            'access_token' => $issued['token'],
            'expires_at'   => $issued['expires_at'],
            'user'         => [
                'id'        => (int) $user['Id'],
                'client_id' => $clientId,
                'username'  => $user['Username'],
                'level'     => $user['level_dropdown'] ?? null,
                'email'     => $user['user_email']     ?? null,
                'phone'     => $user['phone']          ?? null,
            ],
        ], 200);
    }

    public static function logout(Request $req): void
    {
        $token = $req->bearerToken();
        if ($token !== null) {
            TokenModel::revoke($token);
        }
        Response::json(['success' => true, 'message' => 'Logged out'], 200);
    }
}
