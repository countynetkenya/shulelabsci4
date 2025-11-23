<?php

declare(strict_types=1);

namespace Tests\Ci4\Services;

use App\Services\UserMigrationService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Tests for UserMigrationService.
 *
 * These tests verify the automatic user migration from CI3 tables to users
 */
class UserMigrationServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false; // We'll manage migrations manually for these tests

    protected UserMigrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserMigrationService();
    }

    public function testFindAndMigrateUserReturnsNullWhenUserNotFound(): void
    {
        // Attempt to migrate a non-existent user
        $result = $this->service->findAndMigrateUser('nonexistent.user');

        $this->assertNull($result);
    }

    public function testExtractSchoolIDHandlesNullSchoolID(): void
    {
        // Create a reflection of the protected method to test it
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractSchoolID');
        $method->setAccessible(true);

        $user = (object) ['username' => 'test'];
        $result = $method->invoke($this->service, $user);

        $this->assertNull($result);
    }

    public function testExtractSchoolIDReturnsStringWhenSet(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractSchoolID');
        $method->setAccessible(true);

        $user = (object) ['username' => 'test', 'schoolID' => '1,2,3'];
        $result = $method->invoke($this->service, $user);

        $this->assertSame('1,2,3', $result);
    }

    public function testExtractCreatedAtReturnsCurrentDateWhenNotSet(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractCreatedAt');
        $method->setAccessible(true);

        $user = (object) ['username' => 'test'];
        $result = $method->invoke($this->service, $user);

        // Should return a date string in Y-m-d H:i:s format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result);
    }

    public function testExtractCreatedAtUsesCreateDateWhenAvailable(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractCreatedAt');
        $method->setAccessible(true);

        $user = (object) ['username' => 'test', 'create_date' => '2023-01-15 10:30:00'];
        $result = $method->invoke($this->service, $user);

        $this->assertSame('2023-01-15 10:30:00', $result);
    }

    public function testExtractUpdatedAtPrefersModifyDate(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractUpdatedAt');
        $method->setAccessible(true);

        $user = (object) [
            'username' => 'test',
            'create_date' => '2023-01-15 10:30:00',
            'modify_date' => '2023-02-20 14:45:00',
        ];
        $result = $method->invoke($this->service, $user);

        $this->assertSame('2023-02-20 14:45:00', $result);
    }

    public function testExtractUpdatedAtFallsBackToCreateDate(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractUpdatedAt');
        $method->setAccessible(true);

        $user = (object) [
            'username' => 'test',
            'create_date' => '2023-01-15 10:30:00',
        ];
        $result = $method->invoke($this->service, $user);

        $this->assertSame('2023-01-15 10:30:00', $result);
    }
}
