<?php

namespace Modules\Learning\Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Router\RouteCollection;

class Routes extends BaseConfig
{
    public static function map(RouteCollection $routes)
    {
        $routes->group('learning', ['namespace' => 'Modules\Learning\Controllers\Web'], function ($routes) {
            $routes->get('courses', 'CoursesController::index');
            $routes->get('courses/create', 'CoursesController::create');
            $routes->post('courses', 'CoursesController::store');
            $routes->get('courses/(:num)', 'CoursesController::show/$1');
            $routes->get('courses/(:num)/edit', 'CoursesController::edit/$1');
            $routes->post('courses/(:num)/update', 'CoursesController::update/$1');
            $routes->get('courses/(:num)/delete', 'CoursesController::delete/$1');

            // Lesson routes
            $routes->get('courses/(:num)/lessons/create', 'LessonsController::create/$1');
            $routes->post('courses/(:num)/lessons', 'LessonsController::store/$1');
        });

        $routes->group('api/learning', ['namespace' => 'Modules\Learning\Controllers\Api'], function ($routes) {
            $routes->get('courses', 'CoursesController::index');
            $routes->get('courses/(:num)', 'CoursesController::show/$1');

            $routes->get('enrollments', 'EnrollmentsController::index');
            $routes->post('enrollments', 'EnrollmentsController::create');

            $routes->post('progress', 'ProgressController::create');
            $routes->get('progress/(:num)', 'ProgressController::show/$1');
        });
    }
}
