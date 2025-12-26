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
            $routes->get('invoices', 'InvoicesController::index');
            $routes->get('invoices/create', 'InvoicesController::create');
            $routes->post('invoices', 'InvoicesController::store');
            $routes->get('invoices/show/(:num)', 'InvoicesController::show/$1');

            // Payments
            $routes->get('payments', 'PaymentsController::index');
            $routes->get('payments/create', 'PaymentsController::create');
            $routes->post('payments', 'PaymentsController::store');

            // Fee Structures
            $routes->post('fee-structures', 'FinanceWebController::createFeeStructure');
        });

        // Transactions CRUD Routes
        $routes->group('finance/transactions', ['namespace' => 'App\Modules\Finance\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'FinanceController::index');
            $routes->get('create', 'FinanceController::create');
            $routes->post('store', 'FinanceController::store');
            $routes->get('edit/(:num)', 'FinanceController::edit/$1');
            $routes->post('update/(:num)', 'FinanceController::update/$1');
            $routes->get('delete/(:num)', 'FinanceController::delete/$1');
        });

        // API Routes
        $routes->group('api/finance', ['filter' => 'auth', 'namespace' => 'Modules\Finance\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('invoices', 'InvoiceApiController::index');
            $routes->get('invoices/(:num)', 'InvoiceApiController::show/$1');
            $routes->get('health', 'HealthApiController::index');
        });
    }
}
