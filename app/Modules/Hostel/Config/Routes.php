<?php

namespace Modules\Hostel\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('hostel', ['namespace' => 'App\Modules\Hostel\Controllers\Web', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'HostelController::index');
            $routes->get('create', 'HostelController::create');
            $routes->post('store', 'HostelController::store');
            $routes->get('edit/(:num)', 'HostelController::edit/$1');
            $routes->post('update/(:num)', 'HostelController::update/$1');
            $routes->get('delete/(:num)', 'HostelController::delete/$1');
        });
    }
}
