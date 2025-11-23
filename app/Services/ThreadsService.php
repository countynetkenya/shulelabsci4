<?php

namespace App\Services;

use App\Models\ThreadMessageModel;
use App\Models\ThreadAnnouncementModel;

/**
 * ThreadsService - Messaging and announcements.
 */
class ThreadsService
{
    protected ThreadMessageModel $messageModel;
    protected ThreadAnnouncementModel $announcementModel;

    public function __construct()
    {
        $this->messageModel = model(ThreadMessageModel::class);
        $this->announcementModel = model(ThreadAnnouncementModel::class);
    }

    /**
     * Send message.
     */
    public function sendMessage(int $schoolId, int $senderId, int $recipientId, string $subject, string $body): array
    {
        $data = [
            'school_id' => $schoolId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => date('Y-m-d H:i:s'),
            'is_read' => 0,
        ];

        $messageId = $this->messageModel->insert($data);

        if (!$messageId) {
            return ['success' => false, 'message' => 'Failed to send message'];
        }

        return ['success' => true, 'message_id' => $messageId];
    }

    /**
     * Get user inbox.
     */
    public function getInbox(int $userId, int $schoolId, bool $unreadOnly = false): array
    {
        $builder = $this->messageModel
            ->forSchool($schoolId)
            ->where('recipient_id', $userId);

        if ($unreadOnly) {
            $builder->where('is_read', 0);
        }

        return $builder->orderBy('sent_at', 'DESC')->findAll();
    }

    /**
     * Get sent messages.
     */
    public function getSentMessages(int $userId, int $schoolId): array
    {
        return $this->messageModel
            ->forSchool($schoolId)
            ->where('sender_id', $userId)
            ->orderBy('sent_at', 'DESC')
            ->findAll();
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(int $messageId): array
    {
        $message = $this->messageModel->find($messageId);

        if (!$message) {
            return ['success' => false, 'message' => 'Message not found'];
        }

        $this->messageModel->update($messageId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true];
    }

    /**
     * Delete message.
     */
    public function deleteMessage(int $messageId, int $userId): array
    {
        $message = $this->messageModel->find($messageId);

        if (!$message) {
            return ['success' => false, 'message' => 'Message not found'];
        }

        // Verify user owns the message (sender or recipient)
        if ($message['sender_id'] != $userId && $message['recipient_id'] != $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $this->messageModel->delete($messageId);

        return ['success' => true];
    }

    /**
     * Create announcement.
     */
    public function createAnnouncement(int $schoolId, int $authorId, string $title, string $content, string $targetAudience = 'all'): array
    {
        $data = [
            'school_id' => $schoolId,
            'author_id' => $authorId,
            'title' => $title,
            'content' => $content,
            'target_audience' => $targetAudience,
            'published_at' => date('Y-m-d H:i:s'),
            'is_active' => 1,
        ];

        $announcementId = $this->announcementModel->insert($data);

        if (!$announcementId) {
            return ['success' => false, 'message' => 'Failed to create announcement'];
        }

        return ['success' => true, 'announcement_id' => $announcementId];
    }

    /**
     * Get school announcements.
     */
    public function getAnnouncements(int $schoolId, bool $activeOnly = true): array
    {
        $builder = $this->announcementModel->forSchool($schoolId);

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->orderBy('published_at', 'DESC')->findAll();
    }

    /**
     * Update announcement.
     */
    public function updateAnnouncement(int $announcementId, array $data): array
    {
        $announcement = $this->announcementModel->find($announcementId);

        if (!$announcement) {
            return ['success' => false, 'message' => 'Announcement not found'];
        }

        $this->announcementModel->update($announcementId, $data);

        return ['success' => true];
    }

    /**
     * Deactivate announcement.
     */
    public function deactivateAnnouncement(int $announcementId): array
    {
        return $this->updateAnnouncement($announcementId, ['is_active' => 0]);
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCount(int $userId, int $schoolId): int
    {
        return $this->messageModel
            ->forSchool($schoolId)
            ->where('recipient_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Get message thread between two users.
     */
    public function getMessageThread(int $user1Id, int $user2Id, int $schoolId): array
    {
        return $this->messageModel
            ->forSchool($schoolId)
            ->groupStart()
                ->groupStart()
                    ->where('sender_id', $user1Id)
                    ->where('recipient_id', $user2Id)
                ->groupEnd()
                ->orGroupStart()
                    ->where('sender_id', $user2Id)
                    ->where('recipient_id', $user1Id)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('sent_at', 'ASC')
            ->findAll();
    }
}
