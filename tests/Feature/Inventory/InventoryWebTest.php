<?php

namespace Tests\Feature\Inventory;

use App\Database\Seeds\InventoryV2Seeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class InventoryWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected $migrate = false;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Manual Cleanup
        $this->db->disableForeignKeyChecks();
        if ($this->db->tableExists('inventory_transfers')) $this->db->table('inventory_transfers')->truncate();
        if ($this->db->tableExists('inventory_stock')) $this->db->table('inventory_stock')->truncate();
        if ($this->db->tableExists('inventory_items')) $this->db->table('inventory_items')->truncate();
        if ($this->db->tableExists('inventory_categories')) $this->db->table('inventory_categories')->truncate();
        if ($this->db->tableExists('inventory_locations')) $this->db->table('inventory_locations')->truncate();
        $this->db->enableForeignKeyChecks();

        $this->setupTenantContext();
        $this->seed(InventoryV2Seeder::class);
    }

    public function testStockList()
    {
        $session = $this->getAdminSession();
        $session['loggedin'] = true;

        $result = $this->withSession($session)
                       ->get('inventory/stock');

        $result->assertStatus(200);
        $result->assertSee('Stock Management');
        $result->assertSee('Math Book');
        $result->assertSee('Warehouse');
    }

    public function testTransferForm()
    {
        $session = $this->getAdminSession();
        $session['loggedin'] = true;

        $result = $this->withSession($session)
                       ->get('inventory/transfer');

        $result->assertStatus(200);
        $result->assertSee('Initiate Transfer');
        $result->assertSee('Math Book');
        $result->assertSee('Warehouse');
    }
}
