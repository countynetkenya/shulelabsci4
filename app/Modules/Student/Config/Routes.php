<?php

namespace Modules\Student\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Student Module Routes Configuration.
 *
 * Web Routes: /students/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Students
        $routes->group('students', ['namespace' => 'App\Modules\Student\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'StudentController::index');
            $routes->get('create', 'StudentController::create');
            $routes->post('store', 'StudentController::store');
            $routes->get('edit/(:num)', 'StudentController::edit/$1');
            $routes->post('update/(:num)', 'StudentController::update/$1');
            $routes->get('delete/(:num)', 'StudentController::delete/$1');
        });
    }
}
