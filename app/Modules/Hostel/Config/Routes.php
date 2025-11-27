<?php

namespace Modules\Hostel\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('api/hostel', ['namespace' => 'Modules\Hostel\Controllers'], function ($routes) {
            // Hostels
            $routes->get('hostels', 'HostelController::index');
            $routes->get('hostels/(:num)', 'HostelController::show/$1');
            $routes->post('hostels', 'HostelController::create');
            $routes->put('hostels/(:num)', 'HostelController::update/$1');
            $routes->delete('hostels/(:num)', 'HostelController::delete/$1');

            // Rooms
            $routes->get('rooms', 'HostelRoomController::index');
            $routes->get('rooms/(:num)', 'HostelRoomController::show/$1');
            $routes->post('rooms', 'HostelRoomController::create');
            $routes->put('rooms/(:num)', 'HostelRoomController::update/$1');
            $routes->delete('rooms/(:num)', 'HostelRoomController::delete/$1');

            // Beds
            $routes->get('beds', 'HostelBedController::index');
            $routes->get('beds/(:num)', 'HostelBedController::show/$1');
            $routes->post('beds', 'HostelBedController::create');
            $routes->put('beds/(:num)', 'HostelBedController::update/$1');
            $routes->delete('beds/(:num)', 'HostelBedController::delete/$1');

            // Allocations
            $routes->get('allocations', 'HostelAllocationController::index');
            $routes->get('allocations/(:num)', 'HostelAllocationController::show/$1');
            $routes->post('allocations', 'HostelAllocationController::create');
            $routes->put('allocations/(:num)', 'HostelAllocationController::update/$1');
            $routes->delete('allocations/(:num)', 'HostelAllocationController::delete/$1');

            // Requests
            $routes->get('requests', 'HostelRequestController::index');
            $routes->get('requests/(:num)', 'HostelRequestController::show/$1');
            $routes->post('requests', 'HostelRequestController::create');
            $routes->put('requests/(:num)', 'HostelRequestController::update/$1');
            $routes->delete('requests/(:num)', 'HostelRequestController::delete/$1');
        });
    }
}
