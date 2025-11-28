<?php

namespace App\Modules\Scheduler\Commands;

use App\Modules\Scheduler\Services\JobRunner;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI command to process the job queue.
 *
 * Usage: php spark queue:work [queue_name]
 */
class ProcessJobQueue extends BaseCommand
{
    protected $group = 'Scheduler';

    protected $name = 'queue:work';

    protected $description = 'Process jobs from the queue';

    protected $usage = 'queue:work [queue_name] [options]';

    protected $arguments = [
        'queue_name' => 'Name of the queue to process (default: default)',
    ];

    protected $options = [
        '--once' => 'Process one job and exit',
        '--sleep' => 'Seconds to sleep when no jobs available (default: 3)',
        '--max-jobs' => 'Maximum jobs to process before restarting (default: 1000)',
        '--timeout' => 'Maximum seconds a job can run (default: 60)',
    ];

    public function run(array $params): void
    {
        $queue = $params[0] ?? 'default';
        $runner = new JobRunner();

        $once = CLI::getOption('once') !== null;
        $sleep = (int) (CLI::getOption('sleep') ?? 3);
        $maxJobs = (int) (CLI::getOption('max-jobs') ?? 1000);
        $timeout = (int) (CLI::getOption('timeout') ?? 60);

        CLI::write("Starting queue worker for '{$queue}'...", 'green');
        CLI::write('  Worker ID: ' . $runner->getWorkerId(), 'light_gray');

        $jobsProcessed = 0;

        do {
            $processed = $runner->processQueue($queue);

            if ($processed) {
                $jobsProcessed++;
                CLI::write("  Processed job (total: {$jobsProcessed})", 'yellow');
            } else {
                if (!$once) {
                    sleep($sleep);
                }
            }

            // Check if we should restart
            if ($jobsProcessed >= $maxJobs) {
                CLI::write("  Reached max jobs ({$maxJobs}), restarting...", 'cyan');
                break;
            }
        } while (!$once);

        CLI::write("Queue worker completed. Processed {$jobsProcessed} jobs.", 'green');
    }
}
