<?php

namespace App\Modules\MultiTenant\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Multi-Tenant module tables for SaaS productization.
 */
class CreateMultiTenantTables extends Migration
{
    public function up(): void
    {
        // tenants - Tenant (school) registry
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'VARCHAR', 'constraint' => 36],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'subdomain' => ['type' => 'VARCHAR', 'constraint' => 63],
            'custom_domain' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'active', 'suspended', 'cancelled'], 'default' => 'pending'],
            'tier' => ['type' => 'ENUM', 'constraint' => ['free', 'starter', 'professional', 'enterprise'], 'default' => 'free'],
            'settings' => ['type' => 'JSON', 'null' => true],
            'features' => ['type' => 'JSON', 'null' => true],
            'storage_quota_mb' => ['type' => 'INT', 'constraint' => 11, 'default' => 5000],
            'storage_used_mb' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'student_quota' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'staff_quota' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'trial_ends_at' => ['type' => 'DATE', 'null' => true],
            'activated_at' => ['type' => 'DATETIME', 'null' => true],
            'suspended_at' => ['type' => 'DATETIME', 'null' => true],
            'cancelled_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid', 'uk_uuid');
        $this->forge->addUniqueKey('subdomain', 'uk_subdomain');
        $this->forge->addUniqueKey('custom_domain', 'uk_custom_domain');
        $this->forge->createTable('tenants', true);

        // tenant_branding - Custom branding per tenant
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'logo_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'favicon_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'primary_color' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#1E40AF'],
            'secondary_color' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#3B82F6'],
            'accent_color' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'font_family' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'custom_css' => ['type' => 'TEXT', 'null' => true],
            'login_background_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'email_header_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'email_footer_html' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tenant_id', 'uk_tenant');
        $this->forge->createTable('tenant_branding', true);

        // tenant_subscriptions - Billing subscriptions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'plan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'billing_cycle' => ['type' => 'ENUM', 'constraint' => ['monthly', 'quarterly', 'yearly']],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'KES'],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'past_due', 'cancelled', 'expired'], 'default' => 'active'],
            'current_period_start' => ['type' => 'DATE'],
            'current_period_end' => ['type' => 'DATE'],
            'next_billing_date' => ['type' => 'DATE', 'null' => true],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'external_subscription_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'idx_tenant_status');
        $this->forge->createTable('tenant_subscriptions', true);

        // subscription_plans - Available plans
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT', 'null' => true],
            'tier' => ['type' => 'ENUM', 'constraint' => ['free', 'starter', 'professional', 'enterprise']],
            'monthly_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'yearly_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'features' => ['type' => 'JSON'],
            'limits' => ['type' => 'JSON'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_public' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code', 'uk_code');
        $this->forge->createTable('subscription_plans', true);

        // tenant_invoices - Billing invoices
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subscription_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'invoice_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'KES'],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'pending', 'paid', 'overdue', 'cancelled'], 'default' => 'draft'],
            'billing_period_start' => ['type' => 'DATE'],
            'billing_period_end' => ['type' => 'DATE'],
            'due_date' => ['type' => 'DATE'],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'line_items' => ['type' => 'JSON'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('invoice_number', 'uk_invoice_number');
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'idx_tenant_status');
        $this->forge->createTable('tenant_invoices', true);

        // tenant_usage - Usage metrics tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'metric_name' => ['type' => 'VARCHAR', 'constraint' => 50],
            'metric_value' => ['type' => 'BIGINT', 'constraint' => 20],
            'recorded_date' => ['type' => 'DATE'],
            'recorded_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'metric_name', 'recorded_date'], false, false, 'idx_tenant_metric_date');
        $this->forge->createTable('tenant_usage', true);

        // tenant_onboarding - Onboarding progress
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'step' => ['type' => 'VARCHAR', 'constraint' => 50],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'completed', 'skipped'], 'default' => 'pending'],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'data' => ['type' => 'JSON', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'step'], 'uk_tenant_step');
        $this->forge->createTable('tenant_onboarding', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tenant_onboarding', true);
        $this->forge->dropTable('tenant_usage', true);
        $this->forge->dropTable('tenant_invoices', true);
        $this->forge->dropTable('subscription_plans', true);
        $this->forge->dropTable('tenant_subscriptions', true);
        $this->forge->dropTable('tenant_branding', true);
        $this->forge->dropTable('tenants', true);
    }
}
