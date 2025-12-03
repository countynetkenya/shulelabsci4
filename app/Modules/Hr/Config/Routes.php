<?php

declare(strict_types=1);

namespace Modules\Hr\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('hr', static function (RouteCollection $routes): void {
            $routes->post('payroll/payslips', 'Modules\\Hr\\Controllers\\PayrollController::create');
            $routes->get('payroll/approvals', 'Modules\\Hr\\Controllers\\PayrollApprovalController::index');
            $routes->get('payroll/approvals/pending', 'Modules\\Hr\\Controllers\\PayrollApprovalController::pending');
            $routes->post('payroll/approvals/(:num)/approve', 'Modules\\Hr\\Controllers\\PayrollApprovalController::approve/$1');
            $routes->post('payroll/approvals/(:num)/reject', 'Modules\\Hr\\Controllers\\PayrollApprovalController::reject/$1');

            $routes->group('employees', static function (RouteCollection $routes): void {
                $routes->get('/', '\Modules\Hr\Controllers\EmployeesController::index');
                $routes->get('create', '\Modules\Hr\Controllers\EmployeesController::create');
                $routes->post('create', '\Modules\Hr\Controllers\EmployeesController::store');
                $routes->get('edit/(:num)', '\Modules\Hr\Controllers\EmployeesController::edit/$1');
                $routes->post('edit/(:num)', '\Modules\Hr\Controllers\EmployeesController::update/$1');
                $routes->get('delete/(:num)', '\Modules\Hr\Controllers\EmployeesController::delete/$1');
            });
        });
    }
}
