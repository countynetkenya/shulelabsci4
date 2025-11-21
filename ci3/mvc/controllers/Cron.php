<?php

defined('BASEPATH') or exit('No direct script access allowed');

use App\Services\Database\DatabaseBackupService;

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (php_sapi_name() !== 'cli') {
            show_404();
            exit;
        }

        $this->load->database();
    }

    public function refreshInventoryMonthly($from = null, $to = null): void
    {
        $from = $from ?: date('Y-m-01', strtotime('-2 months'));
        $to = $to ?: date('Y-m-t');

        echo "Rebuilding inventory_monthly from {$from} to {$to}\n";

        $this->db->query(
            "DELETE im FROM inventory_monthly im WHERE im.month BETWEEN DATE_FORMAT(?,'%Y-%m-01') AND DATE_FORMAT(?,'%Y-%m-01')",
            [$from, $to]
        );

        $sql = "INSERT INTO inventory_monthly (month, productID, productwarehouseID, purchases, sales, adjustments, nonbillable_issues)
                SELECT DATE_FORMAT(txn_date,'%Y-%m-01'), productID, productwarehouseID,
                       SUM(CASE WHEN source='purchase' THEN qty_in ELSE 0 END),
                       SUM(CASE WHEN source='sale' THEN qty_out ELSE 0 END),
                       SUM(CASE WHEN source='adjustment' THEN qty_in-qty_out ELSE 0 END),
                       SUM(CASE WHEN source='issue_nonbillable' THEN qty_out ELSE 0 END)
                FROM inventory_ledger
                WHERE txn_date BETWEEN ? AND ?
                GROUP BY 1,2,3
                ON DUPLICATE KEY UPDATE purchases=VALUES(purchases), sales=VALUES(sales), adjustments=VALUES(adjustments), nonbillable_issues=VALUES(nonbillable_issues)";

        $this->db->query($sql, [$from, $to]);

        echo "Done.\n";
    }

    public function okr_update_progress($schoolID = null): void
    {
        if (!function_exists('feature_flag_enabled') || !feature_flag_enabled('OKR_V1')) {
            echo "OKR_V1 flag disabled; skipping.\n";
            return;
        }

        if (!$this->db->table_exists('okr_objectives')) {
            echo "No okr_objectives table found.\n";
            return;
        }

        $this->load->library('Okr_progress_service');

        $targets = [];
        if ($schoolID !== null) {
            $targets[] = (int) $schoolID;
        } else {
            $rows = $this->db->select('DISTINCT schoolID as schoolID')->from('okr_objectives')->get()->result();
            foreach ($rows as $row) {
                $targets[] = (int) $row->schoolID;
            }
        }

        if (empty($targets)) {
            echo "No OKR objectives found to process.\n";
            return;
        }

        foreach ($targets as $targetSchool) {
            echo "Recomputing OKR progress for school {$targetSchool}\n";
            $results = $this->okr_progress_service->recomputeAllForSchool($targetSchool);
            echo sprintf("Processed %d objective(s).\n", count($results));
        }

        echo "OKR progress refresh complete.\n";
    }

    public function nightly_database_backup(): void
    {
        $service = new DatabaseBackupService($this);
        try {
            $result = $service->runNightlyBackup();
            echo sprintf(
                "Backup complete: %s (sha256: %s, drive_id: %s)\n",
                basename($result['file']),
                $result['checksum'],
                $result['drive_file_id']
            );
        } catch (\Throwable $exception) {
            log_message('error', 'Nightly backup failed: ' . $exception->getMessage());
            echo 'Nightly backup failed: ' . $exception->getMessage() . "\n";
        }
    }

    public function monthly_restore_drill(): void
    {
        $service = new DatabaseBackupService($this);
        try {
            $result = $service->runMonthlyRestoreDrill();
            echo sprintf(
                "Restore drill succeeded. Source file %s restored to %s (%d bytes).\n",
                $result['source_file_id'],
                $result['restored_database'],
                $result['restored_bytes']
            );
        } catch (\Throwable $exception) {
            log_message('error', 'Restore drill failed: ' . $exception->getMessage());
            echo 'Restore drill failed: ' . $exception->getMessage() . "\n";
        }
    }
}
