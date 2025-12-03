<?php

namespace Modules\Analytics\Controllers;

use App\Controllers\BaseController;

class AnalyticsWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Analytics\Views\index', [
            'title' => 'Analytics Dashboard'
        ]);
    }
}
