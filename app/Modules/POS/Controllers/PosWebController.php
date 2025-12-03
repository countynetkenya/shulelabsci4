<?php

namespace Modules\POS\Controllers;

use App\Controllers\BaseController;

class PosWebController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Point of Sale',
        ];

        return view('Modules\POS\Views\index', $data);
    }

    public function sales()
    {
        return "Sales History";
    }
}
