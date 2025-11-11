<?php

declare(strict_types=1);

namespace Modules\Learning\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('v2/learning', static function (RouteCollection $routes): void {
            $routes->post('moodle/grades', 'Modules\\Learning\\Controllers\\MoodleSyncController::pushGrades');
            $routes->post('moodle/enrollments', 'Modules\\Learning\\Controllers\\MoodleSyncController::syncEnrollments');
        });
    }
}
