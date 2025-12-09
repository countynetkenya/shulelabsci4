<?php

namespace Modules\Integrations\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Routes configuration for Integrations module.
 */
class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - CRUD for Integrations Management
        $routes->group('integrations', ['namespace' => 'App\Modules\Integrations\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'IntegrationsController::index');
            $routes->get('create', 'IntegrationsController::create');
            $routes->post('store', 'IntegrationsController::store');
            $routes->get('edit/(:num)', 'IntegrationsController::edit/$1');
            $routes->post('update/(:num)', 'IntegrationsController::update/$1');
            $routes->get('delete/(:num)', 'IntegrationsController::delete/$1');
        });

        // API Integration endpoints
        $routes->group('api/integrations', ['namespace' => 'Modules\Integrations\Controllers'], static function ($routes) {
            // Health check endpoint
            $routes->get('health', 'IntegrationController::health');
            $routes->get('health/(:segment)', 'IntegrationController::checkAdapter/$1');

            // Integration management
            $routes->get('/', 'IntegrationController::index');
            $routes->get('(:segment)/status', 'IntegrationController::status/$1');

            // Webhook receivers
            $routes->post('(:segment)/webhook', 'WebhookController::receive/$1');

            // OAuth callbacks
            $routes->get('(:segment)/oauth/callback', 'OAuthController::callback/$1');

            // Logs
            $routes->get('logs', 'LogController::index');
            $routes->get('logs/(:segment)', 'LogController::show/$1');
        });
    }
}
