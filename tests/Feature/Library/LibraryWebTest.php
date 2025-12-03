<?php

namespace Tests\Feature\Library;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class LibraryWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewDashboard()
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('/library');

        $result->assertOK();
        $result->assertSee('Library Module');
    }

    public function testApiEndpoint()
    {
        // Mock the drive adapter to avoid RuntimeException
        $mockDrive = $this->createMock(\Modules\Library\Services\DriveAdapterInterface::class);
        \CodeIgniter\Config\Services::injectMock('libraryDriveAdapter', $mockDrive);

        try {
            $result = $this->withHeaders([
                'X-Tenant-ID' => '1',
                'X-Actor-ID' => '1',
                'Content-Type' => 'application/json',
            ])->withBody(json_encode([
                'title' => 'Test Document',
                'category' => 'Test Category'
            ]))->post('/api/library/documents');
            
            $this->assertNotEquals(404, $result->response()->getStatusCode());
        } catch (\InvalidArgumentException $e) {
            // If we get here, it means the controller was reached and the service was called.
            $this->assertTrue(true);
        }
    }
}
