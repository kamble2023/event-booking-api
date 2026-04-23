<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight request-body validator.
 *
 * All methods throw \InvalidArgumentException on failure so controllers can
 * catch them in a single try/catch and return a 422 response.
 */
final class Validator
{
    /**
     * Require a non-empty string field within the given length bounds.
     *
     * @throws \InvalidArgumentException
     */
    public static function requireString(array $data, string $key, int $minLen = 1, int $maxLen = 255): string
    {
        $val = $data[$key] ?? null;
        if (!is_string($val)) {
            throw new \InvalidArgumentException("$key is required and must be a string");
        }
        $val = trim($val);
        if (mb_strlen($val) < $minLen) {
            throw new \InvalidArgumentException("$key must be at least $minLen character(s)");
        }
        if (mb_strlen($val) > $maxLen) {
            throw new \InvalidArgumentException("$key must be at most $maxLen character(s)");
        }
        return $val;
    }

    /**
     * Return an optional integer field, or null when the key is absent/empty.
     *
     * @throws \InvalidArgumentException
     */
    public static function optionalInt(array $data, string $key): ?int
    {
        if (!array_key_exists($key, $data) || $data[$key] === '' || $data[$key] === null) {
            return null;
        }
        if (is_int($data[$key])) {
            return $data[$key];
        }
        if (is_string($data[$key]) && ctype_digit($data[$key])) {
            return (int) $data[$key];
        }
        throw new \InvalidArgumentException("$key must be an integer");
    }

    /**
     * Require an integer field.
     *
     * @throws \InvalidArgumentException
     */
    public static function requireInt(array $data, string $key): int
    {
        $val = self::optionalInt($data, $key);
        if ($val === null) {
            throw new \InvalidArgumentException("$key is required and must be an integer");
        }
        return $val;
    }

    /**
     * Require a valid e-mail address.
     *
     * @throws \InvalidArgumentException
     */
    public static function requireEmail(array $data, string $key): string
    {
        $val = self::requireString($data, $key, 3, 320);
        if (filter_var($val, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException("$key must be a valid e-mail address");
        }
        return $val;
    }
}
