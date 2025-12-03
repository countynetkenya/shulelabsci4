<?php

namespace Modules\Hostel\Controllers;

use App\Controllers\BaseController;
use Modules\Hostel\Models\HostelModel;

class HostelWebController extends BaseController
{
    protected $hostelModel;

    public function __construct()
    {
        $this->hostelModel = new HostelModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Hostel Management',
            'hostels' => $this->hostelModel->findAll(),
        ];

        return view('Modules\Hostel\Views\index', $data);
    }
}
