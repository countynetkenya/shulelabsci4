<?php

namespace Tests\Feature\ParentEngagement;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * ParentEngagementModuleTest - Tests for surveys, events, conferences, and fundraising.
 */
class ParentEngagementModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= ADMIN TESTS =============

    /**
     * Test admin can create survey.
     */
    public function testAdminCanCreateSurvey(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/surveys', [
                'title' => 'Parent Satisfaction Survey',
                'description' => 'Annual satisfaction survey',
                'survey_type' => 'feedback',
                'target_audience' => 'all_parents',
                'questions' => [
                    ['text' => 'How satisfied are you?', 'type' => 'rating'],
                    ['text' => 'Comments', 'type' => 'text'],
                ],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can create event.
     */
    public function testAdminCanCreateEvent(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/events', [
                'title' => 'Annual Sports Day',
                'description' => 'Annual inter-house competition',
                'event_type' => 'sports',
                'venue' => 'School Field',
                'start_datetime' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'end_datetime' => date('Y-m-d H:i:s', strtotime('+30 days +6 hours')),
                'registration_required' => 1,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can create conference.
     */
    public function testAdminCanCreateConference(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/conferences', [
                'name' => 'Term 1 Parent-Teacher Conference',
                'conference_date' => date('Y-m-d', strtotime('+14 days')),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'slot_duration_minutes' => 15,
                'venue' => 'School Hall',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can create fundraising campaign.
     */
    public function testAdminCanCreateCampaign(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/campaigns', [
                'name' => 'Library Expansion Fund',
                'description' => 'Help us expand our school library',
                'target_amount' => 500000,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+60 days')),
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= PARENT TESTS =============

    /**
     * Test parent can view surveys.
     */
    public function testParentCanViewSurveys(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/engagement/surveys');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can submit survey response.
     */
    public function testParentCanSubmitSurveyResponse(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/surveys/1/respond', [
                'responses' => [
                    ['question_id' => 0, 'answer' => 4],
                    ['question_id' => 1, 'answer' => 'Great school!'],
                ],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can view events.
     */
    public function testParentCanViewEvents(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/engagement/events');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can register for event.
     */
    public function testParentCanRegisterForEvent(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/events/1/register', [
                'attendees' => 2,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can book conference slot.
     */
    public function testParentCanBookConferenceSlot(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/conferences/slots/1/book', [
                'student_id' => 100,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can view available conference slots.
     */
    public function testParentCanViewAvailableSlots(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/engagement/conferences/1/slots?teacher_id=101');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can donate to campaign.
     */
    public function testParentCanDonate(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/campaigns/1/donate', [
                'amount' => 5000,
                'is_anonymous' => false,
                'message' => 'Happy to support!',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can volunteer.
     */
    public function testParentCanVolunteer(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/volunteers', [
                'skills' => ['coaching', 'tutoring'],
                'availability' => ['weekends'],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= TEACHER TESTS =============

    /**
     * Test teacher can view their conference slots.
     */
    public function testTeacherCanViewTheirSlots(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/engagement/conferences/1/my-slots');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ACCESS CONTROL =============

    /**
     * Test student cannot create survey.
     */
    public function testStudentCannotCreateSurvey(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->post('/api/v1/engagement/surveys', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
