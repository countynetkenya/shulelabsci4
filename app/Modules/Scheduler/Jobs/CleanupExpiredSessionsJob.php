<?php

namespace App\Modules\Scheduler\Jobs;

/**
 * Cleans up expired sessions from the database.
 */
class CleanupExpiredSessionsJob extends BaseJob
{
    public function handle(array $parameters = []): string
    {
        $this->log('Starting session cleanup');

        $db = \Config\Database::connect();
        $expiredBefore = date('Y-m-d H:i:s', strtotime('-24 hours'));

        $deleted = $db->table('ci_sessions')
            ->where('timestamp <', strtotime($expiredBefore))
            ->delete();

        $count = $db->affectedRows();
        $this->log("Deleted {$count} expired sessions");

        return "Cleaned up {$count} expired sessions";
    }
}
