<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Immutable value-object that represents an incoming HTTP request.
 */
final class Request
{
    public string $method;
    public string $path;
    public array  $query      = [];
    public array  $headers    = [];
    public array  $body       = [];
    public array  $params     = []; // populated by Router (URL segments like {id})
    public array  $attributes = []; // shared data set by middleware (e.g. auth context)

    /**
     * Build a Request from PHP superglobals.
     */
    public static function fromGlobals(): self
    {
        $r         = new self();
        $r->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $r->path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $r->query   = $_GET ?? [];
        $r->headers = self::collectHeaders();

        $raw         = (string) (file_get_contents('php://input') ?: '');
        $contentType = $r->headers['content-type'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($raw, true);
            $r->body = is_array($decoded) ? $decoded : [];
        } else {
            $r->body = $_POST ?? [];
        }

        return $r;
    }

    /**
     * Extract a Bearer token from the Authorization header.
     */
    public function bearerToken(): ?string
    {
        $auth = $this->headers['authorization'] ?? '';
        if ($auth === '') {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private static function collectHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name            = strtolower(str_replace('_', '-', substr($k, 5)));
                $headers[$name]  = $v;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        return $headers;
    }
}
