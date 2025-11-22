<?php

namespace Tests\Ci4\Integrations;

use CodeIgniter\Test\CIUnitTestCase;
use Modules\Integrations\Services\IntegrationService;
use Modules\Integrations\Services\Adapters\Storage\LocalStorageAdapter;

/**
 * Tests for the IntegrationService.
 */
class IntegrationServiceTest extends CIUnitTestCase
{
    private IntegrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IntegrationService();
    }

    public function testCanRegisterAdapter(): void
    {
        $adapter = new LocalStorageAdapter(['base_path' => WRITEPATH . 'uploads']);
        $this->service->register('local_storage', $adapter);

        $this->assertTrue($this->service->hasAdapter('local_storage'));
    }

    public function testCanGetRegisteredAdapter(): void
    {
        $adapter = new LocalStorageAdapter(['base_path' => WRITEPATH . 'uploads']);
        $this->service->register('local_storage', $adapter);

        $retrieved = $this->service->getAdapter('local_storage');

        $this->assertInstanceOf(LocalStorageAdapter::class, $retrieved);
    }

    public function testGetRegisteredAdaptersReturnsArray(): void
    {
        $adapter = new LocalStorageAdapter(['base_path' => WRITEPATH . 'uploads']);
        $this->service->register('local_storage', $adapter);

        $adapters = $this->service->getRegisteredAdapters();

        $this->assertIsArray($adapters);
        $this->assertContains('local_storage', $adapters);
    }

    public function testCheckHealthReturnsStatus(): void
    {
        $adapter = new LocalStorageAdapter(['base_path' => WRITEPATH . 'uploads']);
        $this->service->register('local_storage', $adapter);

        $health = $this->service->checkHealth('local_storage');

        $this->assertIsArray($health);
        $this->assertArrayHasKey('status', $health);
    }

    public function testThrowsExceptionForUnknownAdapter(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Integration adapter "unknown" not found');

        $this->service->getAdapter('unknown');
    }
}
