<?php

namespace Tests\Feature\Inventory;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class InventoryCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testIndexDisplaysItems()
    {
        $this->db->table('inventory_items')->insert([
            'school_id' => $this->schoolId,
            'category_id' => 1,
            'name' => 'Test Item',
            'sku' => 'TEST-001',
            'type' => 'physical',
            'unit_cost' => 100.00,
            'reorder_level' => 10,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('inventory');

        $result->assertOK();
        $result->assertSee('Test Item');
        $result->assertSee('TEST-001');
    }

    public function testCreateItem()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('inventory/store', [
                           'name' => 'New Item',
                           'sku' => 'NEW-001',
                           'type' => 'physical',
                           'unit_cost' => 200.00,
                           'reorder_level' => 5,
                           'description' => 'Description',
                           'csrf_test_name' => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/inventory');
        $this->seeInDatabase('inventory_items', ['name' => 'New Item', 'school_id' => $this->schoolId]);
    }

    public function testUpdateItem()
    {
        $this->db->table('inventory_items')->insert([
            'school_id' => $this->schoolId,
            'category_id' => 1,
            'name' => 'Old Item',
            'sku' => 'OLD-001',
            'type' => 'physical',
            'unit_cost' => 100.00,
            'reorder_level' => 10,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("inventory/update/$id", [
                           'name' => 'Updated Item',
                           'sku' => 'OLD-001',
                           'type' => 'physical',
                           'unit_cost' => 150.00,
                           'reorder_level' => 10,
                           'description' => 'Updated',
                           'csrf_test_name' => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/inventory');
        $this->seeInDatabase('inventory_items', ['id' => $id, 'name' => 'Updated Item']);
    }

    public function testDeleteItem()
    {
        $this->db->table('inventory_items')->insert([
            'school_id' => $this->schoolId,
            'category_id' => 1,
            'name' => 'Delete Me',
            'sku' => 'DEL-001',
            'type' => 'physical',
            'unit_cost' => 100.00,
            'reorder_level' => 10,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("inventory/delete/$id");

        $result->assertRedirectTo('/inventory');
        $this->dontSeeInDatabase('inventory_items', ['id' => $id]);
    }
}
