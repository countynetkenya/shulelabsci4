<?php

declare(strict_types=1);

namespace Modules\Foundation\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Registers module specific routes.
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('system', static function (RouteCollection $routes): void {
            $routes->get('health', 'Modules\\Foundation\\Controllers\\HealthController::index');
        });

        $routes->group('operations', static function (RouteCollection $routes): void {
            $routes->get('dashboard', 'Modules\\Foundation\\Controllers\\OperationsDashboardController::index');
            $routes->get('mobile-snapshots', 'Modules\\Foundation\\Controllers\\OperationsDashboardController::mobileSnapshots');
        });
    }
}
