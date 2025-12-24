<?php

namespace Tests\Feature\LMS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * LMSCourseCrudTest - Feature tests for LMS Course CRUD operations
 */
class LMSCourseCrudTest extends CIUnitTestCase
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
     * Test: Index page displays courses
     */
    public function testIndexDisplaysCourses()
    {
        // Seed a test course
        $this->db->table('learning_courses')->insert([
            'school_id' => $this->schoolId,
            'teacher_id' => $this->userId,
            'instructor_id' => $this->userId,
            'title' => 'Test Course',
            'description' => 'Test Description',
            'status' => 'published',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('lms/courses');

        $result->assertOK();
        $result->assertSee('Test Course');
    }

    /**
     * Test: Create page displays form
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('lms/courses/create');

        $result->assertOK();
        $result->assertSee('Course Title');
        $result->assertSee('Description');
    }

    /**
     * Test: Store creates a new course
     */
    public function testStoreCreatesCourse()
    {
        $data = [
            'title' => 'New Test Course',
            'description' => 'Course description',
            'modules' => 'Module 1\nModule 2',
            'status' => 'draft',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('lms/courses/store', $data);

        $result->assertRedirectTo('/lms/courses');
        
        $course = $this->db->table('learning_courses')
                          ->where('school_id', $this->schoolId)
                          ->where('title', 'New Test Course')
                          ->get()
                          ->getRowArray();
        
        $this->assertNotNull($course);
        $this->assertEquals('draft', $course['status']);
    }

    /**
     * Test: Edit page displays course
     */
    public function testEditPageDisplaysCourse()
    {
        $courseId = $this->db->table('learning_courses')->insert([
            'school_id' => $this->schoolId,
            'teacher_id' => $this->userId,
            'instructor_id' => $this->userId,
            'title' => 'Edit Test Course',
            'description' => 'Test',
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('lms/courses/edit/' . $courseId);

        $result->assertOK();
        $result->assertSee('Edit Test Course');
    }

    /**
     * Test: Update modifies existing course
     */
    public function testUpdateModifiesCourse()
    {
        $courseId = $this->db->table('learning_courses')->insert([
            'school_id' => $this->schoolId,
            'teacher_id' => $this->userId,
            'instructor_id' => $this->userId,
            'title' => 'Original Title',
            'description' => 'Original',
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'status' => 'published',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('lms/courses/update/' . $courseId, $data);

        $result->assertRedirectTo('/lms/courses');
        
        $course = $this->db->table('learning_courses')
                          ->where('id', $courseId)
                          ->get()
                          ->getRowArray();
        
        $this->assertEquals('Updated Title', $course['title']);
        $this->assertEquals('published', $course['status']);
    }

    /**
     * Test: Delete removes course (soft delete)
     */
    public function testDeleteRemovesCourse()
    {
        $courseId = $this->db->table('learning_courses')->insert([
            'school_id' => $this->schoolId,
            'teacher_id' => $this->userId,
            'instructor_id' => $this->userId,
            'title' => 'Delete Test Course',
            'description' => 'Test',
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('lms/courses/delete/' . $courseId);

        $result->assertRedirectTo('/lms/courses');
        
        // Check soft delete
        $course = $this->db->table('learning_courses')
                          ->where('id', $courseId)
                          ->get()
                          ->getRowArray();
        
        $this->assertNotNull($course['deleted_at']);
    }
}
