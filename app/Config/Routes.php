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
$routes->get('/', 'LoginController::index');
$routes->get('/login', 'LoginController::index');
$routes->post('/login', 'LoginController::authenticate');

FoundationRoutes::map($routes);
FinanceRoutes::map($routes);
HrRoutes::map($routes);
LearningRoutes::map($routes);
InventoryRoutes::map($routes);
LibraryRoutes::map($routes);
MobileRoutes::map($routes);
ThreadsRoutes::map($routes);
