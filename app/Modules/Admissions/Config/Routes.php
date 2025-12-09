<?php

namespace Modules\Admissions\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('admissions', ['namespace' => 'App\Modules\Admissions\Controllers\Web', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'AdmissionsController::index');
            $routes->get('create', 'AdmissionsController::create');
            $routes->post('store', 'AdmissionsController::store');
            $routes->get('edit/(:num)', 'AdmissionsController::edit/$1');
            $routes->post('update/(:num)', 'AdmissionsController::update/$1');
            $routes->get('delete/(:num)', 'AdmissionsController::delete/$1');
        });
    }
}
