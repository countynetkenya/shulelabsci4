<?php

namespace Modules\ParentEngagement\Controllers;

use App\Controllers\BaseController;
use App\Modules\ParentEngagement\Services\ParentEngagementService;
use Modules\ParentEngagement\Models\SurveyModel;
use Modules\ParentEngagement\Models\EventModel;
use Modules\ParentEngagement\Models\ConferenceModel;
use Modules\ParentEngagement\Models\FundraisingCampaignModel;

class ParentEngagementWebController extends BaseController
{
    protected $service;
    protected $surveyModel;
    protected $eventModel;
    protected $conferenceModel;
    protected $campaignModel;

    public function __construct()
    {
        $this->service = new ParentEngagementService();
        $this->surveyModel = new SurveyModel();
        $this->eventModel = new EventModel();
        $this->conferenceModel = new ConferenceModel();
        $this->campaignModel = new FundraisingCampaignModel();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        
        $data = [
            'title' => 'Parent Engagement Dashboard',
            'surveys' => $this->surveyModel->where('school_id', $schoolId)->findAll(5),
            'events' => $this->eventModel->where('school_id', $schoolId)->where('status', 'published')->findAll(5),
            'campaigns' => $this->campaignModel->where('school_id', $schoolId)->where('status', 'active')->findAll(5),
        ];

        return view('Modules\ParentEngagement\Views\index', $data);
    }

    // ============= SURVEYS =============
    
    public function surveys()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data = [
            'title' => 'Surveys',
            'surveys' => $this->surveyModel->where('school_id', $schoolId)->orderBy('created_at', 'DESC')->findAll(),
        ];
        return view('Modules\ParentEngagement\Views\surveys_index', $data);
    }

    public function createSurvey()
    {
        return view('Modules\ParentEngagement\Views\surveys_create', ['title' => 'Create Survey']);
    }

    public function storeSurvey()
    {
        if (!$this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'survey_type' => 'required|in_list[feedback,poll,evaluation,custom]',
            'target_audience' => 'required|in_list[all_parents,class_parents,specific]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'survey_type' => $this->request->getPost('survey_type'),
            'target_audience' => $this->request->getPost('target_audience'),
            'questions' => [], // Would be populated from a form
            'is_anonymous' => $this->request->getPost('is_anonymous') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
        ];

        $this->service->createSurvey($data);
        return redirect()->to('/parent-engagement/surveys')->with('message', 'Survey created successfully');
    }

    // ============= EVENTS =============
    
    public function events()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data = [
            'title' => 'Events',
            'events' => $this->eventModel->where('school_id', $schoolId)->orderBy('start_datetime', 'DESC')->findAll(),
        ];
        return view('Modules\ParentEngagement\Views\events_index', $data);
    }

    public function createEvent()
    {
        return view('Modules\ParentEngagement\Views\events_create', ['title' => 'Create Event']);
    }

    public function storeEvent()
    {
        if (!$this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'event_type' => 'required|in_list[academic,sports,cultural,meeting,fundraising,other]',
            'start_datetime' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'event_type' => $this->request->getPost('event_type'),
            'venue' => $this->request->getPost('venue'),
            'start_datetime' => $this->request->getPost('start_datetime'),
            'end_datetime' => $this->request->getPost('end_datetime'),
            'max_attendees' => $this->request->getPost('max_attendees'),
            'registration_required' => $this->request->getPost('registration_required') ? 1 : 0,
            'fee' => $this->request->getPost('fee') ?? 0,
        ];

        $this->service->createEvent($data);
        return redirect()->to('/parent-engagement/events')->with('message', 'Event created successfully');
    }

    public function editEvent($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $event = $this->eventModel->where('id', $id)->where('school_id', $schoolId)->first();
        
        if (!$event) {
            return redirect()->to('/parent-engagement/events')->with('error', 'Event not found');
        }

        return view('Modules\ParentEngagement\Views\events_edit', ['title' => 'Edit Event', 'event' => $event]);
    }

    public function updateEvent($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'event_type' => 'required|in_list[academic,sports,cultural,meeting,fundraising,other]',
            'start_datetime' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'event_type' => $this->request->getPost('event_type'),
            'venue' => $this->request->getPost('venue'),
            'start_datetime' => $this->request->getPost('start_datetime'),
            'end_datetime' => $this->request->getPost('end_datetime'),
            'status' => $this->request->getPost('status'),
        ];

        $this->eventModel->update($id, $data);
        return redirect()->to('/parent-engagement/events')->with('message', 'Event updated successfully');
    }

    public function deleteEvent($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->eventModel->where('school_id', $schoolId)->delete($id);
        return redirect()->to('/parent-engagement/events')->with('message', 'Event deleted successfully');
    }

    // ============= CAMPAIGNS =============
    
    public function campaigns()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $campaigns = $this->campaignModel->where('school_id', $schoolId)->orderBy('created_at', 'DESC')->findAll();
        
        // Calculate progress percentage for each campaign
        foreach ($campaigns as &$campaign) {
            $campaign['progress_percentage'] = ($campaign['target_amount'] > 0) ? 
                min(100, ($campaign['raised_amount'] / $campaign['target_amount']) * 100) : 0;
        }
        
        $data = [
            'title' => 'Fundraising Campaigns',
            'campaigns' => $campaigns,
        ];
        return view('Modules\ParentEngagement\Views\campaigns_index', $data);
    }

    public function createCampaign()
    {
        return view('Modules\ParentEngagement\Views\campaigns_create', ['title' => 'Create Campaign']);
    }

    public function storeCampaign()
    {
        if (!$this->validate([
            'name' => 'required|min_length[3]|max_length[200]',
            'target_amount' => 'required|decimal',
            'start_date' => 'required',
            'end_date' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'target_amount' => $this->request->getPost('target_amount'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
        ];

        $this->service->createCampaign($data);
        return redirect()->to('/parent-engagement/campaigns')->with('message', 'Campaign created successfully');
    }
}
