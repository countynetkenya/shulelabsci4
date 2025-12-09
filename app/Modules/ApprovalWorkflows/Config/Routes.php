<?php

namespace Modules\ApprovalWorkflows\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * ApprovalWorkflows Module Routes Configuration
 * 
 * Web Routes: /approvals/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Approval Workflows CRUD Routes
        $routes->group('approvals', ['namespace' => 'App\Modules\ApprovalWorkflows\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'ApprovalController::index');
            $routes->get('create', 'ApprovalController::create');
            $routes->post('store', 'ApprovalController::store');
            $routes->get('edit/(:num)', 'ApprovalController::edit/$1');
            $routes->post('update/(:num)', 'ApprovalController::update/$1');
            $routes->get('delete/(:num)', 'ApprovalController::delete/$1');
        });
    }
}
