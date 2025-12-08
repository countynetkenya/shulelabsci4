<?php

namespace Modules\Hostel\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('hostel', ['namespace' => 'Modules\Hostel\Controllers\Web'], function ($routes) {
            $routes->get('hostels', 'HostelController::index');
        });

        // API Routes
        $routes->group('api/hostel', ['namespace' => 'Modules\Hostel\Controllers\Api'], function ($routes) {
            // Hostels
            $routes->get('hostels', 'HostelApiController::index');
            $routes->get('hostels/(:num)', 'HostelApiController::show/$1');
            $routes->post('hostels', 'HostelApiController::create');
            $routes->put('hostels/(:num)', 'HostelApiController::update/$1');
            $routes->delete('hostels/(:num)', 'HostelApiController::delete/$1');

            // Rooms
            $routes->get('rooms', 'HostelRoomApiController::index');
            $routes->get('rooms/(:num)', 'HostelRoomApiController::show/$1');
            $routes->post('rooms', 'HostelRoomApiController::create');
            $routes->put('rooms/(:num)', 'HostelRoomApiController::update/$1');
            $routes->delete('rooms/(:num)', 'HostelRoomApiController::delete/$1');

            // Beds
            $routes->get('beds', 'HostelBedApiController::index');
            $routes->get('beds/(:num)', 'HostelBedApiController::show/$1');
            $routes->post('beds', 'HostelBedApiController::create');
            $routes->put('beds/(:num)', 'HostelBedApiController::update/$1');
            $routes->delete('beds/(:num)', 'HostelBedApiController::delete/$1');

            // Allocations
            $routes->get('allocations', 'HostelAllocationApiController::index');
            $routes->get('allocations/(:num)', 'HostelAllocationApiController::show/$1');
            $routes->post('allocations', 'HostelAllocationApiController::create');
            $routes->put('allocations/(:num)', 'HostelAllocationApiController::update/$1');
            $routes->delete('allocations/(:num)', 'HostelAllocationApiController::delete/$1');

            // Requests
            $routes->get('requests', 'HostelRequestApiController::index');
            $routes->get('requests/(:num)', 'HostelRequestApiController::show/$1');
            $routes->post('requests', 'HostelRequestApiController::create');
            $routes->put('requests/(:num)', 'HostelRequestApiController::update/$1');
            $routes->delete('requests/(:num)', 'HostelRequestApiController::delete/$1');
        });
    }
}
