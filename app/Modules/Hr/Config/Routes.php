<?php

declare(strict_types=1);

namespace Modules\Hr\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('hr', ['namespace' => 'Modules\Hr\Controllers\Web'], static function (RouteCollection $routes): void {
            // $routes->get('', 'HrWebController::index'); // TODO: Move to Web/DashboardController
            
            // Payroll Approvals Web
            // $routes->get('payroll/approvals', 'PayrollApprovalWebController::index'); // TODO: Move

            // Employees Web
            $routes->group('employees', static function (RouteCollection $routes): void {
                $routes->get('', 'EmployeesController::index');
                $routes->get('create', 'EmployeesController::create');
                $routes->post('create', 'EmployeesController::store');
                $routes->get('edit/(:num)', 'EmployeesController::edit/$1');
                $routes->post('edit/(:num)', 'EmployeesController::update/$1');
                $routes->get('delete/(:num)', 'EmployeesController::delete/$1');
            });
        });

        // API Routes
        $routes->group('api/hr', ['namespace' => 'Modules\Hr\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->post('payroll/payslips', 'PayrollApiController::create');
            
            // Payroll Approvals API
            $routes->get('payroll/approvals/pending', 'PayrollApprovalApiController::pending');
            $routes->post('payroll/approvals/(:num)/approve', 'PayrollApprovalApiController::approve/$1');
            $routes->post('payroll/approvals/(:num)/reject', 'PayrollApprovalApiController::reject/$1');
        });
    }
}
