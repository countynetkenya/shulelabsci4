<?php

namespace App\Modules\Audit\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Audit\Models\AuditEventModel;
use App\Modules\Audit\Services\AuditService;

/**
 * AuditController - Handles CRUD operations for audit events viewer.
 *
 * All data is tenant-scoped by school_id from session.
 */
class AuditController extends BaseController
{
    protected AuditService $service;

    protected AuditEventModel $model;

    public function __construct()
    {
        $this->service = new AuditService();
        $this->model = new AuditEventModel();
    }

    /**
     * Check if user has permission to access audit module.
     */
    protected function checkAccess(): bool
    {
        // Allow admins only
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);

        if ($isAdmin) {
            return true;
        }

        // Check audit-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('audit.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin
        return $isAdmin;
    }

    /**
     * Get current school ID from session.
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all audit events.
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Get filter parameters
        $filters = [
            'school_id' => $schoolId,
            'event_type' => $this->request->getGet('event_type'),
            'entity_type' => $this->request->getGet('entity_type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $data = [
            'events'      => $this->model->search(array_filter($filters), 50, 0),
            'filters'     => $filters,
            'eventTypes'  => $this->getEventTypes(),
            'entityTypes' => $this->getEntityTypes(),
        ];

        return view('App\Modules\Audit\Views\index', $data);
    }

    /**
     * Show create form (audit events are typically auto-generated, but we allow manual entry).
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'eventTypes'  => $this->getEventTypes(),
            'entityTypes' => $this->getEntityTypes(),
        ];

        return view('App\Modules\Audit\Views\create', $data);
    }

    /**
     * Store a new audit event.
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'event_type'  => 'required|max_length[100]',
            'entity_type' => 'permit_empty|max_length[100]',
            'entity_id'   => 'permit_empty|numeric',
            'ip_address'  => 'permit_empty|max_length[45]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolId = $this->getSchoolId();
        $data = [
            'school_id'   => $schoolId,
            'user_id'     => (int) session()->get('user_id'),
            'event_type'  => $this->request->getPost('event_type'),
            'entity_type' => $this->request->getPost('entity_type'),
            'entity_id'   => $this->request->getPost('entity_id'),
            'ip_address'  => $this->request->getPost('ip_address') ?: $this->request->getIPAddress(),
            'user_agent'  => $this->request->getUserAgent()?->getAgentString(),
            'request_uri' => $this->request->getPath(),
            'event_key'   => uniqid('manual_', true),
            'trace_id'    => uniqid('trace_', true),
        ];

        if ($this->model->insert($data)) {
            return redirect()->to('/audit')->with('success', 'Audit event created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create audit event.');
    }

    /**
     * Show edit form (typically audit logs are immutable, but we allow viewing/editing metadata).
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $event = $this->model->where('school_id', $schoolId)->find($id);

        if (!$event) {
            return redirect()->to('/audit')->with('error', 'Audit event not found.');
        }

        $data = [
            'event'       => $event,
            'eventTypes'  => $this->getEventTypes(),
            'entityTypes' => $this->getEntityTypes(),
        ];

        return view('App\Modules\Audit\Views\edit', $data);
    }

    /**
     * Update an audit event (limited fields for compliance).
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $event = $this->model->where('school_id', $schoolId)->find($id);

        if (!$event) {
            return redirect()->to('/audit')->with('error', 'Audit event not found.');
        }

        $rules = [
            'metadata_json' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Only allow updating metadata for compliance notes
        $updateData = [
            'metadata_json' => $this->request->getPost('metadata_json'),
        ];

        if ($this->model->update($id, $updateData)) {
            return redirect()->to('/audit')->with('success', 'Audit event updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update audit event.');
    }

    /**
     * Delete an audit event (soft delete or archival only).
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $event = $this->model->where('school_id', $schoolId)->find($id);

        if (!$event) {
            return redirect()->to('/audit')->with('error', 'Audit event not found.');
        }

        // For audit compliance, we typically don't delete, but archive
        // For now, we'll just prevent deletion
        return redirect()->to('/audit')->with('warning', 'Audit events cannot be deleted for compliance reasons. Use archival instead.');
    }

    /**
     * Get available event types.
     */
    protected function getEventTypes(): array
    {
        return [
            'create', 'update', 'delete', 'view', 'access',
            'login', 'logout', 'export', 'import', 'configure',
        ];
    }

    /**
     * Get available entity types.
     */
    protected function getEntityTypes(): array
    {
        return [
            'user', 'student', 'teacher', 'class', 'book',
            'inventory', 'payment', 'invoice', 'report',
        ];
    }
}
