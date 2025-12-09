<?php

namespace Modules\Transport\Controllers;

use App\Controllers\BaseController;

class TransportWebController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Transport Management',
        ];

        return view('Modules\Transport\Views\index', $data);
    }

    public function routes()
    {
        return 'Transport Routes';
    }

    public function vehicles()
    {
        return 'Transport Vehicles';
    }
}
