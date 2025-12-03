<?php

namespace Modules\Inventory\Controllers;

use App\Controllers\BaseController;

class InventoryWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Inventory\Views\index', [
            'title' => 'Inventory Dashboard',
        ]);
    }
}
