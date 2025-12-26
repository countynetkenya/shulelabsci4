<?php

namespace Modules\Mobile\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Mobile\Services\MobileService;

/**
 * MobileDeviceController - Web CRUD for mobile device management.
 */
class MobileDeviceController extends BaseController
{
    protected MobileService $service;

    public function __construct()
    {
        $this->service = new MobileService();
    }

    public function index()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;

        $data = [
            'title' => 'Mobile Devices',
            'devices' => $this->service->getAll($schoolId),
            'statistics' => $this->service->getStatistics($schoolId),
        ];

        return view('Modules\Mobile\Views\devices\index', $data);
    }

    public function create()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $data = ['title' => 'Register Mobile Device'];
        return view('Modules\Mobile\Views\devices\create', $data);
    }

    public function store()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id') ?? 1;

        $rules = [
            'device_id' => 'required|max_length[255]',
            'device_name' => 'permit_empty|max_length[150]',
            'device_type' => 'required|in_list[ios,android,web]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_id' => $this->request->getPost('user_id') ?: $userId,
            'device_id' => $this->request->getPost('device_id'),
            'device_name' => $this->request->getPost('device_name'),
            'device_type' => $this->request->getPost('device_type'),
            'os_version' => $this->request->getPost('os_version'),
            'app_version' => $this->request->getPost('app_version'),
            'is_active' => 1,
            'last_active_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->service->create($data)) {
            return redirect()->to('/mobile/devices')->with('success', 'Device registered successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to register device');
    }

    public function edit($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;
        $device = $this->service->getById($id, $schoolId);

        if (!$device) {
            return redirect()->to('/mobile/devices')->with('error', 'Device not found');
        }

        $data = [
            'title' => 'Edit Mobile Device',
            'device' => $device,
        ];

        return view('Modules\Mobile\Views\devices\edit', $data);
    }

    public function update($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $rules = [
            'device_name' => 'permit_empty|max_length[150]',
            'is_active' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'device_name' => $this->request->getPost('device_name'),
            'os_version' => $this->request->getPost('os_version'),
            'app_version' => $this->request->getPost('app_version'),
            'is_active' => $this->request->getPost('is_active'),
        ];

        if ($this->service->update($id, $data)) {
            return redirect()->to('/mobile/devices')->with('success', 'Device updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update device');
    }

    public function delete($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        if ($this->service->delete($id)) {
            return redirect()->to('/mobile/devices')->with('success', 'Device deleted successfully');
        }

        return redirect()->to('/mobile/devices')->with('error', 'Failed to delete device');
    }
}
