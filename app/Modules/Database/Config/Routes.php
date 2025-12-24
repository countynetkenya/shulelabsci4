<?php

namespace Modules\Database\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Database Module Routes Configuration
 * 
 * Web Routes: /database/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Database Backups
        $routes->group('database', ['namespace' => 'Modules\Database\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'DatabaseController::index');
            $routes->get('create', 'DatabaseController::create');
            $routes->post('store', 'DatabaseController::store');
            $routes->get('edit/(:num)', 'DatabaseController::edit/$1');
            $routes->post('update/(:num)', 'DatabaseController::update/$1');
            $routes->get('delete/(:num)', 'DatabaseController::delete/$1');
        });
    }
}
