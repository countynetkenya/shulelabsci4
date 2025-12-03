<?php

namespace Modules\Admissions\Controllers;

use App\Controllers\BaseController;

class AdmissionsWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Admissions\Views\index', [
            'title' => 'Admissions Portal'
        ]);
    }
}
