<?php

namespace Modules\Reports\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('reports', ['namespace' => 'Modules\Reports\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'ReportsWebController::index');
        });

        // API Routes
        $routes->group('api/reports', ['namespace' => 'Modules\Reports\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->post('generate', 'ReportsApiController::generate');
            $routes->get('latest', 'ReportsApiController::latest');
        });
    }
}
