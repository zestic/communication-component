<?php

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = new Symfony\Component\Dotenv\Dotenv();
    $dotenv->load(__DIR__ . '/../.env');
}

// Set default values for required environment variables if not set
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_NAME') ?: 'communication_development';
$dbUser = getenv('DB_USER') ?: 'postgres';
$dbPassword = getenv('DB_PASSWORD') ?: 'postgres';
$dbSchema = getenv('POSTGRES_SCHEMA') ?: 'communication_component';

// Get the absolute path to the project root
$projectRoot = realpath(__DIR__ . '/..');

// Create a temporary Phinx configuration file with expanded environment variables
$phinxConfig = <<<YAML
paths:
    migrations: '{$projectRoot}/db/migrations'
    seeds: '{$projectRoot}/db/seeds'

environments:
    default_migration_table: phinxlog
    default_environment: development

    development:
        adapter: pgsql
        host: {$dbHost}
        name: {$dbName}
        user: {$dbUser}
        pass: {$dbPassword}
        port: {$dbPort}
        charset: utf8
        schema: {$dbSchema}

version_order: creation
YAML;

$tempConfigFile = tempnam(sys_get_temp_dir(), 'phinx_');
file_put_contents($tempConfigFile, $phinxConfig);

// Run the seed
$seedName = $argv[1] ?? 'GenericCommunicationSeed';
$environment = $argv[2] ?? 'development';

$command = sprintf('vendor/bin/phinx seed:run -c %s -p yaml -e %s -s %s', $tempConfigFile, $environment, $seedName);
echo "Executing: $command\n";
passthru($command);

// Clean up
unlink($tempConfigFile);
