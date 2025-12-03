<?php

namespace Modules\Admissions\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('admissions', ['namespace' => 'Modules\Admissions\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'AdmissionsWebController::index');
        });

        // API Routes
        $routes->group('api/admissions', ['namespace' => 'Modules\Admissions\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->post('apply', 'AdmissionsApiController::apply');
            $routes->get('status', 'AdmissionsApiController::status');
        });
    }
}
