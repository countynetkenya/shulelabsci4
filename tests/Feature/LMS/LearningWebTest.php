<?php

namespace Tests\Feature\LMS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Modules\LMS\Database\Seeds\LearningSeeder;
use Tests\Support\Traits\TenantTestTrait;

class LearningWebTest extends CIUnitTestCase
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

        $this->setupTenantContext();

        // Ensure clean slate for Learning tables
        $this->db->table('learning_progress')->emptyTable();
        $this->db->table('learning_enrollments')->emptyTable();
        $this->db->table('learning_lessons')->emptyTable();
        $this->db->table('learning_courses')->emptyTable();

        $this->seed(LearningSeeder::class);
    }

    public function testIndex()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('lms/courses');

        $result->assertStatus(200);
        $result->assertSee('Introduction to Algebra');
    }

    public function testCreateCourse()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('lms/courses', [
                           'title' => 'New Course',
                           'description' => 'Description',
                           'status' => 'draft',
                           'csrf_test_name' => csrf_hash(), // CSRF is disabled in TenantTestTrait but good practice
                       ]);

        $result->assertRedirectTo('lms/courses');
        $this->seeInDatabase('learning_courses', ['title' => 'New Course']);
    }

    public function testShowCourse()
    {
        $course = $this->db->table('learning_courses')->get()->getRow();

        $result = $this->withSession($this->getAdminSession())
                       ->get("lms/courses/{$course->id}");

        $result->assertStatus(200);
        $result->assertSee($course->title);
        $result->assertSee('Variables'); // Lesson title from seeder
    }

    public function testCreateLesson()
    {
        $course = $this->db->table('learning_courses')->get()->getRow();

        $result = $this->withSession($this->getAdminSession())
            ->post("/lms/courses/{$course->id}/lessons", [
                'title' => 'New Lesson',
                'content' => 'Lesson Content',
                'sequence_order' => 1,
            ]);

        $result->assertRedirect();
        $this->seeInDatabase('learning_lessons', ['title' => 'New Lesson', 'course_id' => $course->id]);
    }
}
