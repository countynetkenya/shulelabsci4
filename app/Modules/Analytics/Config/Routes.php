<?php

namespace Modules\Analytics\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Analytics Module Routes Configuration.
 *
 * Web Routes: /analytics/*
 * API Routes: /api/analytics/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Analytics Dashboards
        $routes->group('analytics', ['namespace' => 'Modules\Analytics\Controllers', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('/', 'AnalyticsWebController::index');
            $routes->get('create', 'AnalyticsWebController::create');
            $routes->post('store', 'AnalyticsWebController::store');
            $routes->get('edit/(:num)', 'AnalyticsWebController::edit/$1');
            $routes->post('update/(:num)', 'AnalyticsWebController::update/$1');
            $routes->get('delete/(:num)', 'AnalyticsWebController::delete/$1');
        });

        // API Routes
        $routes->group('api/analytics', ['namespace' => 'Modules\Analytics\Controllers\Api', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('summary', 'AnalyticsApiController::summary');
            $routes->get('performance', 'AnalyticsApiController::performance');
        });
    }
}
