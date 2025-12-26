<?php

namespace Modules\Orchestration\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Orchestration Module Routes Configuration
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Orchestration Workflows
        $routes->group('orchestration', ['namespace' => 'Modules\Orchestration\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'OrchestrationController::index');
            $routes->get('create', 'OrchestrationController::create');
            $routes->post('store', 'OrchestrationController::store');
            $routes->get('edit/(:num)', 'OrchestrationController::edit/$1');
            $routes->post('update/(:num)', 'OrchestrationController::update/$1');
            $routes->get('delete/(:num)', 'OrchestrationController::delete/$1');
        });
    }
}
