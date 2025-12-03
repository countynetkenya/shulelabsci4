<?php

namespace Modules\Finance\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('finance', ['filter' => 'auth', 'namespace' => 'Modules\Finance\Controllers'], static function (RouteCollection $routes): void {
            $routes->get('', 'FinanceWebController::index');
            
            // Invoices
            $routes->get('invoices/new', 'FinanceWebController::newInvoice');
            $routes->post('invoices', 'FinanceWebController::createInvoice');
            
            // Payments
            $routes->get('payments/new', 'FinanceWebController::newPayment');
            $routes->post('payments', 'FinanceWebController::recordPayment');
            
            // Fee Structures
            $routes->post('fee-structures', 'FinanceWebController::createFeeStructure');
        });

        // API Routes
        $routes->group('api/finance', ['filter' => 'auth', 'namespace' => 'Modules\Finance\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('invoices', 'InvoiceApiController::index');
            $routes->get('invoices/(:num)', 'InvoiceApiController::show/$1');
            $routes->get('health', 'HealthApiController::index');
        });
    }
}
