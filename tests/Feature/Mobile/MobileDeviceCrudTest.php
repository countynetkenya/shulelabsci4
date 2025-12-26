<?php

namespace Tests\Feature\Mobile;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * MobileDeviceCrudTest - Feature tests for Mobile Device CRUD operations
 */
class MobileDeviceCrudTest extends CIUnitTestCase
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

    public function testIndexDisplaysDevices()
    {
        $this->db->table('mobile_devices')->insert([
            'user_id' => $this->userId,
            'device_id' => 'test_device_001',
            'device_name' => 'Test iPhone',
            'device_type' => 'ios',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())->get('mobile/devices');
        $result->assertOK();
        $result->assertSee('Test iPhone');
    }

    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())->get('mobile/devices/create');
        $result->assertOK();
        $result->assertSee('Device ID');
    }

    public function testStoreCreatesDevice()
    {
        $data = [
            'device_id' => 'new_device_001',
            'device_name' => 'New Test Device',
            'device_type' => 'android',
        ];

        $result = $this->withSession($this->getAdminSession())->post('mobile/devices/store', $data);
        $result->assertRedirectTo('/mobile/devices');
        
        $device = $this->db->table('mobile_devices')->where('device_id', 'new_device_001')->get()->getRowArray();
        $this->assertNotNull($device);
        $this->assertEquals('android', $device['device_type']);
    }

    public function testUpdateModifiesDevice()
    {
        $deviceId = $this->db->table('mobile_devices')->insert([
            'user_id' => $this->userId,
            'device_id' => 'update_device_001',
            'device_name' => 'Original Name',
            'device_type' => 'ios',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $data = ['device_name' => 'Updated Name', 'is_active' => 0];
        $result = $this->withSession($this->getAdminSession())->post('mobile/devices/update/' . $deviceId, $data);
        $result->assertRedirectTo('/mobile/devices');
        
        $device = $this->db->table('mobile_devices')->where('id', $deviceId)->get()->getRowArray();
        $this->assertEquals('Updated Name', $device['device_name']);
        $this->assertEquals(0, $device['is_active']);
    }

    public function testDeleteRemovesDevice()
    {
        $deviceId = $this->db->table('mobile_devices')->insert([
            'user_id' => $this->userId,
            'device_id' => 'delete_device_001',
            'device_name' => 'Delete Test',
            'device_type' => 'web',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())->get('mobile/devices/delete/' . $deviceId);
        $result->assertRedirectTo('/mobile/devices');
        
        $device = $this->db->table('mobile_devices')->where('id', $deviceId)->get()->getRowArray();
        $this->assertNull($device);
    }
}
