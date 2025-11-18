<?php

declare(strict_types=1);

namespace Modules\Mobile\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('mobile', ['filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->post('snapshots', '\\Modules\\Mobile\\Controllers\\SnapshotController::issue');
            $routes->post('snapshots/verify', '\\Modules\\Mobile\\Controllers\\SnapshotController::verify');
            $routes->get('telemetry/snapshots', '\\Modules\\Mobile\\Controllers\\SnapshotController::telemetry');
        });
    }
}
