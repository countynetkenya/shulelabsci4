<?php

namespace Tests\Feature\Teacher;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class TeacherCrudTest extends CIUnitTestCase
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

    public function testIndexDisplaysTeachers()
    {
        $this->db->table('teachers')->insert([
            'school_id'  => $this->schoolId,
            'teacher_id' => null,
            'first_name' => 'Test',
            'last_name'  => 'Teacher',
            'employee_id' => 'TEST001',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())->get('teachers');
        $result->assertOK();
        $result->assertSee('Test Teacher');
    }

    public function testStoreCreatesTeacher()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('teachers/store', [
                           'first_name'  => 'New',
                           'last_name'   => 'Teacher',
                           'employee_id' => 'NEW001',
                           'status'      => 'active',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/teachers');
        $this->seeInDatabase('teachers', ['first_name' => 'New', 'school_id' => $this->schoolId]);
    }

    public function testUpdateModifiesTeacher()
    {
        $this->db->table('teachers')->insert([
            'school_id'  => $this->schoolId,
            'teacher_id' => null,
            'first_name' => 'Original',
            'last_name'  => 'Name',
            'employee_id' => 'UPD001',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $teacherId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("teachers/update/{$teacherId}", [
                           'first_name'  => 'Updated',
                           'last_name'   => 'Teacher',
                           'employee_id' => 'UPD001',
                           'status'      => 'active',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/teachers');
        $this->seeInDatabase('teachers', ['id' => $teacherId, 'first_name' => 'Updated']);
    }

    public function testDeleteRemovesTeacher()
    {
        $this->db->table('teachers')->insert([
            'school_id'  => $this->schoolId,
            'teacher_id' => null,
            'first_name' => 'Delete',
            'last_name'  => 'Me',
            'employee_id' => 'DEL001',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $teacherId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())->get("teachers/delete/{$teacherId}");
        $result->assertRedirectTo('/teachers');
        $this->dontSeeInDatabase('teachers', ['id' => $teacherId]);
    }
}
