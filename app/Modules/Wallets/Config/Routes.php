<?php

namespace Modules\Wallets\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // Web Routes
        $routes->group('wallets', ['namespace' => 'Modules\Wallets\Controllers', 'filter' => 'auth'], function ($routes) {
            $routes->get('/', 'WalletWebController::index');
        });

        // API Routes
        $routes->group('api/v1/wallets', ['namespace' => 'Modules\Wallets\Controllers\Api'], static function ($routes) {
            $routes->get('/', 'WalletApiController::index');
            $routes->get('my', 'WalletApiController::myWallet');
            $routes->get('balance', 'WalletApiController::balance');
            $routes->get('transactions', 'WalletApiController::transactions');
            $routes->post('topup', 'WalletApiController::topup');
            $routes->post('transfer', 'WalletApiController::transfer');
            $routes->post('(:num)/limits', 'WalletApiController::setLimits/$1');
            $routes->post('topup/cash', 'WalletApiController::cashTopup');
            $routes->put('(:num)/deactivate', 'WalletApiController::deactivate/$1');
            
            $routes->get('(:num)', 'WalletController::show/$1');
            $routes->get('(:num)/transactions', 'WalletController::showTransactions/$1');
            $routes->get('(:num)/balance', 'WalletController::showBalance/$1');
        });
    }
}
