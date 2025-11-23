<?php

use CodeIgniter\Router\RouteCollection;
use Modules\Finance\Config\Routes as FinanceRoutes;
use Modules\Foundation\Config\Routes as FoundationRoutes;
use Modules\Hr\Config\Routes as HrRoutes;
use Modules\Inventory\Config\Routes as InventoryRoutes;
use Modules\Learning\Config\Routes as LearningRoutes;
use Modules\Library\Config\Routes as LibraryRoutes;
use Modules\Mobile\Config\Routes as MobileRoutes;
use Modules\Threads\Config\Routes as ThreadsRoutes;

/**
 * @var RouteCollection $routes
 */

// Installation Routes (only when not installed)
$routes->group('install', static function ($routes) {
    $routes->get('/', 'Install::index');
    $routes->get('check', 'Install::checkEnvironment');
    $routes->post('organization', 'Install::createOrganization');
    $routes->post('admin', 'Install::createAdmin');
    $routes->post('complete', 'Install::complete');
});

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
$routes->group('admin', ['filter' => ['auth', 'admin']], static function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('users', 'Admin::users');
    $routes->post('users/create', 'Admin::createUser');
    $routes->post('users/update/(:num)', 'Admin::updateUser/$1');
    $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');
    $routes->get('schools', 'Admin::schools');
    $routes->post('schools/update', 'Admin::updateSchool');
    $routes->get('settings', 'Admin::settings');
    $routes->post('settings/update', 'Admin::updateSettings');
    $routes->get('reports', 'Admin::reports');
    $routes->get('finance', 'Admin::finance');
});

// Teacher Portal (Teacher role only)
$routes->group('teacher', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Teacher::index');
    $routes->get('classes', 'Teacher::classes');
    $routes->get('class/(:num)/students', 'Teacher::students/$1');
    $routes->get('assignments', 'Teacher::assignments');
    $routes->post('assignment/create', 'Teacher::createAssignment');
    $routes->get('grading', 'Teacher::grading');
    $routes->post('grade/submit', 'Teacher::submitGrade');
    $routes->post('announcement/create', 'Teacher::createAnnouncement');
});

// Student Portal (Student role only)
$routes->group('student', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Student::index');
    $routes->get('courses', 'Student::courses');
    $routes->get('course/(:num)/materials', 'Student::materials/$1');
    $routes->get('assignments', 'Student::assignments');
    $routes->post('assignment/submit', 'Student::submitAssignment');
    $routes->get('grades', 'Student::grades');
});

// Parent Portal (Parent role only)
$routes->group('parent', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'ParentPortal::index');
    $routes->get('children', 'ParentPortal::children');
    $routes->get('child/(:num)/attendance', 'ParentPortal::attendance/$1');
    $routes->get('child/(:num)/grades', 'ParentPortal::grades/$1');
    $routes->get('child/(:num)/assignments', 'ParentPortal::assignments/$1');
    $routes->post('message/send', 'ParentPortal::sendMessage');
});

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
