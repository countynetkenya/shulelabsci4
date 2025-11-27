<?php

namespace Modules\Finance\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Legacy Routes (Keep for now)
        $routes->group('finance', static function (RouteCollection $routes): void {
            $routes->get('ping', 'Modules\\Finance\\Controllers\\HealthController::index');
            $routes->post('invoices', 'Modules\\Finance\\Controllers\\InvoiceController::create');
            $routes->post('invoices/(:segment)/settle', 'Modules\\Finance\\Controllers\\InvoiceController::settle/$1');
        });

        // New API Routes
        $routes->group('api/finance', ['namespace' => 'Modules\Finance\Controllers\Api'], static function ($routes) {
            $routes->get('invoices/(:num)', 'FinanceApiController::invoices/$1');
        });

        // New Web Routes
        $routes->group('finance', ['namespace' => 'Modules\Finance\Controllers\Web'], static function ($routes) {
            $routes->get('/', 'FinanceWebController::index');
        });
    }
}
