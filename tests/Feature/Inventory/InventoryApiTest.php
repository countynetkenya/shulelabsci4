<?php

namespace Tests\Feature\Inventory;

use App\Database\Seeds\InventoryV2Seeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class InventoryApiTest extends CIUnitTestCase
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
        if ($this->db->tableExists('audit_events')) {
            $this->db->table('audit_events')->truncate();
        }
        $this->db->enableForeignKeyChecks();

        $this->setupTenantContext();

        // Mock AuditService to bypass DB issues
        $auditMock = $this->getMockBuilder(\Modules\Foundation\Services\AuditService::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $auditMock->method('recordEvent')->willReturn(1);
        \Config\Services::injectMock('audit', $auditMock);

        $this->seed(InventoryV2Seeder::class);
    }

    public function testCanFetchInventoryItemsViaApi()
    {
        // 1. Call API
        $result = $this->withSession($this->getAdminSession())
                       ->get('/api/inventory/items');

        // 2. Verify Response
        $result->assertOK();

        // Check that the data array contains the seeded item
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertNotEmpty($json['data']);

        // Find the item in the data
        $item = null;
        foreach ($json['data'] as $i) {
            if ($i['sku'] === 'MATH-001') {
                $item = $i;
                break;
            }
        }

        $this->assertNotNull($item, 'Seeded item not found in response');
        $this->assertEquals('Math Book', $item['name']);
    }

    public function testInitiateTransfer()
    {
        // Get IDs from DB to be safe
        $item = $this->db->table('inventory_items')->get()->getRow();
        $warehouse = $this->db->table('inventory_locations')->where('name', 'Warehouse')->get()->getRow();
        $shop = $this->db->table('inventory_locations')->where('name', 'Shop')->get()->getRow();

        $data = [
            'item_id' => (int) $item->id,
            'from_location_id' => (int) $warehouse->id,
            'to_location_id' => (int) $shop->id,
            'quantity' => 10,
        ];

        $session = $this->getAdminSession();
        $session['loggedin'] = true;

        $result = $this->withSession($session)
                       ->withBody(json_encode($data))
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
            'item_id' => (int) $item->id,
            'from_location_id' => (int) $warehouse->id,
            'to_location_id' => (int) $shop->id,
            'quantity' => 10,
        ];

        $session = $this->getAdminSession();
        $session['loggedin'] = true;

        $initResult = $this->withSession($session)
                           ->withBody(json_encode($data))
                           ->withHeaders(['Content-Type' => 'application/json'])
                           ->post('api/inventory/transfers');

        $initResult->assertStatus(201);
        $json = json_decode($initResult->getJSON());
        $transferId = $json->transfer_id;

        // 2. Confirm
        $result = $this->withSession($session)
                       ->post("api/inventory/transfers/{$transferId}/confirm");

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
