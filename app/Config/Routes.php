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
use Modules\Hostel\Config\Routes as HostelRoutes;

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
    // Legacy routes
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

    // New module controllers & pages
    $routes->get('dashboard', 'Modules\\Admin\\Controllers\\Dashboard::index');
    // Students
    $routes->get('students', 'Modules\\Admin\\Controllers\\Students::index');
    $routes->get('students/create', 'Modules\\Admin\\Controllers\\Students::create');
    $routes->post('students/store', 'Modules\\Admin\\Controllers\\Students::store');
    // Teachers
    $routes->get('teachers', 'Modules\\Admin\\Controllers\\Teachers::index');
    $routes->get('teachers/create', 'Modules\\Admin\\Controllers\\Teachers::create');
    $routes->post('teachers/store', 'Modules\\Admin\\Controllers\\Teachers::store');
    // Classes
    $routes->get('classes', 'Modules\\Admin\\Controllers\\Classes::index');
    $routes->get('classes/create', 'Modules\\Admin\\Controllers\\Classes::create');
    $routes->post('classes/store', 'Modules\\Admin\\Controllers\\Classes::store');
    // SuperAdmin management
    $routes->get('schools', 'App\\Controllers\\Admin\\Schools::index');
    $routes->get('schools/create', 'App\\Controllers\\Admin\\Schools::create');
    $routes->post('schools/store', 'App\\Controllers\\Admin\\Schools::store');
    $routes->get('users', 'App\\Controllers\\Admin\\Users::index');
    $routes->get('users/create', 'App\\Controllers\\Admin\\Users::create');
    $routes->post('users/store', 'App\\Controllers\\Admin\\Users::store');
});

// Teacher Portal (Teacher role only)
$routes->group('teacher', ['filter' => 'auth'], static function ($routes) {
    // Legacy
    $routes->get('/', 'Teacher::index');
    $routes->get('classes', 'Teacher::classes');
    $routes->get('class/(:num)/students', 'Teacher::students/$1');
    $routes->get('assignments', 'Teacher::assignments');
    $routes->post('assignment/create', 'Teacher::createAssignment');
    $routes->get('grading', 'Teacher::grading');
    $routes->post('grade/submit', 'Teacher::submitGrade');
    $routes->post('announcement/create', 'Teacher::createAnnouncement');

    // New module pages
    $routes->get('dashboard', 'Modules\\Teacher\\Controllers\\Dashboard::index');
    $routes->get('gradebook', 'Modules\\Teacher\\Controllers\\Gradebook::index');
    $routes->get('attendance', 'Modules\\Teacher\\Controllers\\Attendance::index');
});

// Student Portal (Student role only)
$routes->group('student', ['filter' => 'auth'], static function ($routes) {
    // Legacy
    $routes->get('/', 'Student::index');
    $routes->get('courses', 'Student::courses');
    $routes->get('course/(:num)/materials', 'Student::materials/$1');
    $routes->get('assignments', 'Student::assignments');
    $routes->post('assignment/submit', 'Student::submitAssignment');
    $routes->get('grades', 'Student::grades');

    // New module pages
    $routes->get('dashboard', 'Modules\\Student\\Controllers\\Dashboard::index');
    $routes->get('library', 'Modules\\Student\\Controllers\\Library::index');
    $routes->get('attendance', 'Modules\\Student\\Controllers\\Attendance::index');
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
HostelRoutes::map($routes);
