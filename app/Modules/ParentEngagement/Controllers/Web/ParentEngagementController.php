<?php

namespace App\Modules\ParentEngagement\Controllers\Web;

use App\Controllers\BaseController;
use Modules\ParentEngagement\Services\ParentEngagementCrudService;

/**
 * ParentEngagementController - Handles CRUD operations for parent engagement surveys.
 *
 * All data is tenant-scoped by school_id from session.
 */
class ParentEngagementController extends BaseController
{
    protected ParentEngagementCrudService $service;

    public function __construct()
    {
        $this->service = new ParentEngagementCrudService();
    }

    /**
     * Check if user has permission to access parent engagement module.
     */
    protected function checkAccess(): bool
    {
        // Allow admins
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);

        if ($isAdmin) {
            return true;
        }

        // Check permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('parent_engagement.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin for now
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
     * List all surveys.
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Get filter parameters
        $filters = [
            'search'      => $this->request->getGet('search'),
            'status'      => $this->request->getGet('status'),
            'survey_type' => $this->request->getGet('survey_type'),
        ];

        $data = [
            'surveys' => $this->service->getAll($schoolId, array_filter($filters)),
            'filters' => $filters,
        ];

        return view('App\Modules\ParentEngagement\Views\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        return view('App\Modules\ParentEngagement\Views\create');
    }

    /**
     * Store new survey.
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'title'           => 'required|max_length[255]',
            'survey_type'     => 'required|in_list[feedback,poll,evaluation,custom]',
            'target_audience' => 'required|in_list[all_parents,class_parents,specific]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolId = $this->getSchoolId();
        $userId = session()->get('user_id') ?? session()->get('userID') ?? 1;

        // Prepare questions as JSON
        $questions = $this->request->getPost('questions');
        if (is_string($questions)) {
            $questions = json_decode($questions, true);
        }
        if (!$questions) {
            $questions = [
                ['text' => 'Default question', 'type' => 'text'],
            ];
        }

        $data = [
            'school_id'       => $schoolId,
            'title'           => $this->request->getPost('title'),
            'description'     => $this->request->getPost('description'),
            'survey_type'     => $this->request->getPost('survey_type'),
            'target_audience' => $this->request->getPost('target_audience'),
            'questions'       => json_encode($questions),
            'is_anonymous'    => $this->request->getPost('is_anonymous') ? 1 : 0,
            'start_date'      => $this->request->getPost('start_date'),
            'end_date'        => $this->request->getPost('end_date'),
            'status'          => $this->request->getPost('status') ?? 'draft',
            'created_by'      => $userId,
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/parent-engagement')->with('success', 'Survey created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create survey.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $survey = $this->service->getById($id, $schoolId);

        if (!$survey) {
            return redirect()->to('/parent-engagement')->with('error', 'Survey not found.');
        }

        $data = [
            'survey' => $survey,
        ];

        return view('App\Modules\ParentEngagement\Views\edit', $data);
    }

    /**
     * Update existing survey.
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'title'           => 'required|max_length[255]',
            'survey_type'     => 'required|in_list[feedback,poll,evaluation,custom]',
            'target_audience' => 'required|in_list[all_parents,class_parents,specific]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare questions as JSON
        $questions = $this->request->getPost('questions');
        if (is_string($questions)) {
            $questions = json_decode($questions, true);
        }
        if (!$questions) {
            $questions = [
                ['text' => 'Default question', 'type' => 'text'],
            ];
        }

        $data = [
            'title'           => $this->request->getPost('title'),
            'description'     => $this->request->getPost('description'),
            'survey_type'     => $this->request->getPost('survey_type'),
            'target_audience' => $this->request->getPost('target_audience'),
            'questions'       => json_encode($questions),
            'is_anonymous'    => $this->request->getPost('is_anonymous') ? 1 : 0,
            'start_date'      => $this->request->getPost('start_date'),
            'end_date'        => $this->request->getPost('end_date'),
            'status'          => $this->request->getPost('status') ?? 'draft',
        ];

        $result = $this->service->update($id, $data);

        if ($result) {
            return redirect()->to('/parent-engagement')->with('success', 'Survey updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update survey.');
    }

    /**
     * Delete survey.
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $result = $this->service->delete($id);

        if ($result) {
            return redirect()->to('/parent-engagement')->with('success', 'Survey deleted successfully.');
        }

        return redirect()->to('/parent-engagement')->with('error', 'Failed to delete survey.');
    }
}
