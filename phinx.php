<?php

declare(strict_types=1);

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Load test environment variables from .env.test file if it exists
if (file_exists(__DIR__ . '/.env.test')) {
    $lines = file(__DIR__ . '/.env.test', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations/postgres',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'communication_production',
            'user' => getenv('DB_USER') ?: 'postgres',
            'pass' => getenv('DB_PASSWORD') ?: 'postgres',
            'port' => (int) (getenv('DB_PORT') ?: 5432),
            'charset' => 'utf8',
            'schema' => getenv('POSTGRES_SCHEMA') ?: 'communication_component',
        ],
        'development' => [
            'adapter' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'communication_development',
            'user' => getenv('DB_USER') ?: 'postgres',
            'pass' => getenv('DB_PASSWORD') ?: 'postgres',
            'port' => (int) (getenv('DB_PORT') ?: 5432),
            'charset' => 'utf8',
            'schema' => getenv('POSTGRES_SCHEMA') ?: 'communication_component',
        ],
        'testing' => [
            'adapter' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'communication_test',
            'user' => getenv('DB_USER') ?: 'postgres',
            'pass' => getenv('DB_PASSWORD') ?: 'postgres',
            'port' => (int) (getenv('DB_PORT') ?: 5432),
            'charset' => 'utf8',
            'schema' => getenv('POSTGRES_SCHEMA') ?: 'communication_component',
        ],
    ],
    'version_order' => 'creation',
];
