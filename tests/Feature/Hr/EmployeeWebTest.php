<?php

namespace Tests\Feature\Hr;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Modules\Hr\Models\EmployeeModel;
use Tests\Support\Traits\TenantTestTrait;

class EmployeeWebTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected $migrateOnce = true;
    // protected $seedOnce = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testIndexPage()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('/hr/employees');

        $result->assertOK();
        $result->assertSee('Employees');
        $result->assertSee('Add Employee');
    }

    public function testCreatePage()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('/hr/employees/create');

        $result->assertOK();
        $result->assertSee('Add Employee');
        $result->assertSee('Basic Salary');
    }

    public function testStoreEmployee()
    {
        $data = [
            'user_id' => $this->userId,
            'employee_number' => 'EMP001',
            'join_date' => '2023-01-01',
            'basic_salary' => 5000.00,
            'status' => 'active',
            'employment_type' => 'permanent',
            'department_id' => 1,
            'designation_id' => 1,
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('/hr/employees/create', $data);

        $result->assertRedirectTo('/hr/employees');

        $model = new EmployeeModel();
        $employee = $model->where('employee_number', 'EMP001')->first();
        $this->assertNotNull($employee);
        $this->assertEquals(5000.00, $employee['basic_salary']);
    }

    public function testEditPage()
    {
        $model = new EmployeeModel();
        $id = $model->insert([
            'school_id' => $this->schoolId,
            'user_id' => 124,
            'employee_number' => 'EMP002',
            'join_date' => '2023-02-01',
            'basic_salary' => 6000.00,
            'status' => 'active',
            'employment_type' => 'contract',
        ]);

        if ($id === false) {
            $this->fail('Failed to insert employee: ' . json_encode($model->errors()));
        }

        $result = $this->withSession($this->getAdminSession())
                       ->get("/hr/employees/edit/$id");

        $result->assertOK();
        $result->assertSee('Edit Employee');
        $result->assertSee('EMP002');
    }

    public function testUpdateEmployee()
    {
        $model = new EmployeeModel();
        $id = $model->insert([
            'school_id' => $this->schoolId,
            'user_id' => 125,
            'employee_number' => 'EMP003',
            'join_date' => '2023-03-01',
            'basic_salary' => 7000.00,
            'status' => 'active',
            'employment_type' => 'permanent',
        ]);

        if ($id === false) {
            $this->fail('Failed to insert employee: ' . json_encode($model->errors()));
        }

        $data = [
            'user_id' => 125,
            'employee_number' => 'EMP003-UPDATED',
            'join_date' => '2023-03-01',
            'basic_salary' => 7500.00,
            'status' => 'active',
            'employment_type' => 'permanent',
            'department_id' => 2,
            'designation_id' => 2,
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post("/hr/employees/edit/$id", $data);

        $result->assertRedirectTo('/hr/employees');

        $employee = $model->find($id);
        $this->assertEquals('EMP003-UPDATED', $employee['employee_number']);
        $this->assertEquals(7500.00, $employee['basic_salary']);
    }

    public function testDeleteEmployee()
    {
        $model = new EmployeeModel();
        $id = $model->insert([
            'school_id' => $this->schoolId,
            'user_id' => 126,
            'employee_number' => 'EMP004',
            'join_date' => '2023-04-01',
            'basic_salary' => 8000.00,
            'status' => 'active',
            'employment_type' => 'permanent',
        ]);

        if ($id === false) {
            $this->fail('Failed to insert employee: ' . json_encode($model->errors()));
        }

        $result = $this->withSession($this->getAdminSession())
                       ->get("/hr/employees/delete/$id");

        $result->assertRedirectTo('/hr/employees');

        $employee = $model->find($id);
        $this->assertNull($employee);
    }
}
