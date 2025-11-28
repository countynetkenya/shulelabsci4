<?php

namespace App\Modules\Analytics\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Analytics & AI module tables.
 */
class CreateAnalyticsTables extends Migration
{
    public function up(): void
    {
        // analytics_dashboards - Custom dashboard definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'layout' => ['type' => 'JSON'],
            'is_default' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_shared' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'shared_with_roles' => ['type' => 'JSON', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'created_by'], false, false, 'idx_school_creator');
        $this->forge->createTable('analytics_dashboards', true);

        // analytics_widgets - Dashboard widget definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'dashboard_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'widget_type' => ['type' => 'ENUM', 'constraint' => ['chart', 'metric', 'table', 'list', 'map', 'progress']],
            'chart_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'data_source' => ['type' => 'VARCHAR', 'constraint' => 100],
            'query_config' => ['type' => 'JSON'],
            'display_config' => ['type' => 'JSON', 'null' => true],
            'refresh_interval' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'position' => ['type' => 'JSON'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('dashboard_id', false, false, 'idx_dashboard');
        $this->forge->createTable('analytics_widgets', true);

        // analytics_predictions - AI prediction results
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'model_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'prediction_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'prediction_value' => ['type' => 'DECIMAL', 'constraint' => '10,4'],
            'confidence_score' => ['type' => 'DECIMAL', 'constraint' => '5,4'],
            'features_used' => ['type' => 'JSON', 'null' => true],
            'valid_until' => ['type' => 'DATE', 'null' => true],
            'is_notified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'entity_type', 'entity_id'], false, false, 'idx_school_entity');
        $this->forge->addKey(['model_type', 'prediction_type'], false, false, 'idx_model_type');
        $this->forge->createTable('analytics_predictions', true);

        // at_risk_students - Students identified as at-risk
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'risk_category' => ['type' => 'ENUM', 'constraint' => ['academic', 'attendance', 'behavioral', 'financial', 'social']],
            'risk_score' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'risk_level' => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high', 'critical']],
            'risk_factors' => ['type' => 'JSON'],
            'recommended_actions' => ['type' => 'JSON', 'null' => true],
            'intervention_status' => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'resolved', 'escalated'], 'default' => 'pending'],
            'assigned_to' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'identified_at' => ['type' => 'DATETIME'],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'risk_level'], false, false, 'idx_school_risk');
        $this->forge->addKey(['student_id', 'risk_category'], false, false, 'idx_student_category');
        $this->forge->createTable('at_risk_students', true);

        // financial_forecasts - Financial projections
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'forecast_type' => ['type' => 'ENUM', 'constraint' => ['revenue', 'expenses', 'fees_collection', 'cash_flow']],
            'period_type' => ['type' => 'ENUM', 'constraint' => ['monthly', 'quarterly', 'yearly']],
            'period_start' => ['type' => 'DATE'],
            'period_end' => ['type' => 'DATE'],
            'forecast_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'actual_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'variance' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'confidence_level' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'model_version' => ['type' => 'VARCHAR', 'constraint' => 20],
            'generated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'forecast_type', 'period_start'], false, false, 'idx_school_type_period');
        $this->forge->createTable('financial_forecasts', true);

        // trend_analyses - Historical trend analysis
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'metric_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'metric_category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'period_date' => ['type' => 'DATE'],
            'period_type' => ['type' => 'ENUM', 'constraint' => ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']],
            'value' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
            'previous_value' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true],
            'change_percent' => ['type' => 'DECIMAL', 'constraint' => '10,4', 'null' => true],
            'trend_direction' => ['type' => 'ENUM', 'constraint' => ['up', 'down', 'stable'], 'null' => true],
            'dimensions' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'metric_name', 'period_date'], false, false, 'idx_school_metric_date');
        $this->forge->createTable('trend_analyses', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('trend_analyses', true);
        $this->forge->dropTable('financial_forecasts', true);
        $this->forge->dropTable('at_risk_students', true);
        $this->forge->dropTable('analytics_predictions', true);
        $this->forge->dropTable('analytics_widgets', true);
        $this->forge->dropTable('analytics_dashboards', true);
    }
}
