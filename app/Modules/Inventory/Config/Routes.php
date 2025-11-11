<?php

namespace Modules\Inventory\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('v2/inventory', static function (RouteCollection $routes): void {
            $routes->post('transfers', 'Modules\\Inventory\\Controllers\\TransferController::create');
            $routes->post('transfers/(:segment)/complete', 'Modules\\Inventory\\Controllers\\TransferController::complete/$1');
        });
    }
}
