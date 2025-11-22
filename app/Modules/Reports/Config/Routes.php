<?php

declare(strict_types=1);

namespace Modules\Reports\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Registers routes for the Reports module.
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Reports API routes
        $routes->group('api/reports', static function (RouteCollection $routes): void {
            // Report CRUD
            $routes->get('/', 'Modules\\Reports\\Controllers\\ReportController::index');
            $routes->post('/', 'Modules\\Reports\\Controllers\\ReportController::create');
            $routes->get('(:num)', 'Modules\\Reports\\Controllers\\ReportController::show/$1');
            $routes->put('(:num)', 'Modules\\Reports\\Controllers\\ReportController::update/$1');
            $routes->delete('(:num)', 'Modules\\Reports\\Controllers\\ReportController::delete/$1');
            
            // Execute report
            $routes->get('(:num)/execute', 'Modules\\Reports\\Controllers\\ReportController::execute/$1');
            
            // Export
            $routes->get('(:num)/export', 'Modules\\Reports\\Controllers\\ExportController::export/$1');
            $routes->post('(:num)/export', 'Modules\\Reports\\Controllers\\ExportController::exportWithFilters/$1');
            $routes->get('export/formats', 'Modules\\Reports\\Controllers\\ExportController::formats');
            
            // Builder
            $routes->get('builder/metadata', 'Modules\\Reports\\Controllers\\BuilderController::metadata');
            $routes->get('builder/data-sources', 'Modules\\Reports\\Controllers\\BuilderController::dataSources');
            $routes->get('builder/fields/(:segment)', 'Modules\\Reports\\Controllers\\BuilderController::fields/$1');
            $routes->get('builder/templates', 'Modules\\Reports\\Controllers\\BuilderController::templates');
            $routes->post('builder/validate', 'Modules\\Reports\\Controllers\\BuilderController::validate');
            $routes->post('builder/preview', 'Modules\\Reports\\Controllers\\BuilderController::preview');
            
            // Dashboard
            $routes->get('dashboard', 'Modules\\Reports\\Controllers\\DashboardController::index');
            $routes->get('dashboard/stats', 'Modules\\Reports\\Controllers\\DashboardController::stats');
            $routes->get('dashboard/widget/(:num)', 'Modules\\Reports\\Controllers\\DashboardController::widget/$1');
            $routes->post('dashboard/widget/(:num)', 'Modules\\Reports\\Controllers\\DashboardController::widget/$1');
        });
    }
}
