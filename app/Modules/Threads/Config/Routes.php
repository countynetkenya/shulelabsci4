<?php

namespace Modules\Threads\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('v2/threads', static function (RouteCollection $routes): void {
            $routes->get('', '\\Modules\\Threads\\Controllers\\ThreadController::index');
            $routes->post('', '\\Modules\\Threads\\Controllers\\ThreadController::create');
            $routes->post('(:segment)/messages', '\\Modules\\Threads\\Controllers\\ThreadController::postMessage/$1');
        });
    }
}
