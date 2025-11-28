<?php

namespace App\Modules\Monitoring\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Monitoring module tables.
 */
class CreateMonitoringTables extends Migration
{
    public function up(): void
    {
        // health_checks - Health check configurations
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'check_type' => ['type' => 'ENUM', 'constraint' => ['database', 'cache', 'queue', 'storage', 'external_api', 'custom']],
            'endpoint' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'check_interval_seconds' => ['type' => 'INT', 'constraint' => 11, 'default' => 60],
            'timeout_seconds' => ['type' => 'INT', 'constraint' => 11, 'default' => 5],
            'expected_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'is_critical' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('check_type', false, false, 'idx_check_type');
        $this->forge->createTable('health_checks', true);

        // health_check_results - Health check history
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'health_check_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['healthy', 'degraded', 'unhealthy']],
            'response_time_ms' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'message' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'details' => ['type' => 'JSON', 'null' => true],
            'checked_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['health_check_id', 'checked_at'], false, false, 'idx_check_time');
        $this->forge->createTable('health_check_results', true);

        // metrics - Application metrics
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'metric_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'metric_type' => ['type' => 'ENUM', 'constraint' => ['counter', 'gauge', 'histogram', 'summary']],
            'value' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
            'labels' => ['type' => 'JSON', 'null' => true],
            'recorded_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['metric_name', 'recorded_at'], false, false, 'idx_metric_time');
        $this->forge->addKey(['school_id', 'metric_name'], false, false, 'idx_school_metric');
        $this->forge->createTable('metrics', true);

        // request_logs - Request tracking for performance monitoring
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'trace_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'span_id' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true],
            'parent_span_id' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true],
            'method' => ['type' => 'VARCHAR', 'constraint' => 10],
            'path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'status_code' => ['type' => 'INT', 'constraint' => 3],
            'duration_ms' => ['type' => 'INT', 'constraint' => 11],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'request_size' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'response_size' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'recorded_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('trace_id', false, false, 'idx_trace');
        $this->forge->addKey(['path', 'recorded_at'], false, false, 'idx_path_time');
        $this->forge->addKey(['status_code', 'recorded_at'], false, false, 'idx_status_time');
        $this->forge->createTable('request_logs', true);

        // error_logs - Error tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'trace_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'level' => ['type' => 'ENUM', 'constraint' => ['debug', 'info', 'warning', 'error', 'critical']],
            'message' => ['type' => 'TEXT'],
            'exception_class' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'file' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'line' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'stack_trace' => ['type' => 'TEXT', 'null' => true],
            'context' => ['type' => 'JSON', 'null' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'fingerprint' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'occurrence_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'first_seen_at' => ['type' => 'DATETIME'],
            'last_seen_at' => ['type' => 'DATETIME'],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['level', 'last_seen_at'], false, false, 'idx_level_time');
        $this->forge->addKey('fingerprint', false, false, 'idx_fingerprint');
        $this->forge->addKey('trace_id', false, false, 'idx_trace');
        $this->forge->createTable('error_logs', true);

        // alerts - Alert configurations
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'alert_type' => ['type' => 'ENUM', 'constraint' => ['threshold', 'anomaly', 'pattern', 'absence']],
            'metric_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'condition' => ['type' => 'JSON'],
            'severity' => ['type' => 'ENUM', 'constraint' => ['info', 'warning', 'critical'], 'default' => 'warning'],
            'notification_channels' => ['type' => 'JSON'],
            'cooldown_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 15],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_triggered_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('metric_name', false, false, 'idx_metric');
        $this->forge->createTable('alerts', true);

        // alert_incidents - Alert incidents history
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'alert_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['triggered', 'acknowledged', 'resolved'], 'default' => 'triggered'],
            'trigger_value' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'message' => ['type' => 'TEXT', 'null' => true],
            'triggered_at' => ['type' => 'DATETIME'],
            'acknowledged_at' => ['type' => 'DATETIME', 'null' => true],
            'acknowledged_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resolution_notes' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['alert_id', 'status'], false, false, 'idx_alert_status');
        $this->forge->addKey('triggered_at', false, false, 'idx_triggered');
        $this->forge->createTable('alert_incidents', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('alert_incidents', true);
        $this->forge->dropTable('alerts', true);
        $this->forge->dropTable('error_logs', true);
        $this->forge->dropTable('request_logs', true);
        $this->forge->dropTable('metrics', true);
        $this->forge->dropTable('health_check_results', true);
        $this->forge->dropTable('health_checks', true);
    }
}
