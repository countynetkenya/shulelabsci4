<?php

namespace Tests\Feature\Admissions;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * AdmissionsWebTest - Feature tests for Admissions CRUD.
 */
class AdmissionsWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewAdmissionsIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('admissions');

        $response->assertOK();
        $response->assertSee('Admissions Applications');
        $response->assertSee('fa-user-plus');
    }

    public function testAdminCanViewCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('admissions/create');

        $response->assertOK();
        $response->assertSee('New Application');
        $response->assertSee('student_first_name');
        $response->assertSee('parent_email');
    }

    public function testAdminCanCreateApplication()
    {
        $data = [
            'student_first_name' => 'Test',
            'student_last_name' => 'Student',
            'student_dob' => '2012-01-01',
            'student_gender' => 'male',
            'class_applied' => 7,
            'parent_first_name' => 'Test',
            'parent_last_name' => 'Parent',
            'parent_email' => 'test@example.com',
            'parent_phone' => '+254700000000',
            'parent_relationship' => 'father',
            'academic_year' => '2024',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('admissions/store', $data);

        $response->assertRedirectTo('/admissions');
        $response->assertSessionHas('message');
    }

    public function testCreateApplicationValidatesRequiredFields()
    {
        $data = [
            'student_first_name' => 'Test',
            // Missing required fields
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('admissions/store', $data);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
    }

    public function testAdminCanViewEditForm()
    {
        // Create an application first
        $db = \Config\Database::connect();
        $applicationId = $db->table('applications')->insert([
            'school_id' => 1,
            'application_number' => 'TEST-001',
            'academic_year' => '2024',
            'class_applied' => 7,
            'student_first_name' => 'Edit',
            'student_last_name' => 'Test',
            'student_dob' => '2012-01-01',
            'student_gender' => 'male',
            'parent_first_name' => 'Test',
            'parent_last_name' => 'Parent',
            'parent_email' => 'edit@example.com',
            'parent_phone' => '+254700000001',
            'parent_relationship' => 'father',
            'status' => 'submitted',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->getAdminSession())
                         ->get("admissions/edit/{$db->insertID()}");

        $response->assertOK();
        $response->assertSee('Edit Application');
        $response->assertSee('edit@example.com');
    }

    public function testAdminCanUpdateApplication()
    {
        // Create an application first
        $db = \Config\Database::connect();
        $db->table('applications')->insert([
            'school_id' => 1,
            'application_number' => 'TEST-002',
            'academic_year' => '2024',
            'class_applied' => 7,
            'student_first_name' => 'Update',
            'student_last_name' => 'Test',
            'student_dob' => '2012-01-01',
            'student_gender' => 'male',
            'parent_first_name' => 'Test',
            'parent_last_name' => 'Parent',
            'parent_email' => 'update@example.com',
            'parent_phone' => '+254700000002',
            'parent_relationship' => 'father',
            'status' => 'submitted',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $applicationId = $db->insertID();

        $data = [
            'student_first_name' => 'Updated',
            'student_last_name' => 'Student',
            'class_applied' => 8,
            'parent_first_name' => 'Updated',
            'parent_last_name' => 'Parent',
            'parent_email' => 'updated@example.com',
            'parent_phone' => '+254700000002',
            'parent_relationship' => 'mother',
            'status' => 'under_review',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post("admissions/update/{$applicationId}", $data);

        $response->assertRedirectTo('/admissions');
        $response->assertSessionHas('message');
    }

    public function testAdminCanDeleteApplication()
    {
        // Create an application first
        $db = \Config\Database::connect();
        $db->table('applications')->insert([
            'school_id' => 1,
            'application_number' => 'TEST-003',
            'academic_year' => '2024',
            'class_applied' => 7,
            'student_first_name' => 'Delete',
            'student_last_name' => 'Test',
            'student_dob' => '2012-01-01',
            'student_gender' => 'male',
            'parent_first_name' => 'Test',
            'parent_last_name' => 'Parent',
            'parent_email' => 'delete@example.com',
            'parent_phone' => '+254700000003',
            'parent_relationship' => 'father',
            'status' => 'submitted',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $applicationId = $db->insertID();

        $response = $this->withSession($this->getAdminSession())
                         ->get("admissions/delete/{$applicationId}");

        $response->assertRedirectTo('/admissions');
        $response->assertSessionHas('message');
    }
}
