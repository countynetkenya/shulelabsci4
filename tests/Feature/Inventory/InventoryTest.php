<?php

namespace Tests\Feature\Inventory;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class InventoryTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable CSRF for testing API
        $filters = config('Filters');
        if (isset($filters->globals['before']['csrf'])) {
            unset($filters->globals['before']['csrf']);
        }
    }

    public function testCanListInventoryItems()
    {
        // This endpoint doesn't exist yet, so it should fail or return 404
        $result = $this->get('/api/inventory/items');

        // We expect a 200 OK response with a JSON structure
        $result->assertStatus(200);
        // ResourceController returns the array directly by default
        $result->assertJSONExact([]);
    }

    public function testCanCreateInventoryItem()
    {
        // Create a category first
        $categoryModel = new \Modules\Inventory\Models\InventoryCategoryModel();
        $categoryId = $categoryModel->insert(['name' => 'Test Category']);

        $data = [
            'category_id' => $categoryId,
            'name'        => 'Test Item',
            'sku'         => 'TEST-001',
            'type'        => 'physical',
            'quantity'    => 10,
            'unit_cost'   => 50.00
        ];

        $result = $this->withBody(json_encode($data))
                       ->withHeaders(['Content-Type' => 'application/json'])
                       ->post('/api/inventory/items');
        
        $result->assertStatus(201);
    }
}
