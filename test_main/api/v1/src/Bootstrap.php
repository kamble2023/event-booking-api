<?php
declare(strict_types=1);

namespace App;

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

final class Bootstrap
{
    public static function run(): void
    {
        // CORS headers — adjust origins as needed for your environment
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $router = new Router();
        require __DIR__ . '/Routes/api.php';

        $request = Request::fromGlobals();

        try {
            $router->dispatch($request);
        } catch (\Throwable $e) {
            Response::json([
                'success' => false,
                'error'   => 'Internal Server Error',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }
}
