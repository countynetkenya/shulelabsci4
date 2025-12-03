<?php

namespace Modules\Governance\Controllers;

use App\Controllers\BaseController;

class GovernanceWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Governance\Views\index', [
            'title' => 'Governance Dashboard'
        ]);
    }
}
