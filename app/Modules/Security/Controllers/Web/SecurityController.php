<?php

namespace App\Modules\Security\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Security\Services\SecurityService;

/**
 * SecurityController - Handles CRUD operations for security logs.
 *
 * Displays and manages access logs and security monitoring.
 */
class SecurityController extends BaseController
{
    protected SecurityService $service;

    public function __construct()
    {
        $this->service = new SecurityService();
    }

    /**
     * Check if user has permission to access security module.
     */
    protected function checkAccess(): bool
    {
        // Allow admins only
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);

        if ($isAdmin) {
            return true;
        }

        // Check security-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('security.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin for security
        return $isAdmin;
    }

    /**
     * List all security logs.
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        // Get filter parameters
        $filters = [
            'search'         => $this->request->getGet('search'),
            'was_successful' => $this->request->getGet('was_successful'),
            'attempt_type'   => $this->request->getGet('attempt_type'),
            'date_from'      => $this->request->getGet('date_from'),
            'date_to'        => $this->request->getGet('date_to'),
        ];

        $data = [
            'logs'       => $this->service->getAll(array_filter($filters)),
            'statistics' => $this->service->getStatistics(),
            'filters'    => $filters,
        ];

        return view('App\Modules\Security\Views\index', $data);
    }

    /**
     * Show create form (for manual log entry).
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        return view('App\Modules\Security\Views\create');
    }

    /**
     * Store new security log entry.
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'identifier'   => 'required|max_length[255]',
            'ip_address'   => 'required|valid_ip',
            'attempt_type' => 'required|in_list[login,2fa,password_reset]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'identifier'       => $this->request->getPost('identifier'),
            'ip_address'       => $this->request->getPost('ip_address'),
            'user_agent'       => $this->request->getPost('user_agent'),
            'attempt_type'     => $this->request->getPost('attempt_type'),
            'was_successful'   => $this->request->getPost('was_successful') ? 1 : 0,
            'failure_reason'   => $this->request->getPost('failure_reason'),
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/security')->with('success', 'Security log entry created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create security log entry.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $log = $this->service->getById($id);

        if (!$log) {
            return redirect()->to('/security')->with('error', 'Security log not found.');
        }

        $data = [
            'log' => $log,
        ];

        return view('App\Modules\Security\Views\edit', $data);
    }

    /**
     * Delete security log.
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $result = $this->service->delete($id);

        if ($result) {
            return redirect()->to('/security')->with('success', 'Security log deleted successfully.');
        }

        return redirect()->to('/security')->with('error', 'Failed to delete security log.');
    }
}
