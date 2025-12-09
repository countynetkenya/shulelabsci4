<?php

namespace Modules\Scheduler\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Scheduler\Services\SchedulerService;

/**
 * CLI command to process scheduled jobs.
 *
 * Usage: php spark scheduler:run
 */
class ProcessScheduledJobs extends BaseCommand
{
    protected $group = 'Scheduler';

    protected $name = 'scheduler:run';

    protected $description = 'Process all due scheduled jobs';

    protected $usage = 'scheduler:run [options]';

    protected $options = [
        '--once' => 'Run once and exit (default behavior)',
        '--daemon' => 'Run continuously as a daemon',
        '--sleep' => 'Seconds to sleep between checks in daemon mode (default: 60)',
    ];

    public function run(array $params): void
    {
        $scheduler = new SchedulerService();
        $daemon = CLI::getOption('daemon') !== null;
        $sleep = (int) (CLI::getOption('sleep') ?? 60);

        CLI::write('Starting scheduler...', 'green');

        do {
            $results = $scheduler->processDueJobs();

            if (!empty($results)) {
                foreach ($results as $jobId => $runId) {
                    CLI::write("  Executed job #{$jobId} (run #{$runId})", 'yellow');
                }
            } else {
                CLI::write('  No jobs due for execution', 'light_gray');
            }

            if ($daemon) {
                CLI::write("  Sleeping for {$sleep} seconds...", 'light_gray');
                sleep($sleep);
            }
        } while ($daemon);

        CLI::write('Scheduler completed.', 'green');
    }
}
