<?php

namespace Modules\Transport\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('transport', ['namespace' => 'Modules\Transport\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'TransportWebController::index');
            $routes->get('routes', 'TransportWebController::routes');
            $routes->get('vehicles', 'TransportWebController::vehicles');
        });

        // API Routes
        $routes->group('api/transport', ['namespace' => 'Modules\Transport\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('routes', 'TransportApiController::routes');
            $routes->get('vehicles', 'TransportApiController::vehicles');
            $routes->post('routes', 'TransportApiController::createRoute');
        });
    }
}
