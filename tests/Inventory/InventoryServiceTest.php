<?php

namespace Tests\Inventory;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\InventoryService;

/**
 * @internal
 */
final class InventoryServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
    }

    public function testAddAsset(): void
    {
        $result = $this->service->addAsset(6, 'Laptop HP', 'LAPTOP-001', 'Electronics', 5, 50000);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('asset_id', $result);

        // Verify asset
        $asset = model('App\Models\InventoryAssetModel')->find($result['asset_id']);
        $this->assertEquals('Laptop HP', $asset['asset_name']);
        $this->assertEquals(5, $asset['quantity']);
        $this->assertEquals(250000, $asset['total_value']);
    }

    public function testGetSchoolAssets(): void
    {
        $this->service->addAsset(6, 'Desk', 'DESK-001', 'Furniture', 20, 5000);
        $this->service->addAsset(6, 'Chair', 'CHAIR-001', 'Furniture', 40, 2000);

        $assets = $this->service->getSchoolAssets(6);

        $this->assertIsArray($assets);
        $this->assertGreaterThan(0, count($assets));
    }

    public function testGetSchoolAssetsByCategory(): void
    {
        $this->service->addAsset(6, 'Projector', 'PROJ-001', 'Electronics', 2, 30000);
        $this->service->addAsset(6, 'Table', 'TABLE-001', 'Furniture', 10, 8000);

        $electronics = $this->service->getSchoolAssets(6, 'Electronics');

        $this->assertIsArray($electronics);
        foreach ($electronics as $asset) {
            $this->assertEquals('Electronics', $asset['category']);
        }
    }

    public function testUpdateQuantityIn(): void
    {
        $assetResult = $this->service->addAsset(6, 'Notebook', 'NOTE-001', 'Stationery', 100, 50);
        $assetId = $assetResult['asset_id'];

        $result = $this->service->updateQuantity($assetId, 50, 'in', 'Restocking');

        $this->assertTrue($result['success']);
        $this->assertEquals(150, $result['new_quantity']);

        // Verify asset
        $asset = model('App\Models\InventoryAssetModel')->find($assetId);
        $this->assertEquals(150, $asset['quantity']);
    }

    public function testUpdateQuantityOut(): void
    {
        $assetResult = $this->service->addAsset(6, 'Pen', 'PEN-001', 'Stationery', 200, 10);
        $assetId = $assetResult['asset_id'];

        $result = $this->service->updateQuantity($assetId, 30, 'out', 'Distribution');

        $this->assertTrue($result['success']);
        $this->assertEquals(170, $result['new_quantity']);
    }

    public function testCannotReduceBelowZero(): void
    {
        $assetResult = $this->service->addAsset(6, 'Marker', 'MARK-001', 'Stationery', 10, 20);
        $assetId = $assetResult['asset_id'];

        $result = $this->service->updateQuantity($assetId, 20, 'out', 'Over distribution');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Insufficient quantity', $result['message']);
    }

    public function testGetAssetTransactions(): void
    {
        $assetResult = $this->service->addAsset(6, 'Book', 'BOOK-001', 'Library', 50, 500);
        $assetId = $assetResult['asset_id'];

        // Make transactions
        $this->service->updateQuantity($assetId, 20, 'in', 'Purchase');
        $this->service->updateQuantity($assetId, 10, 'out', 'Distribution');

        $transactions = $this->service->getAssetTransactions($assetId);

        $this->assertIsArray($transactions);
        $this->assertGreaterThanOrEqual(3, count($transactions)); // Initial + 2 updates
    }

    public function testGetLowStockItems(): void
    {
        $this->service->addAsset(6, 'Low Stock Item', 'LOW-001', 'Stationery', 5, 100);
        $this->service->addAsset(6, 'High Stock Item', 'HIGH-001', 'Stationery', 100, 100);

        $lowStock = $this->service->getLowStockItems(6, 10);

        $this->assertIsArray($lowStock);
        $this->assertGreaterThan(0, count($lowStock));
        
        foreach ($lowStock as $item) {
            $this->assertLessThanOrEqual(10, $item['quantity']);
        }
    }

    public function testGetInventoryStats(): void
    {
        $this->service->addAsset(6, 'Item 1', 'ITEM-001', 'Category A', 10, 1000);
        $this->service->addAsset(6, 'Item 2', 'ITEM-002', 'Category B', 20, 500);

        $stats = $this->service->getInventoryStats(6);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_items', $stats);
        $this->assertArrayHasKey('total_value', $stats);
        $this->assertArrayHasKey('by_category', $stats);
        $this->assertGreaterThan(0, $stats['total_items']);
    }

    public function testSearchAssets(): void
    {
        $this->service->addAsset(6, 'Dell Monitor 24 inch', 'MON-DELL-001', 'Electronics', 3, 15000);
        $this->service->addAsset(6, 'HP Printer', 'PRINT-HP-001', 'Electronics', 2, 20000);

        $results = $this->service->searchAssets(6, 'Dell');

        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));
    }

    public function testTransferAsset(): void
    {
        $assetResult = $this->service->addAsset(6, 'Transfer Item', 'TRANS-001', 'Electronics', 20, 1000);
        $assetId = $assetResult['asset_id'];

        $result = $this->service->transferAsset($assetId, 6, 7, 5);

        $this->assertTrue($result['success']);

        // Verify source decreased
        $sourceAsset = model('App\Models\InventoryAssetModel')->find($assetId);
        $this->assertEquals(15, $sourceAsset['quantity']);

        // Verify target has the asset
        $targetAsset = model('App\Models\InventoryAssetModel')
            ->forSchool(7)
            ->where('asset_code', 'TRANS-001')
            ->first();
        $this->assertNotNull($targetAsset);
        $this->assertEquals(5, $targetAsset['quantity']);
    }
}
