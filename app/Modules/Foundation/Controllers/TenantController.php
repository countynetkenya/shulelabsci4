<?php

namespace Modules\Foundation\Controllers;

use App\Controllers\BaseController;
use Modules\Foundation\Services\TenantService;

class TenantController extends BaseController
{
    protected $tenantService;

    public function __construct()
    {
        $this->tenantService = new TenantService();
    }

    public function index()
    {
        $schools = $this->tenantService->getAllTenants();
        
        // Map to view format
        $viewData = array_map(function($school) {
            return [
                'id' => $school['id'],
                'name' => $school['school_name'],
                'code' => $school['school_code'],
                'domain' => $school['school_code'] . '.shulelabs.local', // Mock domain logic
                'status' => $school['is_active'] ? 'Active' : 'Suspended',
                'students' => 0, // TODO: Count students
            ];
        }, $schools);

        return view('Modules\Foundation\Views\tenants\index', ['schools' => $viewData]);
    }

    public function create()
    {
        return view('Modules\Foundation\Views\tenants\create');
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'code' => 'required|alpha_dash|min_length[2]',
            'admin_email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = $this->tenantService->createTenant($this->request->getPost());

        if ($result['success']) {
            return redirect()->to('/system/tenants')->with('success', $result['message']);
        } else {
            return redirect()->back()->withInput()->with('error', $result['message'] ?? 'Failed to create school.');
        }
    }
}
