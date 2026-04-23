<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HealthController;
use App\Middleware\AuthMiddleware;

/**
 * API route definitions.
 *
 * The $router variable is injected by Bootstrap::run() before this file is
 * required.  Do NOT use a return-value or declare $router here.
 *
 * Base URL:  https://dev.testsol.local/dev/test_main/api/v1
 *
 * If your server is configured to forward requests from a sub-directory (e.g.
 * /dev/test_main/api/v1) directly to this index.php, set $prefix to '' (empty
 * string).  If the full path is visible to PHP, include it in $prefix so the
 * Router can match correctly.
 *
 * Example Apache VirtualHost (or sub-directory alias) that strips the prefix:
 *   Alias /dev/test_main/api/v1 /path/to/test_main/api/v1/public
 *   <Directory /path/to/test_main/api/v1/public>
 *       AllowOverride All
 *   </Directory>
 *
 * In that case set $prefix = '' below.  Otherwise set it to the full sub-path
 * visible in REQUEST_URI, e.g. '/dev/test_main/api/v1'.
 */

$prefix = '/dev/test_main/api/v1';

// --------------------------------------------------------------------------
// Auth routes (public — no token required)
// --------------------------------------------------------------------------
$router->add('POST', $prefix . '/auth/login',  [AuthController::class, 'login']);

// Logout requires a valid token so it can be revoked
$router->add('POST', $prefix . '/auth/logout', [AuthController::class, 'logout'], [
    [AuthMiddleware::class, 'requireToken'],
]);

// --------------------------------------------------------------------------
// Health check (protected)
// --------------------------------------------------------------------------
$router->add('GET', $prefix . '/health', [HealthController::class, 'health'], [
    [AuthMiddleware::class, 'requireToken'],
]);
