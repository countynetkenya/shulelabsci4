<?php

namespace Tests\Feature\LMS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Modules\LMS\Database\Seeds\LearningSeeder;
use Tests\Support\Traits\TenantTestTrait;

class LearningApiTest extends CIUnitTestCase
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

    public function testListCourses()
    {
        // Enroll the test user (student) in the course created by seeder
        $course = $this->db->table('learning_courses')->get()->getRow();
        $this->db->table('learning_enrollments')->insert([
            'school_id'  => $this->schoolId,
            'student_id' => $this->userId,
            'course_id'  => $course->id,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('api/lms/courses');

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertNotEmpty($json['data']);
        $this->assertEquals('Introduction to Algebra', $json['data'][0]['title']);
    }

    public function testShowCourse()
    {
        // Enroll
        $course = $this->db->table('learning_courses')->get()->getRow();
        $this->db->table('learning_enrollments')->insert([
            'school_id'  => $this->schoolId,
            'student_id' => $this->userId,
            'course_id'  => $course->id,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("api/lms/courses/{$course->id}");

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertEquals('Introduction to Algebra', $json['data']['title']);
        $this->assertCount(3, $json['data']['lessons']);
    }

    public function testShowLesson()
    {
        // Enroll
        $course = $this->db->table('learning_courses')->get()->getRow();
        $this->db->table('learning_enrollments')->insert([
            'school_id'  => $this->schoolId,
            'student_id' => $this->userId,
            'course_id'  => $course->id,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $enrollmentId = $this->db->insertID();

        $lesson = $this->db->table('learning_lessons')->where('course_id', $course->id)->get()->getRow();

        $result = $this->withSession($this->getAdminSession())
                       ->get("api/lms/lessons/{$lesson->id}");

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertEquals($lesson->title, $json['data']['title']);
    }

    public function testCompleteLesson()
    {
        // Enroll
        $course = $this->db->table('learning_courses')->get()->getRow();
        $this->db->table('learning_enrollments')->insert([
            'school_id'  => $this->schoolId,
            'student_id' => $this->userId,
            'course_id'  => $course->id,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $enrollmentId = $this->db->insertID();

        $lesson = $this->db->table('learning_lessons')->where('course_id', $course->id)->get()->getRow();

        $result = $this->withSession($this->getAdminSession())
                       ->post("api/lms/lessons/{$lesson->id}/complete");

        $result->assertStatus(200);
        $json = json_decode($result->getJSON(), true);
        $this->assertEquals('Lesson marked as complete', $json['messages']['success']);

        // Verify DB
        // Note: We need to cast IDs to strings because SQLite returns them as strings in some contexts
        // or the assertion might be strict on types.
        $this->seeInDatabase('learning_progress', [
            'enrollment_id' => (string)$enrollmentId,
            'lesson_id'     => (string)$lesson->id,
        ]);
    }
}
