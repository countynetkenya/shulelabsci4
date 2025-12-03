<?php

namespace Modules\POS\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('pos', ['namespace' => 'Modules\POS\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'PosWebController::index');
            $routes->get('sales', 'PosWebController::sales');
        });

        // API Routes
        $routes->group('api/pos', ['namespace' => 'Modules\POS\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('registers', 'PosApiController::registers');
            $routes->post('transactions', 'PosApiController::createTransaction');
        });
    }
}
