<?php

namespace Modules\Wallets\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('api/v1/wallets', ['namespace' => 'Modules\Wallets\Controllers\Api'], static function ($routes) {
            $routes->get('/', 'WalletController::index');
            $routes->get('my', 'WalletController::myWallet');
            $routes->get('balance', 'WalletController::balance');
            $routes->get('transactions', 'WalletController::transactions');
            $routes->post('topup', 'WalletController::topup');
            $routes->post('transfer', 'WalletController::transfer');
            $routes->post('(:num)/limits', 'WalletController::setLimits/$1');
            $routes->post('topup/cash', 'WalletController::cashTopup');
            $routes->put('(:num)/deactivate', 'WalletController::deactivate/$1');
            
            $routes->get('(:num)', 'WalletController::show/$1');
            $routes->get('(:num)/transactions', 'WalletController::showTransactions/$1');
            $routes->get('(:num)/balance', 'WalletController::showBalance/$1');
        });
    }
}
