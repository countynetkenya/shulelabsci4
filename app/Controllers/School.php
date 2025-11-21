<?php

namespace App\Controllers;

use App\Models\SiteModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * School Controller
 *
 * Handles school selection for users with multiple schools
 */
class School extends BaseController
{
    protected $siteModel;
    protected $data = [];

    public function __construct()
    {
        helper(['compatibility', 'form']);
        $this->siteModel = new SiteModel();
    }

    /**
     * School selection page
     *
     * @return string|RedirectResponse
     */
    public function select()
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('loggedin')) {
            return redirect()->to('/auth/signin');
        }

        // Get user's available schools from session
        $availableSchoolIDs = $session->get('available_school_ids');

        // Fallback to parsing schoolID if available_school_ids not set (legacy sessions)
        if (empty($availableSchoolIDs)) {
            $schoolIDs = $session->get('schoolID');
            if (!empty($schoolIDs)) {
                $availableSchoolIDs = array_filter(explode(',', $schoolIDs));
            }
        }

        if (empty($availableSchoolIDs)) {
            return redirect()->to('/dashboard');
        }

        // Get school list using available school IDs
        $schoolIDsString = implode(',', $availableSchoolIDs);
        $this->data['schools'] = $this->siteModel->getSitesForUser($schoolIDsString);
        $this->data['siteinfos'] = $this->siteModel->getSite(0);
        $this->data['current_school_id'] = $session->get('schoolID');

        // If POST request, process selection
        if ($this->request->getMethod() === 'post') {
            return $this->processSelection();
        }

        // Show selection form
        return view('school/select', $this->data);
    }

    /**
     * Process school selection
     *
     * @return RedirectResponse
     */
    protected function processSelection(): RedirectResponse
    {
        $selectedSchoolID = (int)$this->request->getPost('schoolID');

        if (!$selectedSchoolID) {
            return redirect()->back()->with('error', 'Please select a school');
        }

        // Verify user has access to this school using available_school_ids
        $session = session();
        $availableSchoolIDs = $session->get('available_school_ids');

        // Fallback to parsing schoolID if available_school_ids not set (legacy sessions)
        if (empty($availableSchoolIDs)) {
            $schoolIDs = $session->get('schoolID');
            if (!empty($schoolIDs)) {
                $availableSchoolIDs = array_filter(explode(',', $schoolIDs));
            }
        }

        // Convert to integers for comparison
        $availableSchoolIDs = array_map('intval', $availableSchoolIDs);

        if (!in_array($selectedSchoolID, $availableSchoolIDs)) {
            return redirect()->back()->with('error', 'Access denied to selected school');
        }

        // Set school session data (this sets the active school)
        $siteInfo = $this->siteModel->getSite($selectedSchoolID);

        if ($siteInfo) {
            $session->set([
                'schoolID' => $selectedSchoolID, // Active school ID (different from comma-separated schoolID in user record)
                'defaultschoolyearID' => $siteInfo->school_year ?? null,
                'lang' => $siteInfo->language ?? 'english'
            ]);
        }

        // Redirect to dashboard
        return redirect()->to('/dashboard');
    }
}
