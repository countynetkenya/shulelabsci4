<?php

declare(strict_types=1);

namespace Modules\Foundation\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\Foundation\Services\InstallService;

/**
 * InstallController handles the web-based installation flow.
 */
class InstallController extends \CodeIgniter\Controller
{
    private InstallService $installService;

    public function __construct()
    {
        $this->installService = new InstallService();
    }

    /**
     * Main installer entry point - shows environment check.
     */
    public function index(): ResponseInterface|string
    {
        // If already installed, redirect to login
        if ($this->installService->isInstalled()) {
            return redirect()->to('/auth/signin')->with('info', 'Application is already installed.');
        }

        $data = [
            'dbConnected' => false,
            'migrationsOk' => false,
            'missingTables' => [],
        ];

        // Check database connection
        $data['dbConnected'] = $this->installService->checkDatabaseConnection();

        // Check migrations if DB is connected
        if ($data['dbConnected']) {
            $migrationCheck = $this->installService->checkMigrations();
            $data['migrationsOk'] = $migrationCheck['success'];
            $data['missingTables'] = $migrationCheck['missing'];
        }

        return view('Modules\Foundation\Views\install\environment', $data);
    }

    /**
     * Show organisation and school setup form.
     */
    public function tenants(): ResponseInterface|string
    {
        // If already installed, redirect to login
        if ($this->installService->isInstalled()) {
            return redirect()->to('/auth/signin')->with('info', 'Application is already installed.');
        }

        // Verify prerequisites
        if (!$this->installService->checkDatabaseConnection()) {
            return redirect()->to('/install')->with('error', 'Database connection failed.');
        }

        $migrationCheck = $this->installService->checkMigrations();
        if (!$migrationCheck['success']) {
            return redirect()->to('/install')->with('error', 'Migrations have not been run.');
        }

        // Show form or handle POST
        if ($this->request->getMethod() === 'POST') {
            return $this->handleTenantCreation();
        }

        return view('Modules\Foundation\Views\install\tenants', [
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Handle tenant creation POST.
     */
    private function handleTenantCreation(): ResponseInterface
    {
        $validation = \Config\Services::validation();

        $rules = [
            'organisation_name' => 'required|min_length[2]|max_length[191]',
            'school_name' => 'required|min_length[2]|max_length[191]',
            'organisation_code' => 'permit_empty|max_length[64]|alpha_dash',
            'school_code' => 'permit_empty|max_length[64]|alpha_dash',
            'country' => 'permit_empty|max_length[100]',
            'curriculum' => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $tenantData = [
                'organisation_name' => $this->request->getPost('organisation_name'),
                'school_name' => $this->request->getPost('school_name'),
            ];

            if ($this->request->getPost('organisation_code')) {
                $tenantData['organisation_code'] = $this->request->getPost('organisation_code');
            }
            if ($this->request->getPost('school_code')) {
                $tenantData['school_code'] = $this->request->getPost('school_code');
            }
            if ($this->request->getPost('country')) {
                $tenantData['country'] = $this->request->getPost('country');
            }
            if ($this->request->getPost('curriculum')) {
                $tenantData['curriculum'] = $this->request->getPost('curriculum');
            }

            $result = $this->installService->createTenants($tenantData);

            // Store IDs in session for the next step
            session()->set('install_org_id', $result['organisation_id']);
            session()->set('install_school_id', $result['school_id']);

            return redirect()->to('/install/admin')->with('success', 'Organisation and school created successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show admin user creation form.
     */
    public function admin(): ResponseInterface|string
    {
        // If already installed, redirect to login
        if ($this->installService->isInstalled()) {
            return redirect()->to('/auth/signin')->with('info', 'Application is already installed.');
        }

        // Verify school ID is in session (tenants were created)
        $schoolId = session()->get('install_school_id');
        if (!$schoolId) {
            return redirect()->to('/install/tenants')->with('error', 'Please create organisation and school first.');
        }

        // Show form or handle POST
        if ($this->request->getMethod() === 'POST') {
            return $this->handleAdminCreation();
        }

        return view('Modules\Foundation\Views\install\admin', [
            'validation' => \Config\Services::validation(),
            'schoolId' => $schoolId,
        ]);
    }

    /**
     * Handle admin user creation POST.
     */
    private function handleAdminCreation(): ResponseInterface
    {
        $schoolId = session()->get('install_school_id');
        if (!$schoolId) {
            return redirect()->to('/install/tenants')->with('error', 'Please create organisation and school first.');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'full_name' => 'required|min_length[2]|max_length[60]',
            'email' => 'required|valid_email|max_length[40]',
            'username' => 'required|min_length[3]|max_length[40]|alpha_dash',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $userData = [
                'full_name' => $this->request->getPost('full_name'),
                'email' => $this->request->getPost('email'),
                'username' => $this->request->getPost('username'),
                'password' => $this->request->getPost('password'),
                'school_id' => $schoolId,
            ];

            $userId = $this->installService->createAdminUser($userData);

            // Clear installation session data
            session()->remove('install_org_id');
            session()->remove('install_school_id');

            // Set success message
            session()->setFlashdata('success', 'Installation completed! Please sign in with your admin account.');

            return redirect()->to('/auth/signin');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
