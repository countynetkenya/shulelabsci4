<?php

namespace Tests\Threads;

use App\Services\ThreadsService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ThreadsServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;

    protected ThreadsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThreadsService();
    }

    public function testSendMessage(): void
    {
        $result = $this->service->sendMessage(6, 25, 26, 'Test Subject', 'Test message body');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message_id', $result);

        // Verify message
        $message = model('App\Models\ThreadMessageModel')->find($result['message_id']);
        $this->assertEquals('Test Subject', $message['subject']);
        $this->assertEquals(0, $message['is_read']);
    }

    public function testGetInbox(): void
    {
        // Send message to user 26
        $this->service->sendMessage(6, 25, 26, 'Inbox Test', 'Message content');

        $inbox = $this->service->getInbox(26, 6);

        $this->assertIsArray($inbox);
        $this->assertGreaterThan(0, count($inbox));
    }

    public function testGetInboxUnreadOnly(): void
    {
        $this->service->sendMessage(6, 25, 27, 'Unread Test', 'Unread message');

        $unread = $this->service->getInbox(27, 6, true);

        $this->assertIsArray($unread);
        foreach ($unread as $message) {
            $this->assertEquals(0, $message['is_read']);
        }
    }

    public function testGetSentMessages(): void
    {
        $this->service->sendMessage(6, 28, 29, 'Sent Test', 'Sent message');

        $sent = $this->service->getSentMessages(28, 6);

        $this->assertIsArray($sent);
        $this->assertGreaterThan(0, count($sent));
    }

    public function testMarkAsRead(): void
    {
        $messageResult = $this->service->sendMessage(6, 25, 30, 'Read Test', 'Message to read');
        $messageId = $messageResult['message_id'];

        $result = $this->service->markAsRead($messageId);

        $this->assertTrue($result['success']);

        // Verify message is read
        $message = model('App\Models\ThreadMessageModel')->find($messageId);
        $this->assertEquals(1, $message['is_read']);
        $this->assertNotNull($message['read_at']);
    }

    public function testDeleteMessage(): void
    {
        $messageResult = $this->service->sendMessage(6, 31, 32, 'Delete Test', 'Message to delete');
        $messageId = $messageResult['message_id'];

        $result = $this->service->deleteMessage($messageId, 31); // Sender deletes

        $this->assertTrue($result['success']);

        // Verify message deleted
        $message = model('App\Models\ThreadMessageModel')->find($messageId);
        $this->assertNull($message);
    }

    public function testCannotDeleteOthersMessage(): void
    {
        $messageResult = $this->service->sendMessage(6, 33, 34, 'Unauthorized Delete', 'Message');
        $messageId = $messageResult['message_id'];

        $result = $this->service->deleteMessage($messageId, 35); // Different user

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Unauthorized', $result['message']);
    }

    public function testCreateAnnouncement(): void
    {
        $result = $this->service->createAnnouncement(6, 25, 'Important Announcement', 'Announcement content', 'all');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('announcement_id', $result);

        // Verify announcement
        $announcement = model('App\Models\ThreadAnnouncementModel')->find($result['announcement_id']);
        $this->assertEquals('Important Announcement', $announcement['title']);
        $this->assertEquals(1, $announcement['is_active']);
    }

    public function testGetAnnouncements(): void
    {
        $this->service->createAnnouncement(6, 25, 'Test Announcement 1', 'Content 1', 'all');
        $this->service->createAnnouncement(6, 25, 'Test Announcement 2', 'Content 2', 'teachers');

        $announcements = $this->service->getAnnouncements(6);

        $this->assertIsArray($announcements);
        $this->assertGreaterThan(0, count($announcements));
    }

    public function testDeactivateAnnouncement(): void
    {
        $announcementResult = $this->service->createAnnouncement(6, 25, 'Deactivate Test', 'Content', 'all');
        $announcementId = $announcementResult['announcement_id'];

        $result = $this->service->deactivateAnnouncement($announcementId);

        $this->assertTrue($result['success']);

        // Verify deactivated
        $announcement = model('App\Models\ThreadAnnouncementModel')->find($announcementId);
        $this->assertEquals(0, $announcement['is_active']);
    }

    public function testGetUnreadCount(): void
    {
        $this->service->sendMessage(6, 25, 36, 'Count Test 1', 'Message 1');
        $this->service->sendMessage(6, 25, 36, 'Count Test 2', 'Message 2');

        $count = $this->service->getUnreadCount(36, 6);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function testGetMessageThread(): void
    {
        // Create conversation between user 37 and 38
        $this->service->sendMessage(6, 37, 38, 'Thread 1', 'Message from 37');
        $this->service->sendMessage(6, 38, 37, 'Thread 2', 'Reply from 38');
        $this->service->sendMessage(6, 37, 38, 'Thread 3', 'Another from 37');

        $thread = $this->service->getMessageThread(37, 38, 6);

        $this->assertIsArray($thread);
        $this->assertGreaterThanOrEqual(3, count($thread));
    }
}
