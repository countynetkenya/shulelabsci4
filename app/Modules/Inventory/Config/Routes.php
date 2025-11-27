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

            // Stock & Transfers
            $routes->get('stock', 'InventoryStockWebController::index');
            $routes->get('transfer', 'InventoryStockWebController::transfer');
            $routes->post('transfer/process', 'InventoryStockWebController::processTransfer');
        });

        // API Routes (Mobile/External)
        $routes->group('api/inventory', ['namespace' => 'Modules\Inventory\Controllers\Api'], function ($routes) {
            // Items
            $routes->get('items', 'ItemsApiController::index');
            $routes->get('items/(:num)', 'ItemsApiController::show/$1');
            $routes->post('items', 'ItemsApiController::create');
            $routes->put('items/(:num)', 'ItemsApiController::update/$1');
            $routes->delete('items/(:num)', 'ItemsApiController::delete/$1');

            // Stock
            $routes->get('stock', 'InventoryStockController::index');

            // Transfers
            $routes->post('transfers', 'TransfersApiController::create');
            $routes->post('transfers/(:num)/confirm', 'TransfersApiController::confirm/$1');
        });
    }
}
