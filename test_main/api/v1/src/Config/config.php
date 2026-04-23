<?php
declare(strict_types=1);

/**
 * Application configuration.
 *
 * Copy this file (do NOT commit credentials to version control).
 * Set DB credentials and adjust auth settings as needed.
 */
return [
    'db' => [
        'dsn'     => 'mysql:host=localhost;dbname=YOUR_DB;charset=utf8mb4',
        'user'    => 'YOUR_DB_USER',
        'pass'    => 'YOUR_DB_PASS',
        'options' => [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],

    // Non-JWT bearer token settings
    'auth' => [
        'token_ttl_seconds' => 60 * 60 * 24 * 7, // 7 days
    ],
];
