<?php

declare(strict_types=1);

namespace Modules\LMS\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('lms', ['namespace' => 'Modules\LMS\Controllers', 'filter' => 'auth'], static function (RouteCollection $routes): void {
            $routes->get('', 'LMSWebController::index');

            // Existing routes
            $routes->get('courses', 'CoursesWebController::index');
            $routes->get('courses/new', 'CoursesWebController::new');
            $routes->post('courses', 'CoursesWebController::create');
            $routes->get('courses/(:num)', 'CoursesWebController::show/$1');
            $routes->get('courses/(:num)/edit', 'CoursesWebController::edit/$1');
            $routes->post('courses/(:num)', 'CoursesWebController::update/$1');

            $routes->get('courses/(:num)/lessons/new', 'LessonsWebController::new/$1');
            $routes->post('courses/(:num)/lessons', 'LessonsWebController::create/$1');
            $routes->get('lessons/(:num)/edit', 'LessonsWebController::edit/$1');
            $routes->post('lessons/(:num)', 'LessonsWebController::update/$1');
        });

        // LMS CRUD Routes (alternate management interface)
        $routes->group('lms/courses', ['namespace' => 'Modules\LMS\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'LMSCourseController::index');
            $routes->get('create', 'LMSCourseController::create');
            $routes->post('store', 'LMSCourseController::store');
            $routes->get('edit/(:num)', 'LMSCourseController::edit/$1');
            $routes->post('update/(:num)', 'LMSCourseController::update/$1');
            $routes->get('delete/(:num)', 'LMSCourseController::delete/$1');
        });

        // API Routes
        $routes->group('api/lms', ['namespace' => 'Modules\LMS\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('courses', 'CoursesApiController::index');
            $routes->get('courses/(:num)', 'CoursesApiController::show/$1');
            $routes->get('lessons/(:num)', 'LessonsApiController::show/$1');
            $routes->post('lessons/(:num)/complete', 'LessonsApiController::complete/$1');
            
            // Moodle Sync
            $routes->post('moodle/grades', 'MoodleSyncApiController::pushGrades');
            $routes->post('moodle/enrollments', 'MoodleSyncApiController::syncEnrollments');
        });
    }
}
