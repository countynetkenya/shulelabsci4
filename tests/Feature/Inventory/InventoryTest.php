<?php

namespace Tests\Feature\Inventory;

use App\Database\Seeds\InventoryV2Seeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class InventoryTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected $migrate = false;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        // Manual Cleanup
        $this->db->disableForeignKeyChecks();
        if ($this->db->tableExists('inventory_transfers')) {
            $this->db->table('inventory_transfers')->truncate();
        }
        if ($this->db->tableExists('inventory_stock')) {
            $this->db->table('inventory_stock')->truncate();
        }
        if ($this->db->tableExists('inventory_items')) {
            $this->db->table('inventory_items')->truncate();
        }
        if ($this->db->tableExists('inventory_categories')) {
            $this->db->table('inventory_categories')->truncate();
        }
        if ($this->db->tableExists('inventory_locations')) {
            $this->db->table('inventory_locations')->truncate();
        }
        $this->db->enableForeignKeyChecks();

        $this->setupTenantContext();
        $this->seed(InventoryV2Seeder::class);
    }

    public function testCanListInventoryItems()
    {
        $result = $this->get('/api/inventory/items');

        $result->assertStatus(200);

        // Since we seeded, we expect items
        $json = json_decode($result->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
    }

    public function testCanCreateInventoryItem()
    {
        // Get seeded category
        $category = $this->db->table('inventory_categories')->get()->getRow();
        $categoryId = $category->id;

        $data = [
            'category_id' => $categoryId,
            'name'        => 'Test Item',
            'sku'         => 'TEST-001',
            'type'        => 'physical',
            'unit_cost'   => 50.00,
        ];

        $result = $this->withBody(json_encode($data))
                       ->withHeaders(['Content-Type' => 'application/json'])
                       ->post('/api/inventory/items');

        $result->assertStatus(201);
    }
}
