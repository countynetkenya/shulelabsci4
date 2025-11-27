<?php

namespace Modules\Inventory\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes (Browser)
        $routes->group('inventory', ['namespace' => 'Modules\Inventory\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('items', 'InventoryWebController::index');
            $routes->get('items/create', 'InventoryWebController::create');
            $routes->post('items/store', 'InventoryWebController::store');
        });

        // API Routes (Mobile/External)
        $routes->group('api/inventory', ['namespace' => 'Modules\Inventory\Controllers'], function ($routes) {
            // Items
            $routes->get('items', 'InventoryItemController::index');
            $routes->get('items/(:num)', 'InventoryItemController::show/$1');
            $routes->post('items', 'InventoryItemController::create');
            $routes->put('items/(:num)', 'InventoryItemController::update/$1');
            $routes->delete('items/(:num)', 'InventoryItemController::delete/$1');
        });
    }
}
