<?php

namespace Modules\Mobile\Controllers;

use App\Controllers\BaseController;

class MobileWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Mobile\Views\index', [
            'title' => 'Mobile Module Dashboard',
        ]);
    }
}
