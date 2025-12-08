<?php

namespace Modules\Transport\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Transport\Services\TransportService;

class TransportController extends BaseController
{
    protected $transportService;

    public function __construct()
    {
        $this->transportService = new TransportService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id');
        // Fallback for testing if session not set
        if (!$schoolId) {
            $schoolId = 1; 
        }

        $data['routes'] = $this->transportService->getRoutes($schoolId);
        
        return view('Modules\Transport\Views\transport\index', $data);
    }

    public function create()
    {
        return view('Modules\Transport\Views\transport\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;
        
        $rules = [
            'route_name' => 'required|min_length[3]',
            'start_point' => 'required',
            'end_point' => 'required',
            'cost' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'route_name' => $this->request->getPost('route_name'),
            'start_point' => $this->request->getPost('start_point'),
            'end_point' => $this->request->getPost('end_point'),
            'cost' => $this->request->getPost('cost'),
            'is_active' => 1
        ];

        $this->transportService->createRoute($data);

        return redirect()->to('/transport')->with('message', 'Route created successfully');
    }

    public function edit($id)
    {
        $data['route'] = $this->transportService->getRoute($id);
        if (!$data['route']) {
            return redirect()->to('/transport')->with('error', 'Route not found');
        }
        return view('Modules\Transport\Views\transport\edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'route_name' => 'required|min_length[3]',
            'start_point' => 'required',
            'end_point' => 'required',
            'cost' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'route_name' => $this->request->getPost('route_name'),
            'start_point' => $this->request->getPost('start_point'),
            'end_point' => $this->request->getPost('end_point'),
            'cost' => $this->request->getPost('cost'),
        ];

        $this->transportService->updateRoute($id, $data);

        return redirect()->to('/transport')->with('message', 'Route updated successfully');
    }

    public function delete($id)
    {
        $this->transportService->deleteRoute($id);
        return redirect()->to('/transport')->with('message', 'Route deleted successfully');
    }
}
