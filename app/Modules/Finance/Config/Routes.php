<?php

namespace Modules\Finance\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('finance', ['filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('/', '\Modules\Finance\Controllers\FinanceWebController::index');
            $routes->post('fee-structures', '\Modules\Finance\Controllers\FinanceWebController::createFeeStructure');
            $routes->post('invoices', '\Modules\Finance\Controllers\FinanceWebController::createInvoice');
            $routes->post('payments', '\Modules\Finance\Controllers\FinanceWebController::recordPayment');
        });

        $routes->group('api/finance', ['filter' => 'api'], static function (RouteCollection $routes): void {
            $routes->get('invoices', '\Modules\Finance\Controllers\FinanceApiController::index');
        });
    }
}
