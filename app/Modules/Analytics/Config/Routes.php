<?php

namespace Modules\Analytics\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('analytics', ['namespace' => 'Modules\Analytics\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'AnalyticsWebController::index');
        });

        // API Routes
        $routes->group('api/analytics', ['namespace' => 'Modules\Analytics\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('summary', 'AnalyticsApiController::summary');
            $routes->get('performance', 'AnalyticsApiController::performance');
        });
    }
}
