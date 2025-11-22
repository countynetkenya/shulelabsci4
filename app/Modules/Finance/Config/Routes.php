<?php

namespace Modules\Finance\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('finance', static function (RouteCollection $routes): void {
            $routes->get('ping', 'Modules\\Finance\\Controllers\\HealthController::index');
            $routes->post('invoices', 'Modules\\Finance\\Controllers\\InvoiceController::create');
            $routes->post('invoices/(:segment)/settle', 'Modules\\Finance\\Controllers\\InvoiceController::settle/$1');
        });
    }
}
