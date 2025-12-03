<?php

namespace Tests\Feature\Wallets;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class WalletWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testUserCanViewWallet()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('wallets');

        $response->assertOK();
        $response->assertSee('My Wallet');
    }
}
