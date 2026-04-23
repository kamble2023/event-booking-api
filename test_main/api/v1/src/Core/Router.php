<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Simple front-router with middleware support.
 *
 * Routes are matched in registration order.  The first matching route wins.
 *
 * URL parameters are declared as {name} placeholders and are placed into
 * Request::$params after a successful match.
 *
 * Middleware callables receive the Request and must call Response::json() and
 * exit (or throw) to abort the request before the handler runs.
 */
final class Router
{
    /** @var list<array{method:string, pattern:string, handler:callable, middleware:list<callable>}> */
    private array $routes = [];

    /**
     * Register a route.
     *
     * @param string     $method     HTTP method (case-insensitive).
     * @param string     $pattern    URL pattern, e.g. /api/v1/users/{id}.
     * @param callable   $handler    Callable that receives a Request.
     * @param callable[] $middleware Optional list of middleware callables.
     */
    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Find and execute the first matching route.
     *
     * Sends a 404 JSON response when no route matches.
     */
    public function dispatch(Request $req): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $req->method) {
                continue;
            }

            $params = $this->matchPath($route['pattern'], $req->path);
            if ($params === null) {
                continue;
            }

            $req->params = $params;

            foreach ($route['middleware'] as $mw) {
                $mw($req);
            }

            ($route['handler'])($req);
            return;
        }

        Response::json([
            'success' => false,
            'error'   => 'Not Found',
            'path'    => $req->path,
        ], 404);
    }

    /**
     * Match a URL pattern against a concrete path.
     *
     * Returns an associative array of captured params on success, or null on
     * no match.  Only named {param} placeholders are captured; everything
     * else must match literally.
     *
     * @return array<string,string>|null
     */
    private function matchPath(string $pattern, string $path): ?array
    {
        // Convert {name} placeholders into named capture groups
        $regex = preg_replace(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            '(?P<$1>[^/]+)',
            $pattern
        );
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $m)) {
            return null;
        }

        // Keep only string keys (named captures)
        $params = [];
        foreach ($m as $k => $v) {
            if (is_string($k)) {
                $params[$k] = $v;
            }
        }
        return $params;
    }
}
