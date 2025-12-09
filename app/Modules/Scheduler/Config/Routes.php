<?php

namespace Modules\Scheduler\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Scheduler Module Routes Configuration
 * 
 * Web Routes: /scheduler/*
 * API Routes: /api/scheduler/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Scheduled Jobs
        $routes->group('scheduler', ['namespace' => 'App\Modules\Scheduler\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'SchedulerController::index');
            $routes->get('create', 'SchedulerController::create');
            $routes->post('store', 'SchedulerController::store');
            $routes->get('edit/(:num)', 'SchedulerController::edit/$1');
            $routes->post('update/(:num)', 'SchedulerController::update/$1');
            $routes->get('delete/(:num)', 'SchedulerController::delete/$1');
        });

        // API Routes
        $routes->group('api/scheduler', ['namespace' => 'Modules\Scheduler\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('jobs', 'SchedulerApiController::index');
            $routes->get('jobs/(:num)', 'SchedulerApiController::show/$1');
            $routes->post('jobs', 'SchedulerApiController::create');
            $routes->put('jobs/(:num)', 'SchedulerApiController::update/$1');
            $routes->delete('jobs/(:num)', 'SchedulerApiController::delete/$1');
        });
    }
}

