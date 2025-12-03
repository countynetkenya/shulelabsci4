<?php

namespace Modules\Hr\Controllers;

use App\Controllers\BaseController;

class HrWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Hr\Views\index', [
            'title' => 'HR Dashboard',
        ]);
    }
}
