<?php

namespace Tests\Feature\ParentEngagement;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class ParentEngagementCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    // ============= EVENTS TESTS =============

    public function testCanViewEventsIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement/events');

        $response->assertOK();
        $response->assertSee('Events');
    }

    public function testCanViewEventCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement/events/create');

        $response->assertOK();
        $response->assertSee('Create Event');
        $response->assertSee('Event Title');
    }

    public function testCanCreateEvent()
    {
        $data = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'event_type' => 'academic',
            'venue' => 'Main Hall',
            'start_datetime' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'end_datetime' => date('Y-m-d H:i:s', strtotime('+7 days +3 hours')),
            'fee' => '0',
            'registration_required' => '0',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('parent-engagement/events/store', $data);

        $response->assertRedirectTo('/parent-engagement/events');
    }

    public function testCreateEventRequiresTitle()
    {
        $data = [
            'event_type' => 'academic',
            'start_datetime' => date('Y-m-d H:i:s'),
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('parent-engagement/events/store', $data);

        $response->assertRedirect();
    }

    // ============= SURVEYS TESTS =============

    public function testCanViewSurveysIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement/surveys');

        $response->assertOK();
        $response->assertSee('Surveys');
    }

    public function testCanViewSurveyCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement/surveys/create');

        $response->assertOK();
        $response->assertSee('Create Survey');
    }

    // ============= CAMPAIGNS TESTS =============

    public function testCanViewCampaignsIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement/campaigns');

        $response->assertOK();
        $response->assertSee('Fundraising Campaigns');
    }

    public function testCanViewCampaignCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement/campaigns/create');

        $response->assertOK();
        $response->assertSee('Create Campaign');
    }
}
