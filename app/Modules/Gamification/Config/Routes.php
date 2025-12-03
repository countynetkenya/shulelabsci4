<?php

namespace Modules\Gamification\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('gamification', ['namespace' => 'Modules\Gamification\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'GamificationWebController::index');
        });

        // API Routes
        $routes->group('api/gamification', ['namespace' => 'Modules\Gamification\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('leaderboard', 'GamificationApiController::leaderboard');
            $routes->post('award', 'GamificationApiController::award');
        });
    }
}
