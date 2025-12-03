<?php

namespace Modules\Threads\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('threads', ['namespace' => 'Modules\Threads\Controllers'], static function (RouteCollection $routes): void {
            $routes->get('', 'ThreadsWebController::index');
        });

        // API Routes
        $routes->group('api/threads', ['namespace' => 'Modules\Threads\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('', 'ThreadsApiController::index');
            $routes->post('', 'ThreadsApiController::create');
            $routes->post('(:segment)/messages', 'ThreadsApiController::postMessage/$1');
        });
    }
}
