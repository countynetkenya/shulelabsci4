<?php

namespace Modules\Finance\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('finance', ['filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('/', '\Modules\Finance\Controllers\FinanceWebController::index');
            
            // Invoices
            $routes->get('invoices/new', '\Modules\Finance\Controllers\FinanceWebController::newInvoice');
            $routes->post('invoices', '\Modules\Finance\Controllers\FinanceWebController::createInvoice');
            
            // Payments
            $routes->get('payments/new', '\Modules\Finance\Controllers\FinanceWebController::newPayment');
            $routes->post('payments', '\Modules\Finance\Controllers\FinanceWebController::recordPayment');
            
            // Fee Structures
            $routes->post('fee-structures', '\Modules\Finance\Controllers\FinanceWebController::createFeeStructure');
        });

        $routes->group('api/finance', ['filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('invoices', '\Modules\Finance\Controllers\FinanceApiController::index');
            $routes->get('invoices/(:num)', '\Modules\Finance\Controllers\FinanceApiController::show/$1');
        });
    }
}
