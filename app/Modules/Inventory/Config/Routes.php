<?php

namespace Modules\Inventory\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Inventory Module Routes Configuration
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('inventory', ['namespace' => 'App\Modules\Inventory\Controllers\Web'], function($routes) {
            $routes->get('/', 'InventoryController::index');
            $routes->get('create', 'InventoryController::create');
            $routes->post('store', 'InventoryController::store');
            $routes->get('edit/(:num)', 'InventoryController::edit/$1');
            $routes->post('update/(:num)', 'InventoryController::update/$1');
            $routes->get('delete/(:num)', 'InventoryController::delete/$1');
        });
    }
}
