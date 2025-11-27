<?php

namespace Tests\Feature\Inventory;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Database\Seeds\InventoryV2Seeder;

class InventoryApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = null;
    protected $seed = InventoryV2Seeder::class;

    public function testInitiateTransfer()
    {
        // Get IDs from DB to be safe
        $item = $this->db->table('inventory_items')->get()->getRow();
        $warehouse = $this->db->table('inventory_locations')->where('name', 'Warehouse')->get()->getRow();
        $shop = $this->db->table('inventory_locations')->where('name', 'Shop')->get()->getRow();

        $data = [
            'item_id' => (int)$item->id,
            'from_location_id' => (int)$warehouse->id,
            'to_location_id' => (int)$shop->id,
            'quantity' => 10,
        ];

        $result = $this->withBody(json_encode($data))
                       ->withHeaders(['Content-Type' => 'application/json'])
                       ->post('api/inventory/transfers');

        $result->assertStatus(201);
        $result->assertJSONFragment(['message' => 'Transfer initiated successfully']);
        
        // Verify DB
        $transfer = $this->db->table('inventory_transfers')->get()->getRow();
        $this->assertEquals(10, $transfer->quantity);
        $this->assertEquals('pending', $transfer->status);
    }

    public function testConfirmTransfer()
    {
        // 1. Initiate first
        $item = $this->db->table('inventory_items')->get()->getRow();
        $warehouse = $this->db->table('inventory_locations')->where('name', 'Warehouse')->get()->getRow();
        $shop = $this->db->table('inventory_locations')->where('name', 'Shop')->get()->getRow();

        $data = [
            'item_id' => (int)$item->id,
            'from_location_id' => (int)$warehouse->id,
            'to_location_id' => (int)$shop->id,
            'quantity' => 10,
        ];

        $initResult = $this->withBody(json_encode($data))
                           ->withHeaders(['Content-Type' => 'application/json'])
                           ->post('api/inventory/transfers');
        
        $json = json_decode($initResult->getJSON());
        $transferId = $json->transfer_id;

        // 2. Confirm
        $result = $this->post("api/inventory/transfers/{$transferId}/confirm");

        $result->assertStatus(200);
        $result->assertJSONFragment(['message' => 'Transfer confirmed successfully']);

        // Verify DB
        $transfer = $this->db->table('inventory_transfers')->where('id', $transferId)->get()->getRow();
        $this->assertEquals('completed', $transfer->status);

        // Verify Stock
        $shopStock = $this->db->table('inventory_stock')
                        ->where('item_id', $item->id)
                        ->where('location_id', $shop->id)
                        ->get()->getRow();
        $this->assertEquals(10, $shopStock->quantity);
    }

    public function testListStock()
    {
        $result = $this->withHeaders(['Content-Type' => 'application/json'])
                       ->get('api/inventory/stock');

        $result->assertStatus(200);
        
        // Decode JSON to verify structure
        $json = json_decode($result->getJSON(), true);
        
        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
        $this->assertEquals('Math Book', $json[0]['item_name']);
        $this->assertEquals('Warehouse', $json[0]['location_name']);
    }
}
