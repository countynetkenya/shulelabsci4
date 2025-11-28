<?php

namespace Tests\Feature\Scheduler;

use App\Modules\Scheduler\Models\ScheduledJobModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ScheduledJobModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    public function testGetDueJobsFiltersNullStatusesWithOtherPredicates(): void
    {
        $model = new ScheduledJobModel();

        $past = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));

        $dueId = $model->insert([
            'name' => 'Active null status due',
            'job_class' => 'App\\Jobs\\Example',
            'job_method' => 'handle',
            'cron_expression' => '* * * * *',
            'timezone' => 'Africa/Nairobi',
            'is_active' => 1,
            'last_run_status' => null,
            'next_run_at' => $past,
            'created_by' => 1,
        ], true);

        $model->insert([
            'name' => 'Inactive null status',
            'job_class' => 'App\\Jobs\\Example',
            'job_method' => 'handle',
            'cron_expression' => '* * * * *',
            'timezone' => 'Africa/Nairobi',
            'is_active' => 0,
            'last_run_status' => null,
            'next_run_at' => $past,
            'created_by' => 1,
        ]);

        $model->insert([
            'name' => 'Active null status future',
            'job_class' => 'App\\Jobs\\Example',
            'job_method' => 'handle',
            'cron_expression' => '* * * * *',
            'timezone' => 'Africa/Nairobi',
            'is_active' => 1,
            'last_run_status' => null,
            'next_run_at' => $future,
            'created_by' => 1,
        ]);

        $dueJobs = $model->getDueJobs();
        $dueIds = array_column($dueJobs, 'id');

        $this->assertCount(1, $dueJobs);
        $this->assertSame($dueId, $dueIds[0]);
    }
}
