<?php

namespace App\Modules\Admissions\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Admissions\Services\AdmissionsCrudService;

class AdmissionsController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new AdmissionsCrudService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['applications'] = $this->service->getAll($schoolId);
        return view('App\Modules\Admissions\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\Admissions\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;
        
        if (!$this->validate([
            'applicant_name' => 'required|min_length[3]',
            'grade_applied' => 'required',
            'parent_contact' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'applicant_name' => $this->request->getPost('applicant_name'),
            'grade_applied' => $this->request->getPost('grade_applied'),
            'parent_contact' => $this->request->getPost('parent_contact'),
            'status' => 'pending',
            'notes' => $this->request->getPost('notes'),
        ];

        $this->service->create($data);

        return redirect()->to('/admissions')->with('message', 'Application submitted successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['application'] = $this->service->getById($id, $schoolId);
        
        if (!$data['application']) {
            return redirect()->to('/admissions')->with('error', 'Application not found');
        }

        return view('App\Modules\Admissions\Views\edit', $data);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'applicant_name' => 'required|min_length[3]',
            'grade_applied' => 'required',
            'parent_contact' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'applicant_name' => $this->request->getPost('applicant_name'),
            'grade_applied' => $this->request->getPost('grade_applied'),
            'parent_contact' => $this->request->getPost('parent_contact'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes'),
        ];

        $this->service->update($id, $data, $schoolId);

        return redirect()->to('/admissions')->with('message', 'Application updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->service->delete($id, $schoolId);
        return redirect()->to('/admissions')->with('message', 'Application deleted successfully');
    }
}
