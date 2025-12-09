<?php

namespace Tests\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class CoursesTest extends CIUnitTestCase
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
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/learning/courses');
        $result->assertOK();
        $result->assertSee('Courses');
    }

    public function testCreate()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/learning/courses/create');
        $result->assertOK();
        $result->assertSee('Create Course');
    }

    public function testStore()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('post', '/learning/courses', [
            'title' => 'Mathematics 101',
            'description' => 'Basic Math',
            'status' => 'draft',
        ]);

        $result->assertRedirectTo('/learning/courses');
        $this->seeInDatabase('learning_courses', ['title' => 'Mathematics 101', 'school_id' => $this->schoolId]);
    }

    public function testApiIndex()
    {
        // Seed a course
        $this->db->table('learning_courses')->insert([
            'school_id' => $this->schoolId,
            'teacher_id' => $this->userId,
            'title' => 'Science 101',
            'status' => 'published',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->call('get', '/api/learning/courses', ['school_id' => $this->schoolId]);

        $result->assertOK();

        $json = json_decode($result->getJSON(), true);
        $this->assertCount(1, $json);
        $this->assertEquals('Science 101', $json[0]['title']);
    }
}
