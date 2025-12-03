<?php

namespace Modules\ParentEngagement\Controllers;

use App\Controllers\BaseController;

class ParentEngagementWebController extends BaseController
{
    public function index()
    {
        return view('Modules\ParentEngagement\Views\index', [
            'title' => 'Parent Engagement Dashboard'
        ]);
    }
}
