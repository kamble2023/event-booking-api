<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * Simple health-check endpoint (protected — requires a valid bearer token).
 *
 * GET /health — returns 200 OK with the current auth context.
 */
final class HealthController
{
    public static function health(Request $req): void
    {
        Response::json([
            'success' => true,
            'message' => 'OK',
            'auth'    => $req->attributes['auth'] ?? null,
        ]);
    }
}
