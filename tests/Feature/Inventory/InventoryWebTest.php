<?php

namespace Tests\Feature\Inventory;

use App\Database\Seeds\InventoryV2Seeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class InventoryWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = null;

    protected $seed = InventoryV2Seeder::class;

    public function testStockList()
    {
        // Simulate logged in user
        $result = $this->withSession(['loggedin' => true, 'user_id' => 1])
                       ->get('inventory/stock');

        $result->assertStatus(200);
        $result->assertSee('Stock Management');
        $result->assertSee('Math Book');
        $result->assertSee('Warehouse');
    }

    public function testTransferForm()
    {
        $result = $this->withSession(['loggedin' => true, 'user_id' => 1])
                       ->get('inventory/transfer');

        $result->assertStatus(200);
        $result->assertSee('Initiate Transfer');
        $result->assertSee('Math Book'); // In the select options
        $result->assertSee('Warehouse'); // In the select options
    }
}
