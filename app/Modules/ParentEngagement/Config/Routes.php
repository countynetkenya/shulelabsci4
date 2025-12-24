<?php

namespace Modules\ParentEngagement\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes - Standard CRUD for Surveys
        $routes->group('parent-engagement', ['namespace' => 'App\Modules\ParentEngagement\Controllers\Web'], static function (RouteCollection $routes): void {
            $routes->get('/', 'ParentEngagementController::index');
            $routes->get('create', 'ParentEngagementController::create');
            $routes->post('/', 'ParentEngagementController::store');
            $routes->get('(:num)/edit', 'ParentEngagementController::edit/$1');
            $routes->put('(:num)', 'ParentEngagementController::update/$1');
            $routes->post('(:num)', 'ParentEngagementController::update/$1'); // Fallback for browsers without PUT
            $routes->delete('(:num)', 'ParentEngagementController::delete/$1');
            $routes->get('(:num)/delete', 'ParentEngagementController::delete/$1'); // Fallback for browsers without DELETE
        });

        // Additional Web Routes (Dashboard views)
        $routes->group('parent-engagement', ['namespace' => 'Modules\ParentEngagement\Controllers'], static function (RouteCollection $routes): void {
            // Surveys
            $routes->get('surveys', 'ParentEngagementWebController::surveys');
            $routes->get('surveys/create', 'ParentEngagementWebController::createSurvey');
            $routes->post('surveys/store', 'ParentEngagementWebController::storeSurvey');
            
            // Events
            $routes->get('events', 'ParentEngagementWebController::events');
            $routes->get('events/create', 'ParentEngagementWebController::createEvent');
            $routes->post('events/store', 'ParentEngagementWebController::storeEvent');
            $routes->get('events/edit/(:num)', 'ParentEngagementWebController::editEvent/$1');
            $routes->post('events/update/(:num)', 'ParentEngagementWebController::updateEvent/$1');
            $routes->get('events/delete/(:num)', 'ParentEngagementWebController::deleteEvent/$1');
            
            // Campaigns
            $routes->get('campaigns', 'ParentEngagementWebController::campaigns');
            $routes->get('campaigns/create', 'ParentEngagementWebController::createCampaign');
            $routes->post('campaigns/store', 'ParentEngagementWebController::storeCampaign');
        });

        // API Routes
        $routes->group('api/parent-engagement', ['namespace' => 'Modules\ParentEngagement\Controllers\Api'], static function (RouteCollection $routes): void {
            $routes->get('messages', 'ParentEngagementApiController::messages');
            $routes->post('send', 'ParentEngagementApiController::send');
        });
    }
}
