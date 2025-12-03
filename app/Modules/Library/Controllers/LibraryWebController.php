<?php

namespace Modules\Library\Controllers;

use App\Controllers\BaseController;

class LibraryWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Library\Views\index');
    }
}
