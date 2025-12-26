<?php

namespace Modules\Gamification\Controllers;

use App\Controllers\BaseController;
use App\Modules\Gamification\Services\GamificationCrudService;

/**
 * GamificationWebController - Handles CRUD operations for gamification (badges & achievements)
 * 
 * All data is tenant-scoped by school_id from session.
 */
class GamificationWebController extends BaseController
{
    protected GamificationCrudService $service;

    public function __construct()
    {
        $this->service = new GamificationCrudService();
    }

    /**
     * Get current school ID from session
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all badges and achievements
     */
    public function index()
    {
        $schoolId = $this->getSchoolId();
        
        $data = [
            'title' => 'Gamification Dashboard',
            'badges' => $this->service->getAllBadges($schoolId),
            'achievements' => $this->service->getAllAchievements($schoolId),
            'recentPoints' => $this->service->getRecentPoints($schoolId, 10),
        ];
        
        return view('Modules\Gamification\Views\index', $data);
    }

    /**
     * Show create badge form
     */
    public function create()
    {
        $data = [
            'title' => 'Create Badge',
        ];
        
        return view('Modules\Gamification\Views\create', $data);
    }

    /**
     * Store new badge
     */
    public function store()
    {
        $schoolId = $this->getSchoolId();
        
        $validationRules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[50]',
            'category' => 'required|in_list[academic,attendance,behavior,sports,leadership,special]',
            'tier' => 'permit_empty|in_list[bronze,silver,gold,platinum,diamond]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'tier' => $this->request->getPost('tier') ?: 'bronze',
            'points_reward' => (int)$this->request->getPost('points_reward') ?: 0,
            'is_secret' => $this->request->getPost('is_secret') ? 1 : 0,
            'is_active' => 1,
        ];

        $id = $this->service->createBadge($data);

        if ($id) {
            return redirect()->to('/gamification')->with('message', 'Badge created successfully');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create badge');
    }

    /**
     * Show edit badge form
     */
    public function edit($id)
    {
        $schoolId = $this->getSchoolId();
        $badge = $this->service->getBadgeById($id, $schoolId);
        
        if (!$badge) {
            return redirect()->to('/gamification')->with('error', 'Badge not found');
        }

        $data = [
            'title' => 'Edit Badge',
            'badge' => $badge,
        ];

        return view('Modules\Gamification\Views\edit', $data);
    }

    /**
     * Update existing badge
     */
    public function update($id)
    {
        $schoolId = $this->getSchoolId();

        $validationRules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[50]',
            'category' => 'required|in_list[academic,attendance,behavior,sports,leadership,special]',
            'tier' => 'permit_empty|in_list[bronze,silver,gold,platinum,diamond]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'tier' => $this->request->getPost('tier') ?: 'bronze',
            'points_reward' => (int)$this->request->getPost('points_reward') ?: 0,
            'is_secret' => $this->request->getPost('is_secret') ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $success = $this->service->updateBadge($id, $data, $schoolId);

        if ($success) {
            return redirect()->to('/gamification')->with('message', 'Badge updated successfully');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update badge');
    }

    /**
     * Delete badge
     */
    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        $success = $this->service->deleteBadge($id, $schoolId);
        
        if ($success) {
            return redirect()->to('/gamification')->with('message', 'Badge deleted successfully');
        }
        
        return redirect()->to('/gamification')->with('error', 'Failed to delete badge or badge is global');
    }
}
