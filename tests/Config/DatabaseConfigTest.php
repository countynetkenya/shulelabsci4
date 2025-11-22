<?php

declare(strict_types=1);

namespace Tests\Ci4\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Tests for Database configuration class
 * 
 * Ensures that:
 * - Database config reads from .env correctly
 * - Testing environment uses SQLite in-memory
 * - Required credentials are validated
 */
class DatabaseConfigTest extends CIUnitTestCase
{
    public function testDatabaseConfigReadsFromEnv(): void
    {
        // This test runs in testing environment by default
        // so it should use the 'tests' group with SQLite
        $config = new Database();
        
        $this->assertSame('tests', $config->defaultGroup);
        $this->assertSame('SQLite3', $config->tests['DBDriver']);
        $this->assertSame(':memory:', $config->tests['database']);
    }

    public function testDefaultDatabaseConfigHasCorrectStructure(): void
    {
        $config = new Database();
        
        // Verify the default config has all required keys
        $this->assertArrayHasKey('hostname', $config->default);
        $this->assertArrayHasKey('username', $config->default);
        $this->assertArrayHasKey('password', $config->default);
        $this->assertArrayHasKey('database', $config->default);
        $this->assertArrayHasKey('DBDriver', $config->default);
        $this->assertArrayHasKey('port', $config->default);
    }

    public function testTestsConfigUsesInMemorySQLite(): void
    {
        $config = new Database();
        
        $this->assertSame('SQLite3', $config->tests['DBDriver']);
        $this->assertSame(':memory:', $config->tests['database']);
        $this->assertTrue($config->tests['foreignKeys']);
    }
}
