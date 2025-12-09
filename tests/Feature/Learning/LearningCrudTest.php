<?php

namespace Tests\Feature\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class LearningCrudTest extends CIUnitTestCase
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

    public function testIndexDisplaysCourses()
    {
        $this->db->table('learning_courses')->insert([
            'school_id'   => $this->schoolId,
            'teacher_id'  => $this->userId,
            'title'       => 'Test Course',
            'description' => 'Test Description',
            'status'      => 'published',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())->get('learning/courses');
        $result->assertOK();
        $result->assertSee('Test Course');
    }

    public function testStoreCreatesCourse()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('learning/courses', [
                           'title'       => 'New Course',
                           'description' => 'New Description',
                           'status'      => 'draft',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/learning/courses');
        $this->seeInDatabase('learning_courses', ['title' => 'New Course']);
    }

    public function testUpdateModifiesCourse()
    {
        $this->db->table('learning_courses')->insert([
            'school_id'   => $this->schoolId,
            'teacher_id'  => $this->userId,
            'title'       => 'Original Title',
            'description' => 'Original Description',
            'status'      => 'draft',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $courseId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("learning/courses/{$courseId}/update", [
                           'title'       => 'Updated Title',
                           'description' => 'Updated Description',
                           'status'      => 'published',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/learning/courses');
        $this->seeInDatabase('learning_courses', ['id' => $courseId, 'title' => 'Updated Title']);
    }

    public function testDeleteRemovesCourse()
    {
        $this->db->table('learning_courses')->insert([
            'school_id'   => $this->schoolId,
            'teacher_id'  => $this->userId,
            'title'       => 'Delete Me',
            'description' => 'Will be deleted',
            'status'      => 'draft',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $courseId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())->get("learning/courses/{$courseId}/delete");
        $result->assertRedirectTo('/learning/courses');
        $this->dontSeeInDatabase('learning_courses', ['id' => $courseId]);
    }
}
