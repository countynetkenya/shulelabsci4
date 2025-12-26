<?php

namespace Modules\Gamification\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Gamification Module Routes Configuration.
 *
 * Web Routes: /gamification/*
 * API Routes: /api/gamification/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Badges & Achievements
        $routes->group('gamification', ['namespace' => 'Modules\Gamification\Controllers', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('/', 'GamificationWebController::index');
            $routes->get('create', 'GamificationWebController::create');
            $routes->post('store', 'GamificationWebController::store');
            $routes->get('edit/(:num)', 'GamificationWebController::edit/$1');
            $routes->post('update/(:num)', 'GamificationWebController::update/$1');
            $routes->get('delete/(:num)', 'GamificationWebController::delete/$1');
        });

        // API Routes
        $routes->group('api/gamification', ['namespace' => 'Modules\Gamification\Controllers\Api', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('leaderboard', 'GamificationApiController::leaderboard');
            $routes->post('award', 'GamificationApiController::award');
        });
    }
}
