<?php

namespace Modules\Integrations\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Routes configuration for Integrations module.
 */
$routes = service('routes');

// API v2 Integration endpoints
$routes->group('api/v2/integrations', ['namespace' => 'Modules\Integrations\Controllers'], static function ($routes) {
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
