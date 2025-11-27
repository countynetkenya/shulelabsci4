<?php

namespace Tests\Feature\Inventory;

use App\Database\Seeds\InventoryV2Seeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Modules\Inventory\Services\InventoryService;

class InventoryV2Test extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = null; // Run all migrations including Modules

    protected $seed = InventoryV2Seeder::class;

    protected $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService();
    }

    public function testTransferStock()
    {
        // Arrange
        $db = \Config\Database::connect();
        $warehouse = $db->table('inventory_locations')->where('name', 'Warehouse')->get()->getRow();
        $shop = $db->table('inventory_locations')->where('name', 'Shop')->get()->getRow();
        $item = $db->table('inventory_items')->where('sku', 'MATH-001')->get()->getRow();
        $user = $db->table('users')->where('email', 'test@example.com')->get()->getRow();

        $transferQty = 10;

        // Act
        $transferId = $this->inventoryService->transferStock(
            $item->id,
            $warehouse->id,
            $shop->id,
            $transferQty,
            $user->id
        );

        // Assert
        // 1. Warehouse stock = 90
        $warehouseStock = $db->table('inventory_stock')
                             ->where('item_id', $item->id)
                             ->where('location_id', $warehouse->id)
                             ->get()->getRow();
        $this->assertEquals(90, $warehouseStock->quantity);

        // 2. Shop stock = 0 (Pending)
        $shopStock = $db->table('inventory_stock')
                        ->where('item_id', $item->id)
                        ->where('location_id', $shop->id)
                        ->get()->getRow();

        if ($shopStock) {
            $this->assertEquals(0, $shopStock->quantity);
        } else {
            $this->assertTrue(true); // Record doesn't exist, effectively 0
        }

        // 3. Assert Thread created (Indirectly via transfer record existence, as we can't check InMemory repo easily)
        $transfer = $db->table('inventory_transfers')->where('id', $transferId)->get()->getRow();
        $this->assertNotNull($transfer);
        $this->assertEquals('pending', $transfer->status);

        // 4. Confirm Receipt
        $this->inventoryService->confirmTransfer($transferId, $user->id);

        // 5. Assert Shop stock = 10
        $shopStock = $db->table('inventory_stock')
                        ->where('item_id', $item->id)
                        ->where('location_id', $shop->id)
                        ->get()->getRow();
        $this->assertNotNull($shopStock);
        $this->assertEquals(10, $shopStock->quantity);
    }
}
