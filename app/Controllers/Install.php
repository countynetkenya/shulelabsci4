<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Install extends BaseController
{
    /**
     * Installation wizard entry point.
     */
    public function index(): string|ResponseInterface
    {
        // Check if already installed
        $envInstalled = env('app.installed', false);
        $isInstalled = filter_var($envInstalled, FILTER_VALIDATE_BOOLEAN);

        if ($isInstalled) {
            return redirect()->to('/')->with('error', 'Application is already installed.');
        }

        // Display installation wizard
        return view('install/index', [
            'title' => 'ShuleLabs Installation Wizard',
            'step' => $this->request->getGet('step') ?? 1,
        ]);
    }

    /**
     * Step 1: Environment check.
     */
    public function checkEnvironment(): ResponseInterface
    {
        $checks = [
            'php_version' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'intl_extension' => extension_loaded('intl'),
            'mbstring_extension' => extension_loaded('mbstring'),
            'json_extension' => extension_loaded('json'),
            'mysqli_extension' => extension_loaded('mysqli') || extension_loaded('sqlite3'),
            'writable_logs' => is_writable(WRITEPATH . 'logs'),
            'writable_cache' => is_writable(WRITEPATH . 'cache'),
            'writable_session' => is_writable(WRITEPATH . 'session'),
        ];

        return $this->response->setJSON([
            'success' => !in_array(false, $checks, true),
            'checks' => $checks,
        ]);
    }

    /**
     * Step 2: Create organization and school.
     */
    public function createOrganization(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/install');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'org_name' => 'required|min_length[3]|max_length[255]',
            'school_name' => 'required|min_length[3]|max_length[255]',
            'school_code' => 'required|alpha_numeric|min_length[2]|max_length[20]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // TODO: Create organization and school in database
        // For now, return success
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Organization and school created successfully.',
        ]);
    }

    /**
     * Step 3: Create admin user.
     */
    public function createAdmin(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/install');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // TODO: Create admin user in database
        // For now, return success
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Admin user created successfully.',
        ]);
    }

    /**
     * Complete installation.
     */
    public function complete(): ResponseInterface
    {
        // Mark installation as complete
        // Note: In production, this should update the .env file
        // For now, just return success

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Installation completed successfully!',
            'redirect' => '/auth/signin',
        ]);
    }
}
