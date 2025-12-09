<?php

namespace Modules\Threads\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('threads', ['namespace' => 'App\Modules\Threads\Controllers\Web', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'ThreadsController::index');
            $routes->get('create', 'ThreadsController::create');
            $routes->post('store', 'ThreadsController::store');
            $routes->get('edit/(:num)', 'ThreadsController::edit/$1');
            $routes->post('update/(:num)', 'ThreadsController::update/$1');
            $routes->get('delete/(:num)', 'ThreadsController::delete/$1');
        });
    }
}
