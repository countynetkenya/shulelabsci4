<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Tasks\Config\Tasks as BaseTasks;
use CodeIgniter\Tasks\Scheduler;

class Tasks extends BaseTasks
{
    public function init(Scheduler $schedule): void
    {
        parent::init($schedule);

        $schedule->call(static function (): void {
            service('moodleDispatchRunner')->runGrades();
        })
            ->everyFifteenMinutes()
            ->named('moodle-grade-sync');

        $schedule->call(static function (): void {
            service('moodleDispatchRunner')->runEnrollments();
        })
            ->hourly()
            ->named('moodle-enrollment-sync');
    }
}
