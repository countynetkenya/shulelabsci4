<?php

namespace Modules\Reports\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - Standard CRUD for Reports
        $routes->group('reports', ['namespace' => 'App\Modules\Reports\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'ReportsController::index');
            $routes->get('create', 'ReportsController::create');
            $routes->post('/', 'ReportsController::store');
            $routes->get('(:num)/edit', 'ReportsController::edit/$1');
            $routes->put('(:num)', 'ReportsController::update/$1');
            $routes->post('(:num)', 'ReportsController::update/$1'); // Fallback for browsers without PUT
            $routes->delete('(:num)', 'ReportsController::delete/$1');
            $routes->get('(:num)/delete', 'ReportsController::delete/$1'); // Fallback for browsers without DELETE
        });

        // API Routes
        $routes->group('api/reports', ['namespace' => 'Modules\Reports\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->post('generate', 'ReportsApiController::generate');
            $routes->get('latest', 'ReportsApiController::latest');
        });
    }
}
