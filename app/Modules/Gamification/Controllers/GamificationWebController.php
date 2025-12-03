<?php

namespace Modules\Gamification\Controllers;

use App\Controllers\BaseController;

class GamificationWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Gamification\Views\index', [
            'title' => 'Gamification Dashboard'
        ]);
    }
}
