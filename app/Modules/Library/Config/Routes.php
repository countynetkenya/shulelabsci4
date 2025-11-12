<?php

namespace Modules\Library\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('v2/library', static function (RouteCollection $routes): void {
            $routes->post('documents', '\\Modules\\Library\\Controllers\\DocumentController::create');
        });
    }
}
