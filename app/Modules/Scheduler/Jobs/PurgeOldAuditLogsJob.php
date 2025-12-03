<?php

namespace Modules\Scheduler\Jobs;

/**
 * Purges old audit logs based on retention policy.
 */
class PurgeOldAuditLogsJob extends BaseJob
{
    public function handle(array $parameters = []): string
    {
        $this->log('Starting audit log purge');

        $retentionDays = $parameters['retention_days'] ?? 365;
        $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));

        $db = \Config\Database::connect();

        // Archive before deleting (optional)
        if ($parameters['archive'] ?? false) {
            $this->archiveLogs($db, $cutoffDate);
        }

        $deleted = $db->table('audit_events')
            ->where('created_at <', $cutoffDate)
            ->delete();

        $count = $db->affectedRows();
        $this->log("Purged {$count} audit log entries older than {$cutoffDate}");

        return "Purged {$count} audit entries older than {$retentionDays} days";
    }

    private function archiveLogs($db, string $cutoffDate): void
    {
        // Implementation for archiving logs to external storage
        $this->log("Archiving logs before {$cutoffDate}");
    }
}
