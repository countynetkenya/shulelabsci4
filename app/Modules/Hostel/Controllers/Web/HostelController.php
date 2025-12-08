<?php

namespace Modules\Hostel\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Hostel\Services\HostelService;

class HostelController extends BaseController
{
    protected $hostelService;

    public function __construct()
    {
        $this->hostelService = new HostelService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id');
        if (!$schoolId) {
            $schoolId = 1;
        }

        $data['hostels'] = $this->hostelService->getHostels($schoolId);
        
        return view('Modules\Hostel\Views\hostel\index', $data);
    }

    public function create()
    {
        return view('Modules\Hostel\Views\hostel\create');
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'type' => 'required',
            'capacity' => 'required|integer',
            'location' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolId = session()->get('school_id');
        if (!$schoolId) {
            $schoolId = 1;
        }

        $data = [
            'school_id' => $schoolId,
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'capacity' => $this->request->getPost('capacity'),
            'location' => $this->request->getPost('location'),
            'description' => $this->request->getPost('description'),
        ];

        $this->hostelService->createHostel($data);

        return redirect()->to('hostel')->with('message', 'Hostel created successfully');
    }

    public function edit($id)
    {
        $data['hostel'] = $this->hostelService->getHostel($id);
        if (!$data['hostel']) {
            return redirect()->to('hostel')->with('error', 'Hostel not found');
        }
        return view('Modules\Hostel\Views\hostel\edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'type' => 'required',
            'capacity' => 'required|integer',
            'location' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'capacity' => $this->request->getPost('capacity'),
            'location' => $this->request->getPost('location'),
            'description' => $this->request->getPost('description'),
        ];

        $this->hostelService->updateHostel($id, $data);

        return redirect()->to('hostel')->with('message', 'Hostel updated successfully');
    }

    public function delete($id)
    {
        $this->hostelService->deleteHostel($id);
        return redirect()->to('hostel')->with('message', 'Hostel deleted successfully');
    }
}
