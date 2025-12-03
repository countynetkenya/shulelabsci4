<?php

namespace Modules\ParentEngagement\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('parent-engagement', ['namespace' => 'Modules\ParentEngagement\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'ParentEngagementWebController::index');
        });

        // API Routes
        $routes->group('api/parent-engagement', ['namespace' => 'Modules\ParentEngagement\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('messages', 'ParentEngagementApiController::messages');
            $routes->post('send', 'ParentEngagementApiController::send');
        });
    }
}
