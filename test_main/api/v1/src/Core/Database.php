<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PDO singleton.  The connection is created once and reused.
 */
final class Database
{
    private static ?\PDO $pdo = null;

    public static function pdo(): \PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../Config/config.php';
        $db     = $config['db'];

        self::$pdo = new \PDO(
            $db['dsn'],
            $db['user'],
            $db['pass'],
            $db['options'] ?? []
        );

        return self::$pdo;
    }
}
