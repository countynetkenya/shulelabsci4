<?php

declare(strict_types=1);

namespace Modules\Foundation\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Registers module specific routes.
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('system', static function (RouteCollection $routes): void {
            $routes->get('health', 'Modules\\Foundation\\Controllers\\HealthController::index');
            $routes->get('settings', 'Modules\\Foundation\\Controllers\\SettingsController::index');
            $routes->post('settings', 'Modules\\Foundation\\Controllers\\SettingsController::update');
            
            // Tenant Management
            $routes->get('tenants', 'Modules\\Foundation\\Controllers\\TenantController::index');
            $routes->get('tenants/create', 'Modules\\Foundation\\Controllers\\TenantController::create');
            $routes->post('tenants', 'Modules\\Foundation\\Controllers\\TenantController::store');

            // Roles Management
            $routes->get('roles', '\Modules\Foundation\Controllers\RolesController::index');
            $routes->get('roles/create', '\Modules\Foundation\Controllers\RolesController::create');
            $routes->post('roles', '\Modules\Foundation\Controllers\RolesController::store');
            $routes->get('roles/edit/(:num)', '\Modules\Foundation\Controllers\RolesController::edit/$1');
            $routes->post('roles/update/(:num)', '\Modules\Foundation\Controllers\RolesController::update/$1');
            $routes->get('roles/delete/(:num)', '\Modules\Foundation\Controllers\RolesController::delete/$1');

            // Users Management
            $routes->get('users', '\Modules\Foundation\Controllers\UsersController::index');
            $routes->get('users/create', '\Modules\Foundation\Controllers\UsersController::create');
            $routes->post('users', '\Modules\Foundation\Controllers\UsersController::store');
            $routes->get('users/edit/(:num)', '\Modules\Foundation\Controllers\UsersController::edit/$1');
            $routes->post('users/update/(:num)', '\Modules\Foundation\Controllers\UsersController::update/$1');
            $routes->get('users/delete/(:num)', '\Modules\Foundation\Controllers\UsersController::delete/$1');
        });

        $routes->group('operations', static function (RouteCollection $routes): void {
            $routes->get('dashboard', 'Modules\\Foundation\\Controllers\\OperationsDashboardController::index');
            $routes->get('mobile-snapshots', 'Modules\\Foundation\\Controllers\\OperationsDashboardController::mobileSnapshots');
        });

        // API Routes
        $routes->group('api/foundation', ['namespace' => 'Modules\Foundation\Controllers\Api', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('schools', 'SchoolsApiController::index');
            $routes->get('health', 'HealthApiController::index');
        });
    }
}
