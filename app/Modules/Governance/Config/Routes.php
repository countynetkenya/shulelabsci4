<?php

namespace Modules\Governance\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('governance', ['namespace' => 'Modules\Governance\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'GovernanceWebController::index');
        });

        // API Routes
        $routes->group('api/governance', ['namespace' => 'Modules\Governance\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('policies', 'GovernanceApiController::policies');
            $routes->post('vote', 'GovernanceApiController::vote');
        });
    }
}
