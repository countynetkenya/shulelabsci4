<?php

namespace Tests\Feature\POS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class PosWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewPosDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('pos');

        $response->assertOK();
        $response->assertSee('Point of Sale');
    }

    public function testApiRegisters()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/pos/registers');

        $response->assertOK();
        $response->assertSee('Canteen Till 1');
    }
}
