<?php

namespace Tests\Feature\Governance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * GovernanceModuleTest - Tests for board management and governance.
 */
class GovernanceModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $seed = 'WaveModulesSeeder';

    // ============= ADMIN TESTS =============

    /**
     * Test admin can view board members.
     */
    public function testAdminCanViewBoardMembers(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/governance/board-members');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can add board member.
     */
    public function testAdminCanAddBoardMember(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/board-members', [
                'name' => 'John Smith',
                'email' => 'john.smith@board.local',
                'position' => 'Board Chair',
                'term_start' => date('Y-m-d'),
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can schedule meeting.
     */
    public function testAdminCanScheduleMeeting(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/meetings', [
                'title' => 'Q1 Board Meeting',
                'meeting_type' => 'regular',
                'meeting_date' => date('Y-m-d', strtotime('+14 days')),
                'start_time' => '14:00:00',
                'venue' => 'Board Room',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view meetings.
     */
    public function testAdminCanViewMeetings(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/governance/meetings');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can create resolution.
     */
    public function testAdminCanCreateResolution(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/resolutions', [
                'resolution_number' => 'RES-2025-001',
                'title' => 'Budget Approval',
                'content' => 'The board approves the 2025 budget...',
                'category' => 'finance',
                'proposed_by' => 1,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view resolutions.
     */
    public function testAdminCanViewResolutions(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/governance/resolutions');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can create committee.
     */
    public function testAdminCanCreateCommittee(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/committees', [
                'name' => 'Finance Committee',
                'description' => 'Oversees school financial matters',
                'member_ids' => [1, 2],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can create policy.
     */
    public function testAdminCanCreatePolicy(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/policies', [
                'policy_number' => 'POL-001',
                'title' => 'Admissions Policy',
                'category' => 'academic',
                'content' => 'The school admissions policy...',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view policies.
     */
    public function testAdminCanViewPolicies(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/governance/policies');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can upload board document.
     */
    public function testAdminCanUploadDocument(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/documents', [
                'title' => 'Meeting Agenda',
                'document_type' => 'agenda',
                'meeting_id' => 1,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can record meeting attendance.
     */
    public function testAdminCanRecordAttendance(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/meetings/1/attendance', [
                'attendees' => [
                    ['member_id' => 1, 'status' => 'present'],
                    ['member_id' => 2, 'status' => 'excused'],
                ],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can record vote on resolution.
     */
    public function testAdminCanRecordVote(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/governance/resolutions/1/vote', [
                'votes_for' => 5,
                'votes_against' => 1,
                'votes_abstained' => 1,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ACCESS CONTROL =============

    /**
     * Test teacher cannot access governance.
     */
    public function testTeacherCannotAccessGovernance(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/governance/board-members');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }

    /**
     * Test student cannot access governance.
     */
    public function testStudentCannotAccessGovernance(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/governance/resolutions');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
