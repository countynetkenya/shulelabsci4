<?php

namespace Modules\Database\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Database\Services\DatabaseService;

/**
 * DatabaseController - Web CRUD for database backup management.
 */
class DatabaseController extends BaseController
{
    protected DatabaseService $service;

    public function __construct()
    {
        $this->service = new DatabaseService();
    }

    /**
     * Display all backups.
     */
    public function index()
    {
        // Check authentication
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;

        $data = [
            'title' => 'Database Backups',
            'backups' => $this->service->getAll($schoolId),
            'statistics' => $this->service->getStatistics($schoolId),
        ];

        return view('Modules\Database\Views\database\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Create Database Backup',
        ];

        return view('Modules\Database\Views\database\create', $data);
    }

    /**
     * Store a new backup.
     */
    public function store()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;

        $rules = [
            'name' => 'required|max_length[200]',
            'type' => 'required|in_list[full,incremental,differential]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'path' => '/backups/' . $schoolId . '/' . date('Y-m-d'),
            'status' => 'pending',
            'size' => 0,
        ];

        $id = $this->service->create($data);

        if ($id) {
            return redirect()->to('/database')->with('success', 'Backup created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create backup');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;
        $backup = $this->service->getById($id, $schoolId);

        if (!$backup) {
            return redirect()->to('/database')->with('error', 'Backup not found');
        }

        $data = [
            'title' => 'Edit Database Backup',
            'backup' => $backup,
        ];

        return view('Modules\Database\Views\database\edit', $data);
    }

    /**
     * Update a backup.
     */
    public function update($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;

        $rules = [
            'name' => 'required|max_length[200]',
            'status' => 'required|in_list[pending,in_progress,completed,failed]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'status' => $this->request->getPost('status'),
        ];

        if ($this->service->update($id, $data)) {
            return redirect()->to('/database')->with('success', 'Backup updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update backup');
    }

    /**
     * Delete a backup.
     */
    public function delete($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        if ($this->service->delete($id)) {
            return redirect()->to('/database')->with('success', 'Backup deleted successfully');
        }

        return redirect()->to('/database')->with('error', 'Failed to delete backup');
    }
}
