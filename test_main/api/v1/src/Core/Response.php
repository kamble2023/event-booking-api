<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Thin wrapper that writes a JSON response and halts execution.
 */
final class Response
{
    /**
     * Send a JSON response.
     *
     * @param array $data   Data to encode.
     * @param int   $status HTTP status code (default 200).
     */
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
