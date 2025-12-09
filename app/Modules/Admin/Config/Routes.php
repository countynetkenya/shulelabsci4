<?php

namespace Modules\Admin\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - Settings Management
        $routes->group('admin/settings', ['namespace' => 'App\Modules\Admin\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'AdminSettingsController::index');
            $routes->get('create', 'AdminSettingsController::create');
            $routes->post('store', 'AdminSettingsController::store');
            $routes->get('edit/(:num)', 'AdminSettingsController::edit/$1');
            $routes->post('update/(:num)', 'AdminSettingsController::update/$1');
            $routes->get('delete/(:num)', 'AdminSettingsController::delete/$1');
        });

        // API Routes
        $routes->group('api/admin', ['namespace' => 'Modules\Admin\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('students', 'StudentsApiController::index');
            $routes->get('teachers', 'TeachersApiController::index');
            $routes->get('classes', 'ClassesApiController::index');
        });
    }
}
