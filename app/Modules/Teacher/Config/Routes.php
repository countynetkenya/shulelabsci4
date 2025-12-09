<?php

namespace Modules\Teacher\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('teachers', ['namespace' => 'App\Modules\Teacher\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'TeacherController::index');
            $routes->get('create', 'TeacherController::create');
            $routes->post('store', 'TeacherController::store');
            $routes->get('edit/(:num)', 'TeacherController::edit/$1');
            $routes->post('update/(:num)', 'TeacherController::update/$1');
            $routes->get('delete/(:num)', 'TeacherController::delete/$1');
        });
    }
}
