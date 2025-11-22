<?php

declare(strict_types=1);

namespace Modules\Foundation\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Registers module specific routes under the /v2 namespace.
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Installer routes (no filter - controlled by InstallController)
        $routes->get('install', 'Modules\\Foundation\\Controllers\\InstallController::index');
        $routes->get('install/tenants', 'Modules\\Foundation\\Controllers\\InstallController::tenants');
        $routes->post('install/tenants', 'Modules\\Foundation\\Controllers\\InstallController::tenants');
        $routes->get('install/admin', 'Modules\\Foundation\\Controllers\\InstallController::admin');
        $routes->post('install/admin', 'Modules\\Foundation\\Controllers\\InstallController::admin');

        $routes->group('v2/system', static function (RouteCollection $routes): void {
            $routes->get('health', 'Modules\\Foundation\\Controllers\\HealthController::index');
        });

        $routes->group('v2/operations', static function (RouteCollection $routes): void {
            $routes->get('dashboard', 'Modules\\Foundation\\Controllers\\OperationsDashboardController::index');
            $routes->get('mobile-snapshots', 'Modules\\Foundation\\Controllers\\OperationsDashboardController::mobileSnapshots');
        });
    }
}
