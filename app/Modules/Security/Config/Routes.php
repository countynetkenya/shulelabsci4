<?php

namespace App\Modules\Security\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Security module routes.
 *
 * @var RouteCollection $routes
 */

// API Routes
$routes->group('api/v1/security', ['namespace' => 'App\Modules\Security\Controllers\Api', 'filter' => 'auth'], static function ($routes) {
    // Roles
    $routes->get('roles', 'RoleController::index');
    $routes->post('roles', 'RoleController::create');
    $routes->get('roles/(:num)', 'RoleController::show/$1');
    $routes->put('roles/(:num)', 'RoleController::update/$1');
    $routes->delete('roles/(:num)', 'RoleController::delete/$1');
    $routes->get('roles/(:num)/permissions', 'RoleController::permissions/$1');
    $routes->post('roles/(:num)/permissions', 'RoleController::assignPermissions/$1');

    // Permissions
    $routes->get('permissions', 'PermissionController::index');
    $routes->get('permissions/grouped', 'PermissionController::grouped');

    // User roles
    $routes->post('users/(:num)/roles', 'UserRoleController::assign/$1');
    $routes->delete('users/(:num)/roles/(:num)', 'UserRoleController::remove/$1/$2');
    $routes->get('users/(:num)/roles', 'UserRoleController::get/$1');
    $routes->get('users/(:num)/permissions', 'UserRoleController::permissions/$1');
});

// 2FA Routes
$routes->group('api/v1/auth/2fa', ['namespace' => 'App\Modules\Security\Controllers\Api', 'filter' => 'auth'], static function ($routes) {
    $routes->post('setup', 'TwoFactorController::setup');
    $routes->post('verify', 'TwoFactorController::verify');
    $routes->post('disable', 'TwoFactorController::disable');
    $routes->get('status', 'TwoFactorController::status');
});

// Admin Security Settings
$routes->group('admin/security', ['namespace' => 'App\Modules\Security\Controllers\Web', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'SecuritySettingsController::index');
    $routes->get('roles', 'SecuritySettingsController::roles');
    $routes->get('permissions', 'SecuritySettingsController::permissions');
    $routes->get('audit', 'SecuritySettingsController::audit');
    $routes->get('ip-whitelist', 'SecuritySettingsController::ipWhitelist');
    $routes->get('password-policy', 'SecuritySettingsController::passwordPolicy');
});
