<?php

namespace Modules\Security\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('security', ['namespace' => 'Modules\Security\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'SecurityWebController::index');
        });

        // API Routes
        $routes->group('api/security', ['namespace' => 'Modules\Security\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('roles', 'SecurityApiController::roles');
            $routes->get('permissions', 'SecurityApiController::permissions');
        });
    }
}
