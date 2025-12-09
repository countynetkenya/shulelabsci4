<?php

namespace Modules\POS\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * POS Module Routes Configuration
 * 
 * Web Routes: /pos/*
 * API Routes: /api/pos/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for POS Products
        $routes->group('pos', ['namespace' => 'App\Modules\POS\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'PosController::index');
            $routes->get('create', 'PosController::create');
            $routes->post('store', 'PosController::store');
            $routes->get('edit/(:num)', 'PosController::edit/$1');
            $routes->post('update/(:num)', 'PosController::update/$1');
            $routes->get('delete/(:num)', 'PosController::delete/$1');
        });

        // API Routes
        $routes->group('api/pos', ['namespace' => 'Modules\POS\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('products', 'PosApiController::index');
            $routes->get('products/(:num)', 'PosApiController::show/$1');
            $routes->post('products', 'PosApiController::create');
            $routes->put('products/(:num)', 'PosApiController::update/$1');
            $routes->delete('products/(:num)', 'PosApiController::delete/$1');
            $routes->get('registers', 'PosApiController::registers');
            $routes->post('transactions', 'PosApiController::createTransaction');
        });
    }
}

