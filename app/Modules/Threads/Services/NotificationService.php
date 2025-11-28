<?php

namespace App\Modules\Threads\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * NotificationService - Handles notification dispatch and preferences.
 */
class NotificationService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Queue a notification for sending.
     */
    public function queue(
        int $userId,
        string $channel,
        string $category,
        string $title,
        string $body,
        ?array $data = null,
        ?string $scheduledAt = null
    ): int {
        // Check user preferences
        if (!$this->isEnabled($userId, $channel, $category)) {
            return 0; // Notification disabled for this user
        }

        // Check quiet hours
        if ($this->isQuietHours($userId, $channel)) {
            // Delay to after quiet hours
            $scheduledAt = $this->getNextAvailableTime($userId, $channel);
        }

        $this->db->table('notification_queue')->insert([
            'school_id' => session('school_id'),
            'user_id' => $userId,
            'channel' => $channel,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'data' => $data ? json_encode($data) : null,
            'status' => 'pending',
            'scheduled_at' => $scheduledAt ?? date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Queue notifications for multiple users.
     */
    public function queueBulk(
        array $userIds,
        string $channel,
        string $category,
        string $title,
        string $body,
        ?array $data = null
    ): int {
        $count = 0;
        foreach ($userIds as $userId) {
            if ($this->queue($userId, $channel, $category, $title, $body, $data)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Send notification immediately (bypass queue).
     */
    public function send(
        int $userId,
        string $channel,
        string $category,
        string $title,
        string $body,
        ?array $data = null
    ): bool {
        // Queue and process immediately
        $notificationId = $this->queue($userId, $channel, $category, $title, $body, $data);
        if (!$notificationId) {
            return false;
        }

        return $this->processNotification($notificationId);
    }

    /**
     * Process a queued notification.
     */
    public function processNotification(int $notificationId): bool
    {
        $notification = $this->db->table('notification_queue')
            ->where('id', $notificationId)
            ->where('status', 'pending')
            ->get()
            ->getRowArray();

        if (!$notification) {
            return false;
        }

        try {
            // Dispatch based on channel
            $success = match ($notification['channel']) {
                'email' => $this->sendEmail($notification),
                'sms' => $this->sendSms($notification),
                'push' => $this->sendPush($notification),
                'in_app' => $this->saveInApp($notification),
                default => false,
            };

            $this->db->table('notification_queue')
                ->where('id', $notificationId)
                ->update([
                    'status' => $success ? 'sent' : 'failed',
                    'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                    'error_message' => $success ? null : 'Failed to send notification',
                ]);

            return $success;
        } catch (\Exception $e) {
            $this->db->table('notification_queue')
                ->where('id', $notificationId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'retry_count' => $notification['retry_count'] + 1,
                ]);

            return false;
        }
    }

    /**
     * Process pending notifications.
     */
    public function processPending(int $limit = 100): array
    {
        $pending = $this->db->table('notification_queue')
            ->where('status', 'pending')
            ->where('scheduled_at <=', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $results = ['sent' => 0, 'failed' => 0];

        foreach ($pending as $notification) {
            if ($this->processNotification($notification['id'])) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Update notification preferences.
     */
    public function updatePreference(
        int $userId,
        string $channel,
        string $category,
        bool $enabled,
        ?string $quietStart = null,
        ?string $quietEnd = null
    ): bool {
        $existing = $this->db->table('notification_preferences')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->where('category', $category)
            ->get()
            ->getRowArray();

        $data = [
            'user_id' => $userId,
            'channel' => $channel,
            'category' => $category,
            'is_enabled' => $enabled ? 1 : 0,
            'quiet_hours_start' => $quietStart,
            'quiet_hours_end' => $quietEnd,
        ];

        if ($existing) {
            return $this->db->table('notification_preferences')
                ->where('id', $existing['id'])
                ->update($data);
        }

        return $this->db->table('notification_preferences')->insert($data);
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(int $userId): array
    {
        return $this->db->table('notification_preferences')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
    }

    /**
     * Check if notifications are enabled for user/channel/category.
     */
    private function isEnabled(int $userId, string $channel, string $category): bool
    {
        $pref = $this->db->table('notification_preferences')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->where('category', $category)
            ->get()
            ->getRowArray();

        // Default to enabled if no preference set
        return $pref ? (bool) $pref['is_enabled'] : true;
    }

    /**
     * Check if currently in quiet hours.
     */
    private function isQuietHours(int $userId, string $channel): bool
    {
        $pref = $this->db->table('notification_preferences')
            ->select('quiet_hours_start, quiet_hours_end')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->where('quiet_hours_start IS NOT NULL')
            ->get()
            ->getRowArray();

        if (!$pref || !$pref['quiet_hours_start']) {
            return false;
        }

        $now = date('H:i:s');
        $start = $pref['quiet_hours_start'];
        $end = $pref['quiet_hours_end'];

        if ($start < $end) {
            return $now >= $start && $now <= $end;
        }

        // Overnight quiet hours
        return $now >= $start || $now <= $end;
    }

    /**
     * Get next available time after quiet hours.
     */
    private function getNextAvailableTime(int $userId, string $channel): string
    {
        $pref = $this->db->table('notification_preferences')
            ->select('quiet_hours_end')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->get()
            ->getRowArray();

        if ($pref && $pref['quiet_hours_end']) {
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            return $tomorrow . ' ' . $pref['quiet_hours_end'];
        }

        return date('Y-m-d H:i:s', strtotime('+8 hours'));
    }

    /**
     * Send email notification.
     */
    private function sendEmail(array $notification): bool
    {
        // Integration with email service
        log_message('info', "Sending email to user {$notification['user_id']}: {$notification['title']}");
        return true; // Placeholder
    }

    /**
     * Send SMS notification.
     */
    private function sendSms(array $notification): bool
    {
        // Integration with SMS service
        log_message('info', "Sending SMS to user {$notification['user_id']}: {$notification['title']}");
        return true; // Placeholder
    }

    /**
     * Send push notification.
     */
    private function sendPush(array $notification): bool
    {
        // Integration with push service (FCM)
        log_message('info', "Sending push to user {$notification['user_id']}: {$notification['title']}");
        return true; // Placeholder
    }

    /**
     * Save in-app notification.
     */
    private function saveInApp(array $notification): bool
    {
        // In-app notifications are already in the queue
        return true;
    }
}
