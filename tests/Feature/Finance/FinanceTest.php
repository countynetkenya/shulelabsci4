<?php

namespace Tests\Feature\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class FinanceTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App'; // Or null to run all

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the database with test data
        $this->seed('Modules\Finance\Database\Seeds\FinanceSeeder');
    }

    public function testApiListInvoicesReturnsOk()
    {
        // We expect this to return 200 and contain the seeded data
        $result = $this->call('get', 'api/finance/invoices/1');

        $result->assertStatus(200);
        // Check if the JSON contains the specific amount (integer in JSON)
        $result->assertSee('15000');
    }

    public function testWebDashboardReturnsOk()
    {
        // We expect this to return 200 and show totals
        $result = $this->call('get', 'finance');
        $result->assertStatus(200);
        $result->assertSee('20000'); // Total Invoiced (15000 + 5000)
        $result->assertSee('10000'); // Total Collected
    }
}
