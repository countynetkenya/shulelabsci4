<?php

namespace Modules\Library\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('library', ['namespace' => 'Modules\Library\Controllers'], static function (RouteCollection $routes): void {
            $routes->get('', 'LibraryWebController::index');
        });

        // API Routes
        $routes->group('api/library', ['namespace' => 'Modules\Library\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->post('documents', 'DocumentApiController::create');
        });
    }
}
