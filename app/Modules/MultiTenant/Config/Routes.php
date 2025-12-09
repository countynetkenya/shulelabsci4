<?php

namespace Modules\MultiTenant\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * MultiTenant Module Routes Configuration
 * 
 * Web Routes: /multitenant/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - Tenant Management (Super Admin only)
        $routes->group('multitenant', ['namespace' => 'App\Modules\MultiTenant\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'TenantController::index');
            $routes->get('create', 'TenantController::create');
            $routes->post('store', 'TenantController::store');
            $routes->get('edit/(:num)', 'TenantController::edit/$1');
            $routes->post('update/(:num)', 'TenantController::update/$1');
            $routes->get('delete/(:num)', 'TenantController::delete/$1');
            $routes->get('activate/(:num)', 'TenantController::activate/$1');
            $routes->get('suspend/(:num)', 'TenantController::suspend/$1');
        });
    }
}
