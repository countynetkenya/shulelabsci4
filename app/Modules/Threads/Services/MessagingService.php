<?php

namespace App\Modules\Threads\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * MessagingService - Handles thread-based messaging.
 */
class MessagingService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Create a new thread.
     */
    public function createThread(
        string $subject,
        string $type,
        array $participantIds,
        ?string $contextType = null,
        ?int $contextId = null
    ): int {
        $schoolId = session('school_id');
        $creatorId = session('user_id');

        $this->db->transStart();

        // Create thread
        $this->db->table('threads')->insert([
            'school_id' => $schoolId,
            'subject' => $subject,
            'thread_type' => $type,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'created_by' => $creatorId,
        ]);
        $threadId = (int) $this->db->insertID();

        // Add participants
        $participantIds = array_unique(array_merge([$creatorId], $participantIds));
        foreach ($participantIds as $userId) {
            $this->db->table('thread_participants')->insert([
                'thread_id' => $threadId,
                'user_id' => $userId,
                'role' => $userId === $creatorId ? 'owner' : 'member',
                'joined_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();

        return $threadId;
    }

    /**
     * Send a message to a thread.
     */
    public function sendMessage(
        int $threadId,
        string $content,
        string $contentType = 'text',
        ?int $replyToId = null,
        ?array $attachments = null
    ): int {
        $senderId = session('user_id');

        // Verify sender is participant
        $participant = $this->db->table('thread_participants')
            ->where('thread_id', $threadId)
            ->where('user_id', $senderId)
            ->where('left_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$participant) {
            throw new \RuntimeException('User is not a participant of this thread');
        }

        if ($participant['role'] === 'readonly') {
            throw new \RuntimeException('User has readonly access to this thread');
        }

        $this->db->transStart();

        // Insert message
        $this->db->table('thread_messages')->insert([
            'thread_id' => $threadId,
            'sender_id' => $senderId,
            'content' => $content,
            'content_type' => $contentType,
            'reply_to_id' => $replyToId,
            'attachments' => $attachments ? json_encode($attachments) : null,
        ]);
        $messageId = (int) $this->db->insertID();

        // Update thread
        $this->db->table('threads')
            ->where('id', $threadId)
            ->set('last_message_at', date('Y-m-d H:i:s'))
            ->set('message_count', 'message_count + 1', false)
            ->update();

        // Update unread counts for other participants
        $this->db->table('thread_participants')
            ->where('thread_id', $threadId)
            ->where('user_id !=', $senderId)
            ->set('unread_count', 'unread_count + 1', false)
            ->update();

        $this->db->transComplete();

        return $messageId;
    }

    /**
     * Get threads for a user.
     */
    public function getThreads(int $userId, ?string $type = null, int $limit = 50, int $offset = 0): array
    {
        $builder = $this->db->table('threads t')
            ->select('t.*, tp.unread_count, tp.is_muted, tp.role as my_role')
            ->join('thread_participants tp', 'tp.thread_id = t.id')
            ->where('tp.user_id', $userId)
            ->where('tp.left_at IS NULL')
            ->where('t.is_archived', 0);

        if ($type) {
            $builder->where('t.thread_type', $type);
        }

        return $builder
            ->orderBy('t.last_message_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Get messages in a thread.
     */
    public function getMessages(int $threadId, int $limit = 50, ?int $beforeId = null): array
    {
        $builder = $this->db->table('thread_messages m')
            ->select('m.*, u.first_name, u.last_name, u.avatar')
            ->join('users u', 'u.id = m.sender_id')
            ->where('m.thread_id', $threadId)
            ->where('m.is_deleted', 0);

        if ($beforeId) {
            $builder->where('m.id <', $beforeId);
        }

        return $builder
            ->orderBy('m.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(int $threadId, int $userId): void
    {
        // Update participant read status
        $this->db->table('thread_participants')
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->update([
                'last_read_at' => date('Y-m-d H:i:s'),
                'unread_count' => 0,
            ]);

        // Get unread messages
        $messages = $this->db->table('thread_messages m')
            ->select('m.id')
            ->join('message_read_receipts mrr', 'mrr.message_id = m.id AND mrr.user_id = ' . $userId, 'left')
            ->where('m.thread_id', $threadId)
            ->where('m.sender_id !=', $userId)
            ->where('mrr.id IS NULL')
            ->get()
            ->getResultArray();

        // Insert read receipts
        foreach ($messages as $message) {
            $this->db->table('message_read_receipts')->insert([
                'message_id' => $message['id'],
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        $result = $this->db->table('thread_participants')
            ->selectSum('unread_count', 'total')
            ->where('user_id', $userId)
            ->where('left_at IS NULL')
            ->where('is_muted', 0)
            ->get()
            ->getRowArray();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Add participant to thread.
     */
    public function addParticipant(int $threadId, int $userId, string $role = 'member'): bool
    {
        $existing = $this->db->table('thread_participants')
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if ($existing && $existing['left_at'] === null) {
            return false; // Already a participant
        }

        if ($existing) {
            // Rejoin
            return $this->db->table('thread_participants')
                ->where('id', $existing['id'])
                ->update([
                    'left_at' => null,
                    'joined_at' => date('Y-m-d H:i:s'),
                    'role' => $role,
                ]);
        }

        return $this->db->table('thread_participants')->insert([
            'thread_id' => $threadId,
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Remove participant from thread.
     */
    public function removeParticipant(int $threadId, int $userId): bool
    {
        return $this->db->table('thread_participants')
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->update(['left_at' => date('Y-m-d H:i:s')]);
    }
}
