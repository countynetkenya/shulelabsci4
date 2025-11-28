<?php

namespace App\Modules\Scheduler\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Scheduler module routes.
 *
 * @var RouteCollection $routes
 */

// API Routes
$routes->group('api/v1/scheduler', ['namespace' => 'App\Modules\Scheduler\Controllers\Api', 'filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'ScheduledJobController::dashboard');

    // Scheduled Jobs CRUD
    $routes->get('jobs', 'ScheduledJobController::index');
    $routes->post('jobs', 'ScheduledJobController::create');
    $routes->get('jobs/(:num)', 'ScheduledJobController::show/$1');
    $routes->put('jobs/(:num)', 'ScheduledJobController::update/$1');
    $routes->delete('jobs/(:num)', 'ScheduledJobController::delete/$1');
    $routes->post('jobs/(:num)/toggle', 'ScheduledJobController::toggle/$1');
    $routes->post('jobs/(:num)/run', 'ScheduledJobController::run/$1');

    // Job Runs
    $routes->get('runs', 'JobRunController::index');
    $routes->get('runs/(:num)', 'JobRunController::show/$1');
    $routes->get('jobs/(:num)/runs', 'JobRunController::byJob/$1');
    $routes->post('runs/(:num)/retry', 'JobRunController::retry/$1');
    $routes->post('runs/(:num)/cancel', 'JobRunController::cancel/$1');

    // Failed Jobs
    $routes->get('failed', 'FailedJobController::index');
    $routes->post('failed/(:num)/retry', 'FailedJobController::retry/$1');
    $routes->delete('failed/(:num)', 'FailedJobController::delete/$1');
});

// Web Routes (Admin Dashboard)
$routes->group('admin/scheduler', ['namespace' => 'App\Modules\Scheduler\Controllers\Web', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'SchedulerDashboardController::index');
    $routes->get('jobs', 'SchedulerDashboardController::jobs');
    $routes->get('jobs/create', 'SchedulerDashboardController::create');
    $routes->get('jobs/(:num)', 'SchedulerDashboardController::show/$1');
    $routes->get('runs', 'SchedulerDashboardController::runs');
    $routes->get('failed', 'SchedulerDashboardController::failed');
});
