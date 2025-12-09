<?php

namespace Modules\Transport\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('transport', ['namespace' => 'App\Modules\Transport\Controllers\Web', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'TransportController::index');
            $routes->get('create', 'TransportController::create');
            $routes->post('store', 'TransportController::store');
            $routes->get('edit/(:num)', 'TransportController::edit/$1');
            $routes->post('update/(:num)', 'TransportController::update/$1');
            $routes->get('delete/(:num)', 'TransportController::delete/$1');
        });
    }
}
