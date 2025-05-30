<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Base test case for integration tests that loads environment variables from .env file
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * Set up environment variables from .env file
     */
    public static function setUpBeforeClass(): void
    {
        // Load environment variables from .env file if it exists
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            (new Dotenv())
                ->usePutenv(true)
                ->load($envFile);
        }

        // Load environment variables from .env.test file if it exists (for test-specific overrides)
        $testEnvFile = __DIR__ . '/../../.env.test';
        if (file_exists($testEnvFile)) {
            $dotenv = new Dotenv();
            $dotenv->load($testEnvFile);
        }
    }
}
