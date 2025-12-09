<?php

namespace Modules\Library\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Library Module Routes Configuration.
 *
 * Web Routes: /library/*
 * API Routes: /api/library/*
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Library Books
        $routes->group('library', ['namespace' => 'App\Modules\Library\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'LibraryController::index');
            $routes->get('create', 'LibraryController::create');
            $routes->post('store', 'LibraryController::store');
            $routes->get('edit/(:num)', 'LibraryController::edit/$1');
            $routes->post('update/(:num)', 'LibraryController::update/$1');
            $routes->get('delete/(:num)', 'LibraryController::delete/$1');
        });

        // API Routes
        $routes->group('api/library', ['namespace' => 'App\Modules\Library\Controllers\Api'], static function (RouteCollection $routes): void {
            // Books API
            $routes->get('books', 'LibraryApiController::listBooks');
            $routes->get('books/(:num)', 'LibraryApiController::getBook/$1');
            $routes->post('books', 'LibraryApiController::createBook');
            $routes->put('books/(:num)', 'LibraryApiController::updateBook/$1');
            $routes->delete('books/(:num)', 'LibraryApiController::deleteBook/$1');

            // Documents API (existing)
            $routes->post('documents', 'DocumentApiController::create');
        });
    }
}
