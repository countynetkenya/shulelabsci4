<?php

namespace Tests\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Traits\TenantTestTrait;
use Modules\Learning\Models\CourseModel;
use Modules\Learning\Models\LessonModel;
use Modules\Learning\Models\EnrollmentModel;
use Modules\Learning\Models\ProgressModel;

class ProgressApiTest extends CIUnitTestCase
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

    public function testMarkLessonComplete()
    {
        // Setup Course, Lesson, Enrollment
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'published'
        ]);

        $lessonModel = new LessonModel();
        $lessonId = $lessonModel->insert([
            'course_id' => $courseId,
            'title' => 'Lesson 1',
            'content' => 'Content',
            'sequence_order' => 1
        ]);

        $enrollmentModel = new EnrollmentModel();
        $enrollmentId = $enrollmentModel->insert([
            'school_id' => 1,
            'student_id' => 1,
            'course_id' => $courseId,
            'status' => 'active',
            'enrolled_at' => date('Y-m-d H:i:s')
        ]);

        $result = $this->withSession(['student_id' => 1])
                       ->call('post', '/api/learning/progress', [
                           'enrollment_id' => $enrollmentId,
                           'lesson_id' => $lessonId
                       ]);
        
        $result->assertStatus(201);
        
        $progressModel = new ProgressModel();
        $progress = $progressModel->where('enrollment_id', $enrollmentId)->where('lesson_id', $lessonId)->first();
        $this->assertNotNull($progress);
    }

    public function testGetProgress()
    {
        // Setup Course, Lesson, Enrollment, Progress
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'published'
        ]);

        $lessonModel = new LessonModel();
        $lessonId = $lessonModel->insert([
            'course_id' => $courseId,
            'title' => 'Lesson 1',
            'content' => 'Content',
            'sequence_order' => 1
        ]);

        $enrollmentModel = new EnrollmentModel();
        $enrollmentId = $enrollmentModel->insert([
            'school_id' => 1,
            'student_id' => 1,
            'course_id' => $courseId,
            'status' => 'active',
            'enrolled_at' => date('Y-m-d H:i:s')
        ]);

        $progressModel = new ProgressModel();
        $progressModel->insert([
            'enrollment_id' => $enrollmentId,
            'lesson_id' => $lessonId,
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        $result = $this->withSession(['student_id' => 1])
                       ->call('get', "/api/learning/progress/$enrollmentId");
        
        $result->assertOK();
        $json = json_decode($result->getJSON(), true);
        $this->assertEquals($lessonId, $json['data'][0]['lesson_id']);
    }
}
