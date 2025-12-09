<?php

namespace Tests\Feature\Student;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * StudentCrudTest - Feature tests for Student CRUD operations.
 *
 * Tests all CRUD endpoints for the Student module
 */
class StudentCrudTest extends CIUnitTestCase
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

    /**
     * Test: Index page displays students.
     */
    public function testIndexDisplaysStudents()
    {
        // Seed a test student
        $this->db->table('students')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => null,
            'first_name'       => 'Test',
            'last_name'        => 'Student',
            'admission_number' => 'TEST001',
            'gender'           => 'male',
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('students');

        $result->assertOK();
        $result->assertSee('Test Student');
        $result->assertSee('TEST001');
    }

    /**
     * Test: Index page shows empty state when no students.
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('students');

        $result->assertOK();
        $result->assertSee('No students found');
    }

    /**
     * Test: Create page displays form.
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('students/create');

        $result->assertOK();
        $result->assertSee('Add New Student');
        $result->assertSee('First Name');
        $result->assertSee('Last Name');
    }

    /**
     * Test: Store creates a new student.
     */
    public function testStoreCreatesStudent()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('students/store', [
                           'first_name'       => 'New',
                           'last_name'        => 'Student',
                           'admission_number' => 'NEW001',
                           'gender'           => 'female',
                           'status'           => 'active',
                           csrf_token()       => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/students');

        $this->seeInDatabase('students', [
            'first_name' => 'New',
            'last_name'  => 'Student',
            'school_id'  => $this->schoolId,
        ]);
    }

    /**
     * Test: Store validation fails with missing required fields.
     */
    public function testStoreValidationFailsWithMissingFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('students/store', [
                           'first_name' => '', // Empty
                           'last_name'  => '', // Empty
                           csrf_token() => csrf_hash(),
                       ]);

        // Should redirect back with errors
        $result->assertRedirect();
    }

    /**
     * Test: Edit page displays student data.
     */
    public function testEditPageDisplaysStudentData()
    {
        // Seed a test student
        $this->db->table('students')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => null,
            'first_name'       => 'Edit',
            'last_name'        => 'Test',
            'admission_number' => 'EDIT001',
            'gender'           => 'male',
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("students/edit/{$studentId}");

        $result->assertOK();
        $result->assertSee('Edit Student');
        $result->assertSee('Edit');
        $result->assertSee('Test');
    }

    /**
     * Test: Update modifies existing student.
     */
    public function testUpdateModifiesStudent()
    {
        // Seed a test student
        $this->db->table('students')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => null,
            'first_name'       => 'Original',
            'last_name'        => 'Name',
            'admission_number' => 'UPD001',
            'gender'           => 'male',
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("students/update/{$studentId}", [
                           'first_name'       => 'Updated',
                           'last_name'        => 'Student',
                           'admission_number' => 'UPD001',
                           'gender'           => 'female',
                           'status'           => 'active',
                           csrf_token()       => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/students');

        $this->seeInDatabase('students', [
            'id'         => $studentId,
            'first_name' => 'Updated',
            'last_name'  => 'Student',
        ]);
    }

    /**
     * Test: Delete removes a student.
     */
    public function testDeleteRemovesStudent()
    {
        // Seed a test student
        $this->db->table('students')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => null,
            'first_name'       => 'Delete',
            'last_name'        => 'Me',
            'admission_number' => 'DEL001',
            'gender'           => 'male',
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        // Verify student exists
        $this->seeInDatabase('students', ['id' => $studentId]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("students/delete/{$studentId}");

        $result->assertRedirectTo('/students');

        // Verify student is deleted
        $this->dontSeeInDatabase('students', ['id' => $studentId]);
    }

    /**
     * Test: Tenant scoping - cannot access other school's students.
     */
    public function testCannotAccessOtherSchoolStudents()
    {
        // Create a student for a different school
        $this->db->table('students')->insert([
            'school_id'        => 99999, // Different school
            'student_id'       => null,
            'first_name'       => 'Other',
            'last_name'        => 'School',
            'admission_number' => 'OTHER001',
            'gender'           => 'male',
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $otherStudentId = $this->db->insertID();

        // Try to edit it with our session (different school)
        $result = $this->withSession($this->getAdminSession())
                       ->get("students/edit/{$otherStudentId}");

        // Should redirect because student not found for this school
        $result->assertRedirectTo('/students');
    }
}
