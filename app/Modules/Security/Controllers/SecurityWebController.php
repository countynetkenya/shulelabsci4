<?php

namespace Modules\Security\Controllers;

use App\Controllers\BaseController;

class SecurityWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Security\Views\index', [
            'title' => 'Security Dashboard'
        ]);
    }
}
