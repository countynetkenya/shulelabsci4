<?php

namespace Tests\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Modules\Learning\Models\CourseModel;
use Modules\Learning\Models\EnrollmentModel;
use Tests\Support\Traits\TenantTestTrait;

class EnrollmentsApiTest extends CIUnitTestCase
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

    public function testIndex()
    {
        // Create a course
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'published',
        ]);

        // Enroll student (ID 1 is assumed in controller for now)
        $enrollmentModel = new EnrollmentModel();
        $enrollmentModel->insert([
            'school_id' => 1,
            'student_id' => 1,
            'course_id' => $courseId,
            'status' => 'active',
            'enrolled_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession(['student_id' => 1])
                       ->call('get', '/api/learning/enrollments');

        $result->assertOK();
        // $result->assertJSONFragment(['course_title' => 'Test Course']);
        $result->assertSee('Test Course');
    }

    public function testCreate()
    {
        // Create a course
        $courseModel = new CourseModel();
        $courseId = $courseModel->insert([
            'school_id' => 1,
            'teacher_id' => 1,
            'title' => 'Test Course',
            'description' => 'Description',
            'status' => 'published',
        ]);

        $result = $this->withSession(['student_id' => 1, 'current_school_id' => 1])
                       ->call('post', '/api/learning/enrollments', [
                           'course_id' => $courseId,
                       ]);

        $result->assertStatus(201);
        $result->assertJSONFragment(['message' => 'Enrolled successfully']);

        $enrollmentModel = new EnrollmentModel();
        $enrollment = $enrollmentModel->where('student_id', 1)->where('course_id', $courseId)->first();
        $this->assertNotNull($enrollment);
    }
}
