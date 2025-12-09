<?php

namespace App\Modules\Transport\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Transport\Services\TransportVehicleService;

class TransportController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new TransportVehicleService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['vehicles'] = $this->service->getAll($schoolId);
        return view('App\Modules\Transport\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\Transport\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'registration_number' => 'required|min_length[3]',
            'capacity' => 'required|integer',
            'driver_name' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'registration_number' => $this->request->getPost('registration_number'),
            'capacity' => $this->request->getPost('capacity'),
            'driver_name' => $this->request->getPost('driver_name'),
            'status' => 'active',
        ];

        $this->service->create($data);

        return redirect()->to('/transport')->with('message', 'Vehicle created successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['vehicle'] = $this->service->getById($id, $schoolId);

        if (!$data['vehicle']) {
            return redirect()->to('/transport')->with('error', 'Vehicle not found');
        }

        return view('App\Modules\Transport\Views\edit', $data);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'registration_number' => 'required|min_length[3]',
            'capacity' => 'required|integer',
            'driver_name' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'registration_number' => $this->request->getPost('registration_number'),
            'capacity' => $this->request->getPost('capacity'),
            'driver_name' => $this->request->getPost('driver_name'),
            'status' => $this->request->getPost('status'),
        ];

        $this->service->update($id, $data, $schoolId);

        return redirect()->to('/transport')->with('message', 'Vehicle updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->service->delete($id, $schoolId);
        return redirect()->to('/transport')->with('message', 'Vehicle deleted successfully');
    }
}
