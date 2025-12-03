<?php

namespace Modules\Threads\Controllers;

use App\Controllers\BaseController;

class ThreadsWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Threads\Views\index');
    }
}
