<?php

namespace Tests\Feature\Transport;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class TransportCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testCanViewTransportIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('transport');

        $response->assertOK();
    }

    public function testCanViewTransportCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('transport/create');

        $response->assertOK();
    }

    public function testCanCreateTransportVehicle()
    {
        $data = [
            'registration_number' => 'KCA 999Z',
            'capacity' => '30',
            'driver_name' => 'Test Driver',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('transport/store', $data);

        $response->assertRedirectTo('/transport');
    }

    public function testCreateVehicleRequiresRegistrationNumber()
    {
        $data = [
            'capacity' => '30',
            'driver_name' => 'Test Driver',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('transport/store', $data);

        $response->assertRedirect();
    }

    public function testCanViewEditForm()
    {
        // First create a vehicle
        $vehicleId = $this->db->table('transport_vehicles')->insert([
            'school_id' => $this->schoolId,
            'registration_number' => 'TEST 123',
            'capacity' => 25,
            'driver_name' => 'Test Driver',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->getAdminSession())
                         ->get('transport/edit/' . $vehicleId);

        $response->assertOK();
    }

    public function testCanUpdateVehicle()
    {
        // First create a vehicle
        $vehicleId = $this->db->table('transport_vehicles')->insert([
            'school_id' => $this->schoolId,
            'registration_number' => 'OLD 123',
            'capacity' => 25,
            'driver_name' => 'Old Driver',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'registration_number' => 'NEW 123',
            'capacity' => '35',
            'driver_name' => 'New Driver',
            'status' => 'active',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('transport/update/' . $vehicleId, $data);

        $response->assertRedirectTo('/transport');
    }

    public function testCanDeleteVehicle()
    {
        // First create a vehicle
        $vehicleId = $this->db->table('transport_vehicles')->insert([
            'school_id' => $this->schoolId,
            'registration_number' => 'DEL 123',
            'capacity' => 25,
            'driver_name' => 'Delete Driver',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->getAdminSession())
                         ->get('transport/delete/' . $vehicleId);

        $response->assertRedirectTo('/transport');
    }
}
