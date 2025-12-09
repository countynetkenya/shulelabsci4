<?php

namespace App\Modules\Scheduler\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Scheduler\Services\SchedulerEventService;

class SchedulerController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new SchedulerEventService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['events'] = $this->service->getAll($schoolId);
        return view('App\Modules\Scheduler\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\Scheduler\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;
        
        if (!$this->validate([
            'title' => 'required|min_length[3]',
            'start_time' => 'required|valid_date',
            'end_time' => 'required|valid_date'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'title' => $this->request->getPost('title'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'description' => $this->request->getPost('description'),
            'location' => $this->request->getPost('location'),
        ];

        $this->service->create($data);

        return redirect()->to('/scheduler')->with('message', 'Event created successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['event'] = $this->service->getById($id, $schoolId);
        
        if (!$data['event']) {
            return redirect()->to('/scheduler')->with('error', 'Event not found');
        }

        return view('App\Modules\Scheduler\Views\edit', $data);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'title' => 'required|min_length[3]',
            'start_time' => 'required|valid_date',
            'end_time' => 'required|valid_date'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'description' => $this->request->getPost('description'),
            'location' => $this->request->getPost('location'),
        ];

        $this->service->update($id, $data, $schoolId);

        return redirect()->to('/scheduler')->with('message', 'Event updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->service->delete($id, $schoolId);
        return redirect()->to('/scheduler')->with('message', 'Event deleted successfully');
    }
}
