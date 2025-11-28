<?php

namespace Tests\Inventory;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Models\InventoryItemModel;
use Modules\Inventory\Models\InventoryStockModel;

/**
 * @internal
 */
final class InventoryServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace = 'App';

    protected InventoryService $service;
    protected InventoryItemModel $itemModel;
    protected InventoryStockModel $stockModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
        $this->itemModel = new InventoryItemModel();
        $this->stockModel = new InventoryStockModel();

        // Setup required data
        $db = \Config\Database::connect();
        
        // Create a category
        if ($db->tableExists('inventory_categories')) {
             if ($db->table('inventory_categories')->where('id', 1)->countAllResults() == 0) {
                 $db->table('inventory_categories')->insert(['id' => 1, 'name' => 'Electronics']);
             }
        }

        // Create a location
        if ($db->tableExists('inventory_locations')) {
            if ($db->table('inventory_locations')->where('id', 1)->countAllResults() == 0) {
                $db->table('inventory_locations')->insert([
                    'id' => 1, 
                    'name' => 'Main Store',
                    'is_default' => 1
                ]);
            }
        }
    }

    public function testCreateItem(): void
    {
        $data = [
            'name' => 'Laptop HP',
            'sku' => 'LAPTOP-001',
            'category_id' => 1,
            'description' => 'High performance laptop',
            'unit_cost' => 50000.00,
            'reorder_level' => 5,
            'type' => 'physical'
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
        $this->service->createItem([
            'name' => 'Desk',
            'sku' => 'DESK-001',
            'category_id' => 1,
            'unit_cost' => 5000.00,
            'type' => 'physical'
        ]);

        $items = $this->service->getItems();

        $this->assertIsArray($items);
        $this->assertGreaterThan(0, count($items));
    }

    public function testAdjustStockIn(): void
    {
        $itemId = $this->service->createItem([
            'name' => 'Notebook',
            'sku' => 'NOTE-001',
            'category_id' => 1,
            'unit_cost' => 50.00,
            'type' => 'physical'
        ]);

        $locationId = 1;
        $userId = 1;

        $this->service->adjustStock($itemId, $locationId, 50, 'Restocking', $userId);

        $stock = $this->service->getStock($itemId, $locationId);
        $this->assertEquals(50.0, $stock);
    }

    public function testAdjustStockOut(): void
    {
        $itemId = $this->service->createItem([
            'name' => 'Pen',
            'sku' => 'PEN-001',
            'category_id' => 1,
            'unit_cost' => 10.00,
            'type' => 'physical'
        ]);

        $locationId = 1;
        $userId = 1;

        // Add initial stock
        $this->service->adjustStock($itemId, $locationId, 100, 'Initial', $userId);

        // Remove stock
        $this->service->adjustStock($itemId, $locationId, -30, 'Distribution', $userId);

        $stock = $this->service->getStock($itemId, $locationId);
        $this->assertEquals(70.0, $stock);
    }

    public function testCannotReduceStockBelowZero(): void
    {
        $itemId = $this->service->createItem([
            'name' => 'Marker',
            'sku' => 'MARK-001',
            'category_id' => 1,
            'unit_cost' => 20.00,
            'type' => 'physical'
        ]);

        $locationId = 1;
        $userId = 1;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reduce stock below zero');

        $this->service->adjustStock($itemId, $locationId, -10, 'Bad Adjustment', $userId);
    }
}
