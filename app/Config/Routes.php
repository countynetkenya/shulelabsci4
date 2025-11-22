<?php

use CodeIgniter\Router\RouteCollection;
use Modules\Finance\Config\Routes as FinanceRoutes;
use Modules\Foundation\Config\Routes as FoundationRoutes;
use Modules\Hr\Config\Routes as HrRoutes;
use Modules\Learning\Config\Routes as LearningRoutes;
use Modules\Inventory\Config\Routes as InventoryRoutes;
use Modules\Library\Config\Routes as LibraryRoutes;
use Modules\Mobile\Config\Routes as MobileRoutes;
use Modules\Threads\Config\Routes as ThreadsRoutes;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes (Guest only)
$routes->group('auth', ['filter' => 'guest'], static function ($routes) {
    $routes->get('signin', 'Auth::signin');
    $routes->post('signin', 'Auth::signin');
});

// Signout (Auth required)
$routes->get('auth/signout', 'Auth::signout', ['filter' => 'auth']);

// School Selection (Auth required)
$routes->group('school', ['filter' => 'auth'], static function ($routes) {
    $routes->get('select', 'School::select');
    $routes->post('select', 'School::select');
});

// Dashboard (Auth required)
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

// Admin Panel (Admin only)
$routes->get('admin', 'Admin::index', ['filter' => ['auth', 'admin']]);

// Default route - redirect based on authentication and installation status
$routes->get('/', static function () {
    // Check if installed
    $envInstalled = env('app.installed', false);
    $isInstalled = filter_var($envInstalled, FILTER_VALIDATE_BOOLEAN);
    
    if (!$isInstalled) {
        // Not installed - redirect to installer
        return redirect()->to('/install');
    }
    
    // Installed - normal flow
    if (session()->get('loggedin')) {
        return redirect()->to('/dashboard');
    }
    return redirect()->to('/auth/signin');
});

// Module routes
FoundationRoutes::map($routes);
FinanceRoutes::map($routes);
HrRoutes::map($routes);
LearningRoutes::map($routes);
InventoryRoutes::map($routes);
LibraryRoutes::map($routes);
MobileRoutes::map($routes);
ThreadsRoutes::map($routes);
