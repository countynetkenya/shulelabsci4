<?php

namespace Tests\Inventory;

use App\Database\Seeds\InventoryV2Seeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Modules\Inventory\Models\InventoryItemModel;
use Modules\Inventory\Models\InventoryStockModel;
use Modules\Inventory\Services\InventoryService;
use Tests\Support\Traits\TenantTestTrait;

/**
 * @internal
 */
final class InventoryServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected $refresh = true;

    protected $namespace = 'App';

    protected InventoryService $service;

    protected InventoryItemModel $itemModel;

    protected InventoryStockModel $stockModel;

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
        // $this->seed(InventoryV2Seeder::class); // Removed: TenantTestTrait handles basic seeding, InventoryV2Seeder might conflict or be redundant if not scoped.
        // Actually, InventoryV2Seeder seeds categories and locations which TenantTestTrait does NOT.
        // So we should keep it, but ensure it uses the schoolId from TenantTestTrait if possible, or we update the seeder.
        // For now, let's assume InventoryV2Seeder is safe to run.
        $this->seed(InventoryV2Seeder::class);

        $this->service = new InventoryService();
        $this->itemModel = new InventoryItemModel();
        $this->stockModel = new InventoryStockModel();
    }

    public function testCreateItem(): void
    {
        $category = $this->db->table('inventory_categories')->get()->getRow();
        $categoryId = $category->id;

        $data = [
            'name' => 'Laptop HP',
            'sku' => 'LAPTOP-001',
            'category_id' => $categoryId,
            'description' => 'High performance laptop',
            'unit_cost' => 50000.00,
            'reorder_level' => 5,
            'type' => 'physical',
        ];

        $itemId = $this->service->createItem($data);

        $this->assertIsInt($itemId);
        $this->assertGreaterThan(0, $itemId);

        // Verify item
        $item = $this->itemModel->find($itemId);
        $this->assertEquals('Laptop HP', $item->name);
        $this->assertEquals('LAPTOP-001', $item->sku);
    }

    public function testGetItems(): void
    {
        $category = $this->db->table('inventory_categories')->get()->getRow();
        $categoryId = $category->id;

        $this->service->createItem([
            'name' => 'Desk',
            'sku' => 'DESK-001',
            'category_id' => $categoryId,
            'unit_cost' => 5000.00,
            'type' => 'physical',
        ]);

        $items = $this->service->getItems();

        $this->assertIsArray($items);
        $this->assertGreaterThan(0, count($items));
    }

    public function testAdjustStockIn(): void
    {
        $category = $this->db->table('inventory_categories')->get()->getRow();
        $categoryId = $category->id;

        $location = $this->db->table('inventory_locations')->where('is_default', 1)->get()->getRow();
        $locationId = $location->id;

        $user = $this->db->table('users')->get()->getRow();
        $userId = $user->id;

        $itemId = $this->service->createItem([
            'name' => 'Notebook',
            'sku' => 'NOTE-001',
            'category_id' => $categoryId,
            'unit_cost' => 50.00,
            'type' => 'physical',
        ]);

        $this->service->adjustStock($itemId, $locationId, 50, 'Restocking', $userId);

        $stock = $this->service->getStock($itemId, $locationId);
        $this->assertEquals(50.0, $stock);
    }

    public function testAdjustStockOut(): void
    {
        $category = $this->db->table('inventory_categories')->get()->getRow();
        $categoryId = $category->id;

        $location = $this->db->table('inventory_locations')->where('is_default', 1)->get()->getRow();
        $locationId = $location->id;

        $user = $this->db->table('users')->get()->getRow();
        $userId = $user->id;

        $itemId = $this->service->createItem([
            'name' => 'Pen',
            'sku' => 'PEN-001',
            'category_id' => $categoryId,
            'unit_cost' => 10.00,
            'type' => 'physical',
        ]);

        // Add initial stock
        $this->service->adjustStock($itemId, $locationId, 100, 'Initial', $userId);

        // Remove stock
        $this->service->adjustStock($itemId, $locationId, -30, 'Distribution', $userId);

        $stock = $this->service->getStock($itemId, $locationId);
        $this->assertEquals(70.0, $stock);
    }

    public function testCannotReduceStockBelowZero(): void
    {
        $category = $this->db->table('inventory_categories')->get()->getRow();
        $categoryId = $category->id;

        $location = $this->db->table('inventory_locations')->where('is_default', 1)->get()->getRow();
        $locationId = $location->id;

        $user = $this->db->table('users')->get()->getRow();
        $userId = $user->id;

        $itemId = $this->service->createItem([
            'name' => 'Marker',
            'sku' => 'MARK-001',
            'category_id' => $categoryId,
            'unit_cost' => 20.00,
            'type' => 'physical',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reduce stock below zero');

        $this->service->adjustStock($itemId, $locationId, -10, 'Bad Adjustment', $userId);
    }
}
