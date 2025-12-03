<?php

namespace Modules\Wallets\Controllers;

use App\Controllers\BaseController;

class WalletWebController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'My Wallet',
        ];

        return view('Modules\Wallets\Views\index', $data);
    }
}
