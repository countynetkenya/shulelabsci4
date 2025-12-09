<?php

namespace Tests\Feature\Scheduler;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * SchedulerCrudTest - Feature tests for Scheduler CRUD operations.
 *
 * Tests all CRUD endpoints for the Scheduler module:
 * - GET /scheduler (index)
 * - GET /scheduler/create (create form)
 * - POST /scheduler/store (create action)
 * - GET /scheduler/edit/{id} (edit form)
 * - POST /scheduler/update/{id} (update action)
 * - GET /scheduler/delete/{id} (delete action)
 */
class SchedulerCrudTest extends CIUnitTestCase
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
     * Test: Index page displays scheduled jobs.
     */
    public function testIndexDisplaysJobs()
    {
        // Seed a test job
        $this->db->table('scheduled_jobs')->insert([
            'school_id'           => $this->schoolId,
            'name'                => 'Test Job',
            'description'         => 'Test Description',
            'job_class'           => 'App\Jobs\TestJob',
            'job_method'          => 'handle',
            'cron_expression'     => '0 8 * * *',
            'timezone'            => 'Africa/Nairobi',
            'is_active'           => 1,
            'max_retries'         => 3,
            'retry_delay_seconds' => 60,
            'timeout_seconds'     => 3600,
            'overlap_prevention'  => 1,
            'next_run_at'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('scheduler');

        $result->assertOK();
        $result->assertSee('Test Job');
        $result->assertSee('0 8 * * *');
    }

    /**
     * Test: Index page shows empty state when no jobs.
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('scheduler');

        $result->assertOK();
        $result->assertSee('No scheduled jobs found');
    }

    /**
     * Test: Create page displays form.
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('scheduler/create');

        $result->assertOK();
        $result->assertSee('Add Scheduled Job');
        $result->assertSee('Job Name');
        $result->assertSee('Cron Expression');
    }

    /**
     * Test: Store creates a new scheduled job.
     */
    public function testStoreCreatesJob()
    {
        $data = [
            'name'            => 'New Scheduled Job',
            'description'     => 'Job Description',
            'job_class'       => 'App\Jobs\NewJob',
            'job_method'      => 'handle',
            'cron_expression' => '0 9 * * *',
            'timezone'        => 'Africa/Nairobi',
            'is_active'       => 1,
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('scheduler/store', $data);

        $result->assertRedirectTo('/scheduler');
        $result->assertSessionHas('message');

        // Verify database
        $this->seeInDatabase('scheduled_jobs', [
            'school_id'       => $this->schoolId,
            'name'            => 'New Scheduled Job',
            'cron_expression' => '0 9 * * *',
        ]);
    }

    /**
     * Test: Store validates required fields.
     */
    public function testStoreValidatesRequiredFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('scheduler/store', []);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
    }

    /**
     * Test: Edit page displays form with job data.
     */
    public function testEditPageDisplaysJob()
    {
        // Seed a test job
        $this->db->table('scheduled_jobs')->insert([
            'school_id'           => $this->schoolId,
            'name'                => 'Edit Test Job',
            'job_class'           => 'App\Jobs\EditTest',
            'cron_expression'     => '0 10 * * *',
            'timezone'            => 'Africa/Nairobi',
            'is_active'           => 1,
            'max_retries'         => 3,
            'retry_delay_seconds' => 60,
            'timeout_seconds'     => 3600,
            'overlap_prevention'  => 1,
            'next_run_at'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $jobId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get('scheduler/edit/' . $jobId);

        $result->assertOK();
        $result->assertSee('Edit Scheduled Job');
        $result->assertSee('Edit Test Job');
        $result->assertSee('0 10 * * *');
    }

    /**
     * Test: Update modifies existing job.
     */
    public function testUpdateModifiesJob()
    {
        // Seed a test job
        $this->db->table('scheduled_jobs')->insert([
            'school_id'           => $this->schoolId,
            'name'                => 'Old Job Name',
            'job_class'           => 'App\Jobs\OldJob',
            'cron_expression'     => '0 11 * * *',
            'timezone'            => 'Africa/Nairobi',
            'is_active'           => 1,
            'max_retries'         => 3,
            'retry_delay_seconds' => 60,
            'timeout_seconds'     => 3600,
            'overlap_prevention'  => 1,
            'next_run_at'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $jobId = $this->db->insertID();

        $data = [
            'name'            => 'Updated Job Name',
            'job_class'       => 'App\Jobs\UpdatedJob',
            'cron_expression' => '0 12 * * *',
            'timezone'        => 'Africa/Nairobi',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('scheduler/update/' . $jobId, $data);

        $result->assertRedirectTo('/scheduler');
        $result->assertSessionHas('message');

        // Verify database
        $this->seeInDatabase('scheduled_jobs', [
            'id'              => $jobId,
            'name'            => 'Updated Job Name',
            'cron_expression' => '0 12 * * *',
        ]);
    }

    /**
     * Test: Delete removes scheduled job.
     */
    public function testDeleteRemovesJob()
    {
        // Seed a test job
        $this->db->table('scheduled_jobs')->insert([
            'school_id'           => $this->schoolId,
            'name'                => 'Delete Me',
            'job_class'           => 'App\Jobs\DeleteJob',
            'cron_expression'     => '0 13 * * *',
            'timezone'            => 'Africa/Nairobi',
            'is_active'           => 1,
            'max_retries'         => 3,
            'retry_delay_seconds' => 60,
            'timeout_seconds'     => 3600,
            'overlap_prevention'  => 1,
            'next_run_at'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $jobId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get('scheduler/delete/' . $jobId);

        $result->assertRedirectTo('/scheduler');
        $result->assertSessionHas('message');

        // Verify job is deleted
        $this->dontSeeInDatabase('scheduled_jobs', [
            'id' => $jobId,
        ]);
    }

    /**
     * Test: Tenant scoping - cannot access other school's jobs.
     */
    public function testTenantScopingPreventsAccessToOtherSchools()
    {
        // Create job for a different school
        $this->db->table('scheduled_jobs')->insert([
            'school_id'           => 999,
            'name'                => 'Other School Job',
            'job_class'           => 'App\Jobs\OtherSchool',
            'cron_expression'     => '0 14 * * *',
            'timezone'            => 'Africa/Nairobi',
            'is_active'           => 1,
            'max_retries'         => 3,
            'retry_delay_seconds' => 60,
            'timeout_seconds'     => 3600,
            'overlap_prevention'  => 1,
            'next_run_at'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('scheduler');

        $result->assertOK();
        $result->assertDontSee('Other School Job');
    }

    /**
     * Test: Access control - redirects when not logged in.
     */
    public function testAccessControlRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('scheduler');
        $result->assertRedirectTo('/login');
    }
}
