<?php

namespace Modules\Security\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - Standard CRUD for Security Logs
        $routes->group('security', ['namespace' => 'App\Modules\Security\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'SecurityController::index');
            $routes->get('create', 'SecurityController::create');
            $routes->post('/', 'SecurityController::store');
            $routes->get('(:num)/edit', 'SecurityController::edit/$1');
            $routes->put('(:num)', 'SecurityController::update/$1');
            $routes->post('(:num)', 'SecurityController::update/$1'); // Fallback for browsers without PUT
            $routes->delete('(:num)', 'SecurityController::delete/$1');
            $routes->get('(:num)/delete', 'SecurityController::delete/$1'); // Fallback for browsers without DELETE
        });

        // API Routes
        $routes->group('api/security', ['namespace' => 'Modules\Security\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('roles', 'SecurityApiController::roles');
            $routes->get('permissions', 'SecurityApiController::permissions');
        });
    }
}
