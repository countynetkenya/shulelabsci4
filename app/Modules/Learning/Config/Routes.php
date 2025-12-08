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
        });

        $routes->group('api/learning', ['namespace' => 'Modules\Learning\Controllers\Api'], function ($routes) {
            $routes->get('courses', 'CoursesController::index');
            $routes->get('courses/(:num)', 'CoursesController::show/$1');
        });
    }
}
