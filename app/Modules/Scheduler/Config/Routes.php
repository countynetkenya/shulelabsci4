<?php

namespace Modules\Scheduler\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('scheduler', ['namespace' => 'Modules\Scheduler\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'SchedulerWebController::index');
        });

        // API Routes
        $routes->group('api/scheduler', ['namespace' => 'Modules\Scheduler\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('jobs', 'SchedulerApiController::index');
            $routes->post('jobs', 'SchedulerApiController::create');
        });
    }
}
