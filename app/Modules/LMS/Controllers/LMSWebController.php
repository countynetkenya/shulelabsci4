<?php

namespace Modules\LMS\Controllers;

use App\Controllers\BaseController;

class LMSWebController extends BaseController
{
    public function index()
    {
        return view('Modules\LMS\Views\index', [
            'title' => 'LMS Dashboard',
        ]);
    }
}
