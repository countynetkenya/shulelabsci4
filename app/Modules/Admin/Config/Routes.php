<?php

namespace Modules\Admin\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // API Routes
        $routes->group('api/admin', ['namespace' => 'Modules\Admin\Controllers\Api', 'filter' => 'auth'], function ($routes) {
            $routes->get('students', 'StudentsApiController::index');
            $routes->get('teachers', 'TeachersApiController::index');
            $routes->get('classes', 'ClassesApiController::index');
        });
    }
}
