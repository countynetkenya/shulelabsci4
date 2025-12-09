<?php

namespace Modules\Governance\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Governance Module Routes Configuration
 * 
 * Web Routes: /governance/*
 * API Routes: /api/governance/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Governance Policies
        $routes->group('governance', ['namespace' => 'Modules\Governance\Controllers', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('/', 'GovernanceWebController::index');
            $routes->get('create', 'GovernanceWebController::create');
            $routes->post('store', 'GovernanceWebController::store');
            $routes->get('edit/(:num)', 'GovernanceWebController::edit/$1');
            $routes->post('update/(:num)', 'GovernanceWebController::update/$1');
            $routes->get('delete/(:num)', 'GovernanceWebController::delete/$1');
        });

        // API Routes
        $routes->group('api/governance', ['namespace' => 'Modules\Governance\Controllers\Api', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('policies', 'GovernanceApiController::policies');
            $routes->post('vote', 'GovernanceApiController::vote');
        });
    }
}
