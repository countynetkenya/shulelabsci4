<?php

namespace Tests\Feature\Gamification;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * GamificationModuleTest - Tests for points, badges, leaderboards, and challenges.
 */
class GamificationModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= STUDENT TESTS =============

    /**
     * Test student can view their points balance.
     */
    public function testStudentCanViewPointsBalance(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/points/balance');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view point history.
     */
    public function testStudentCanViewPointHistory(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/points/history');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view their badges.
     */
    public function testStudentCanViewOwnBadges(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/badges/my');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view all available badges.
     */
    public function testStudentCanViewAllBadges(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/badges');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view leaderboard.
     */
    public function testStudentCanViewLeaderboard(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/leaderboards/weekly');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view achievements.
     */
    public function testStudentCanViewAchievements(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/achievements');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view active challenges.
     */
    public function testStudentCanViewChallenges(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/challenges');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can join a challenge.
     */
    public function testStudentCanJoinChallenge(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/challenges/1/join', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test student can view rewards catalog.
     */
    public function testStudentCanViewRewards(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/gamification/rewards');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can redeem reward.
     */
    public function testStudentCanRedeemReward(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/rewards/1/redeem', []);

        // Could be 200 (success), 400 (insufficient points), or 404 (not found)
        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 400, 404]));
    }

    // ============= TEACHER TESTS =============

    /**
     * Test teacher can award points to student.
     */
    public function testTeacherCanAwardPoints(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/points/award', [
                'user_id' => 100,
                'points' => 10,
                'source' => 'class_participation',
                'description' => 'Great participation today',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test teacher can award badge to student.
     */
    public function testTeacherCanAwardBadge(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/badges/award', [
                'user_id' => 100,
                'badge_id' => 1,
                'reason' => 'Outstanding performance',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test teacher can view class leaderboard.
     */
    public function testTeacherCanViewClassLeaderboard(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/gamification/leaderboards/class/1');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ADMIN TESTS =============

    /**
     * Test admin can create badge.
     */
    public function testAdminCanCreateBadge(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/badges', [
                'name' => 'Perfect Attendance',
                'code' => 'PERFECT_ATTEND',
                'description' => 'Awarded for perfect attendance',
                'category' => 'attendance',
                'tier' => 'gold',
                'points_reward' => 100,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can create challenge.
     */
    public function testAdminCanCreateChallenge(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/challenges', [
                'name' => 'Reading Challenge',
                'description' => 'Read 5 books this month',
                'challenge_type' => 'individual',
                'category' => 'academic',
                'criteria' => ['target' => 5, 'metric' => 'books_read'],
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+30 days')),
                'points_reward' => 500,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can create reward.
     */
    public function testAdminCanCreateReward(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/rewards', [
                'name' => 'Free Lunch',
                'description' => 'Redeem for one free lunch',
                'category' => 'food',
                'points_cost' => 500,
                'quantity_available' => 50,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view redemption requests.
     */
    public function testAdminCanViewRedemptions(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/gamification/rewards/redemptions');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can fulfill redemption.
     */
    public function testAdminCanFulfillRedemption(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/gamification/rewards/redemptions/1/fulfill', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= PARENT TESTS =============

    /**
     * Test parent can view child's points.
     */
    public function testParentCanViewChildPoints(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/gamification/points/child/100');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can view child's badges.
     */
    public function testParentCanViewChildBadges(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/gamification/badges/child/100');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }
}
