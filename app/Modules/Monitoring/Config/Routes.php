<?php

namespace Modules\Monitoring\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Monitoring Module Routes Configuration.
 *
 * Web Routes: /monitoring/*
 * API Routes: /api/monitoring/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Metrics Management
        $routes->group('monitoring', ['namespace' => 'App\Modules\Monitoring\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'MonitoringController::index');
            $routes->get('create', 'MonitoringController::create');
            $routes->post('store', 'MonitoringController::store');
            $routes->get('edit/(:num)', 'MonitoringController::edit/$1');
            $routes->post('update/(:num)', 'MonitoringController::update/$1');
            $routes->get('delete/(:num)', 'MonitoringController::delete/$1');
        });

        // API Routes (for health checks, metrics collection, etc.)
        $routes->group('api/monitoring', ['namespace' => 'App\Modules\Monitoring\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('health', 'MonitoringApiController::health');
            $routes->get('metrics', 'MonitoringApiController::metrics');
            $routes->post('metrics', 'MonitoringApiController::recordMetric');
        });
    }
}
