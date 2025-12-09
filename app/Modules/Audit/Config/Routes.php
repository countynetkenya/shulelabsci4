<?php

namespace App\Modules\Audit\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Audit module routes.
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // API Routes
        $routes->group('api/v1/audit', ['namespace' => 'App\Modules\Audit\Controllers\Api', 'filter' => 'auth'], static function ($routes) {
            // Audit events
            $routes->get('events', 'AuditController::index');
            $routes->get('events/(:num)', 'AuditController::show/$1');
            $routes->get('events/search', 'AuditController::search');

            // Entity history
            $routes->get('entity/(:segment)/(:num)', 'AuditController::entityHistory/$1/$2');

            // Trace correlation
            $routes->get('trace/(:segment)', 'AuditController::trace/$1');

            // Integrity verification
            $routes->get('verify', 'AuditController::verify');

            // GDPR export
            $routes->get('export/user/(:num)', 'AuditController::exportUser/$1');

            // Retention policies
            $routes->get('policies', 'RetentionController::index');
            $routes->post('policies', 'RetentionController::create');
            $routes->put('policies/(:num)', 'RetentionController::update/$1');
            $routes->delete('policies/(:num)', 'RetentionController::delete/$1');

            // Archives
            $routes->get('archives', 'ArchiveController::index');
            $routes->post('archives/create', 'ArchiveController::create');
            $routes->get('archives/(:num)/download', 'ArchiveController::download/$1');
        });

        // Admin Routes
        $routes->group('admin/audit', ['namespace' => 'App\Modules\Audit\Controllers\Web', 'filter' => 'auth'], static function ($routes) {
            $routes->get('/', 'AuditDashboardController::index');
            $routes->get('events', 'AuditDashboardController::events');
            $routes->get('search', 'AuditDashboardController::search');
            $routes->get('integrity', 'AuditDashboardController::integrity');
            $routes->get('retention', 'AuditDashboardController::retention');
        });
    }
}
