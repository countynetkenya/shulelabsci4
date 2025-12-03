<?php

namespace Modules\Scheduler\Controllers;

use App\Controllers\BaseController;

class SchedulerWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Scheduler\Views\index', [
            'title' => 'Scheduler Dashboard'
        ]);
    }
}
