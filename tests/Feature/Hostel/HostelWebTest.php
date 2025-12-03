<?php

namespace Tests\Feature\Hostel;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class HostelWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewHostels()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('hostel/hostels');

        $response->assertOK();
        $response->assertSee('Hostel Management');
    }
}
