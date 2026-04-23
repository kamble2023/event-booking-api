<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Sanity-check: bootstrap paths must exist before loading the framework.
| This app is designed to be served from the public/ subdirectory while
| all application code lives one level up (e.g. api/v1/app/, api/v1/bootstrap/).
|--------------------------------------------------------------------------
*/
$autoloader = __DIR__.'/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Application dependencies not installed. Run: composer install']);
    exit(1);
}

$bootstrap = __DIR__.'/../bootstrap/app.php';
if (!file_exists($bootstrap)) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Application bootstrap file not found. Verify the directory structure.']);
    exit(1);
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require $autoloader;

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once $bootstrap;

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
