<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap.
 *
 * phpunit.xml targets sqlite :memory:. When pdo_sqlite is unavailable on the host,
 * fall back to a dedicated MySQL testing database so the suite can still run.
 */

require dirname(__DIR__).'/vendor/autoload.php';

if (! extension_loaded('pdo_sqlite')) {
    $fallback = [
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '3306',
        'DB_DATABASE' => 'ai_assessment_testing',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => '',
        'DB_SOCKET' => '/var/run/mysqld/mysqld.sock',
        'DB_URL' => '',
    ];

    $envPath = dirname(__DIR__).'/.env';
    if (is_readable($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\"'");
            if (in_array($key, ['DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD'], true)) {
                $fallback[$key] = $value;
            }
        }
    }

    // Prefer unix socket for root@localhost on this host.
    if (in_array($fallback['DB_HOST'], ['localhost', '127.0.0.1'], true)
        && is_readable('/var/run/mysqld/mysqld.sock')) {
        $fallback['DB_HOST'] = 'localhost';
        $fallback['DB_SOCKET'] = '/var/run/mysqld/mysqld.sock';
    }

    foreach ($fallback as $key => $value) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    // Ensure dedicated testing database exists.
    try {
        $dsn = 'mysql:unix_socket='.$fallback['DB_SOCKET'];
        $pdo = new PDO($dsn, $fallback['DB_USERNAME'], $fallback['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `ai_assessment_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (Throwable) {
        // PHPUnit will surface connection errors if creation fails.
    }
}
