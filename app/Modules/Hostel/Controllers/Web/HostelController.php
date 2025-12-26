<?php

namespace App\Modules\Hostel\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Hostel\Services\HostelRoomService;

class HostelController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new HostelRoomService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['rooms'] = $this->service->getAll($schoolId);
        return view('App\Modules\Hostel\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\Hostel\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'room_number' => 'required',
            'capacity' => 'required|integer',
            'type' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'room_number' => $this->request->getPost('room_number'),
            'capacity' => $this->request->getPost('capacity'),
            'type' => $this->request->getPost('type'),
            'status' => 'available',
        ];

        $this->service->create($data);

        return redirect()->to('/hostel')->with('message', 'Room created successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['room'] = $this->service->getById($id, $schoolId);

        if (!$data['room']) {
            return redirect()->to('/hostel')->with('error', 'Room not found');
        }

        return view('App\Modules\Hostel\Views\edit', $data);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'room_number' => 'required',
            'capacity' => 'required|integer',
            'type' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'room_number' => $this->request->getPost('room_number'),
            'capacity' => $this->request->getPost('capacity'),
            'type' => $this->request->getPost('type'),
            'status' => $this->request->getPost('status'),
        ];

        $this->service->update($id, $data, $schoolId);

        return redirect()->to('/hostel')->with('message', 'Room updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->service->delete($id, $schoolId);
        return redirect()->to('/hostel')->with('message', 'Room deleted successfully');
    }
}
