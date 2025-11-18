<?php

declare(strict_types=1);

namespace Modules\Hr\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('hr', ['filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->post('payroll/payslips', '\\Modules\\Hr\\Controllers\\PayrollController::create');
            $routes->get('payroll/approvals', '\\Modules\\Hr\\Controllers\\PayrollApprovalController::index');
            $routes->get('payroll/approvals/pending', '\\Modules\\Hr\\Controllers\\PayrollApprovalController::pending');
            $routes->post('payroll/approvals/(:num)/approve', '\\Modules\\Hr\\Controllers\\PayrollApprovalController::approve/$1');
            $routes->post('payroll/approvals/(:num)/reject', '\\Modules\\Hr\\Controllers\\PayrollApprovalController::reject/$1');
        });
    }
}
