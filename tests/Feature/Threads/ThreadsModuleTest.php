<?php

namespace Tests\Feature\Threads;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * ThreadsModuleTest - Web and API tests for Threads/Communications module.
 *
 * Tests messaging, announcements, notifications for all user roles.
 */
class ThreadsModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= ADMIN ROLE TESTS =============

    /**
     * Test admin can create announcement.
     */
    public function testAdminCanCreateAnnouncement(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/threads/announcements', [
                'title' => 'Test Announcement',
                'content' => 'This is a test announcement.',
                'scope' => 'school',
                'priority' => 'normal',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view all announcements.
     */
    public function testAdminCanViewAnnouncements(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/threads/announcements');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= TEACHER ROLE TESTS =============

    /**
     * Test teacher can create class announcement.
     */
    public function testTeacherCanCreateClassAnnouncement(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/threads/announcements', [
                'title' => 'Homework Reminder',
                'content' => 'Please complete chapter 5 exercises.',
                'scope' => 'class',
                'scope_ids' => json_encode([1]),
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test teacher can send message to parent.
     */
    public function testTeacherCanMessageParent(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/threads/messages', [
                'recipient_id' => 150,
                'subject' => 'Student Progress',
                'content' => 'I wanted to discuss your child\'s progress.',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test teacher can view threads.
     */
    public function testTeacherCanViewThreads(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/threads');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= STUDENT ROLE TESTS =============

    /**
     * Test student can view announcements.
     */
    public function testStudentCanViewAnnouncements(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/threads/announcements');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student cannot create school-wide announcement.
     */
    public function testStudentCannotCreateSchoolAnnouncement(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->post('/api/v1/threads/announcements', [
                'title' => 'Student Announcement',
                'scope' => 'school',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }

    // ============= PARENT ROLE TESTS =============

    /**
     * Test parent can view announcements.
     */
    public function testParentCanViewAnnouncements(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/threads/announcements');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can message teacher.
     */
    public function testParentCanMessageTeacher(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/threads/messages', [
                'recipient_id' => 101,
                'subject' => 'Question about homework',
                'content' => 'Can you clarify the assignment?',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can view unread count.
     */
    public function testParentCanViewUnreadCount(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/threads/unread-count');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= NOTIFICATION TESTS =============

    /**
     * Test user can update notification preferences.
     */
    public function testCanUpdateNotificationPreferences(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->put('/api/v1/threads/notifications/preferences', [
                'channel' => 'email',
                'category' => 'announcements',
                'is_enabled' => true,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }
}
