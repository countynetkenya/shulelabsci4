<?php

namespace Tests\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Modules\Learning\Models\CourseModel;
use Modules\Learning\Models\LessonModel;
use Tests\Support\Traits\TenantTestTrait;

class LessonsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();

        // Robust CSRF Disable
        $config = config('Filters');
        $newBefore = [];
        foreach ($config->globals['before'] as $key => $value) {
            if ($value !== 'csrf' && $key !== 'csrf') {
                if (is_array($value)) {
                    $newBefore[$key] = $value;
                } else {
                    $newBefore[] = $value;
                }
            }
        }
        $config->globals['before'] = $newBefore;
        \CodeIgniter\Config\Factories::injectMock('filters', 'Filters', $config);
    }

    public function testCreateLessonPage()
    {
        // Create a course first
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'draft',
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->call('get', "/learning/courses/$courseId/lessons/create");

        $result->assertOK();
        $result->assertSee('Add Lesson to: Test Course');
    }

    public function testStoreLesson()
    {
        // Create a course first
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'draft',
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->call('post', "/learning/courses/$courseId/lessons", [
                           'title' => 'Lesson 1',
                           'content' => 'Content of lesson 1',
                           'sequence_order' => 1,
                       ]);

        $result->assertRedirectTo("/learning/courses/$courseId");

        $lessonModel = new LessonModel();
        $lesson = $lessonModel->where('course_id', $courseId)->first();
        $this->assertEquals('Lesson 1', $lesson['title']);
    }

    public function testShowCourseWithLessons()
    {
        // Create a course
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'draft',
        ]);

        // Create a lesson
        $lessonModel = new LessonModel();
        $lessonModel->insert([
            'course_id' => $courseId,
            'title' => 'Lesson 1',
            'content' => 'Content',
            'sequence_order' => 1,
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->call('get', "/learning/courses/$courseId");

        $result->assertOK();
        $result->assertSee('Lesson 1');
    }
}
