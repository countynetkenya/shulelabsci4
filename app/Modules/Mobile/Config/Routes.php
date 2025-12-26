<?php

declare(strict_types=1);

namespace Modules\Mobile\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('mobile', ['namespace' => 'Modules\Mobile\Controllers', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('', 'MobileWebController::index');
        });

        // Mobile Device Management Routes
        $routes->group('mobile/devices', ['namespace' => 'Modules\Mobile\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'MobileDeviceController::index');
            $routes->get('create', 'MobileDeviceController::create');
            $routes->post('store', 'MobileDeviceController::store');
            $routes->get('edit/(:num)', 'MobileDeviceController::edit/$1');
            $routes->post('update/(:num)', 'MobileDeviceController::update/$1');
            $routes->get('delete/(:num)', 'MobileDeviceController::delete/$1');
        });

        // API Routes
        $routes->group('api/mobile', ['namespace' => 'Modules\Mobile\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->post('snapshots', 'SnapshotApiController::issue');
            $routes->post('snapshots/verify', 'SnapshotApiController::verify');
            $routes->get('telemetry/snapshots', 'SnapshotApiController::telemetry');
        });
    }
}
