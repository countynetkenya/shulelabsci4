<?php

namespace Modules\Reports\Controllers;

use App\Controllers\BaseController;

class ReportsWebController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Reports Dashboard',
        ];

        return view('Modules\Reports\Views\index', $data);
    }
}
