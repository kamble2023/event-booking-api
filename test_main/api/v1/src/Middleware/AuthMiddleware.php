<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\TokenModel;

/**
 * Bearer-token authentication middleware.
 *
 * Attach this middleware to any route that requires authentication.
 * On success it injects an 'auth' key into Request::$attributes so
 * downstream controllers can read the authenticated client/user context
 * without touching the database again.
 *
 * Usage in Routes/api.php:
 *   $router->add('GET', $prefix . '/protected', [MyController::class, 'action'], [
 *       [AuthMiddleware::class, 'requireToken'],
 *   ]);
 */
final class AuthMiddleware
{
    /**
     * Abort with 401 unless a valid bearer token is present.
     *
     * Injects $req->attributes['auth'] with:
     *   - client_id (int)
     *   - user_id   (int|null)
     *   - token_id  (int)
     */
    public static function requireToken(Request $req): void
    {
        $token = $req->bearerToken();
        if ($token === null) {
            Response::json(['success' => false, 'error' => 'Missing bearer token'], 401);
        }

        $tokenRow = TokenModel::validate($token);
        if ($tokenRow === null) {
            Response::json(['success' => false, 'error' => 'Invalid or expired token'], 401);
        }

        $req->attributes['auth'] = [
            'client_id' => (int) $tokenRow['client_id'],
            'user_id'   => $tokenRow['user_id'] !== null ? (int) $tokenRow['user_id'] : null,
            'token_id'  => (int) $tokenRow['id'],
        ];
    }
}
