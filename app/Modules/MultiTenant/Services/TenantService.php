<?php

namespace App\Modules\MultiTenant\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * TenantService - Handles tenant provisioning, billing, and management.
 */
class TenantService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Provision a new tenant.
     */
    public function provision(array $data): int
    {
        $this->db->transStart();

        // Create tenant
        $tenantData = [
            'uuid' => $this->generateUuid(),
            'name' => $data['name'],
            'subdomain' => $this->sanitizeSubdomain($data['subdomain']),
            'status' => 'pending',
            'tier' => $data['tier'] ?? 'free',
            'settings' => json_encode($data['settings'] ?? []),
            'features' => json_encode($this->getDefaultFeatures($data['tier'] ?? 'free')),
            'trial_ends_at' => date('Y-m-d', strtotime('+14 days')),
        ];

        $this->db->table('tenants')->insert($tenantData);
        $tenantId = (int) $this->db->insertID();

        // Create default branding
        $this->db->table('tenant_branding')->insert([
            'tenant_id' => $tenantId,
        ]);

        // Initialize onboarding steps
        $this->initializeOnboarding($tenantId);

        $this->db->transComplete();

        return $tenantId;
    }

    /**
     * Generate UUID v4.
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Sanitize subdomain.
     */
    private function sanitizeSubdomain(string $subdomain): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $subdomain));
    }

    /**
     * Get default features for tier.
     */
    private function getDefaultFeatures(string $tier): array
    {
        $features = [
            'free' => ['students', 'attendance', 'basic_reports'],
            'starter' => ['students', 'attendance', 'grades', 'reports', 'messaging'],
            'professional' => ['students', 'attendance', 'grades', 'reports', 'messaging', 'finance', 'library', 'transport'],
            'enterprise' => ['all'],
        ];

        return $features[$tier] ?? $features['free'];
    }

    /**
     * Initialize onboarding steps.
     */
    private function initializeOnboarding(int $tenantId): void
    {
        $steps = [
            'school_profile' => 'pending',
            'admin_account' => 'pending',
            'academic_setup' => 'pending',
            'class_setup' => 'pending',
            'student_import' => 'pending',
            'staff_import' => 'pending',
            'branding' => 'pending',
            'integrations' => 'pending',
        ];

        foreach ($steps as $step => $status) {
            $this->db->table('tenant_onboarding')->insert([
                'tenant_id' => $tenantId,
                'step' => $step,
                'status' => $status,
            ]);
        }
    }

    /**
     * Activate tenant.
     */
    public function activate(int $tenantId): bool
    {
        return $this->db->table('tenants')
            ->where('id', $tenantId)
            ->update([
                'status' => 'active',
                'activated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Suspend tenant.
     */
    public function suspend(int $tenantId, string $reason): bool
    {
        return $this->db->table('tenants')
            ->where('id', $tenantId)
            ->update([
                'status' => 'suspended',
                'suspended_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Create subscription.
     */
    public function createSubscription(int $tenantId, int $planId, string $billingCycle): int
    {
        $plan = $this->db->table('subscription_plans')
            ->where('id', $planId)
            ->get()
            ->getRowArray();

        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }

        $amount = $billingCycle === 'yearly' ? $plan['yearly_price'] : $plan['monthly_price'];
        $periodEnd = $billingCycle === 'yearly'
            ? date('Y-m-d', strtotime('+1 year'))
            : date('Y-m-d', strtotime('+1 month'));

        $this->db->table('tenant_subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'billing_cycle' => $billingCycle,
            'amount' => $amount,
            'status' => 'active',
            'current_period_start' => date('Y-m-d'),
            'current_period_end' => $periodEnd,
            'next_billing_date' => $periodEnd,
        ]);

        // Update tenant tier
        $this->db->table('tenants')
            ->where('id', $tenantId)
            ->update([
                'tier' => $plan['tier'],
                'features' => json_encode($plan['features']),
            ]);

        return (int) $this->db->insertID();
    }

    /**
     * Create invoice.
     */
    public function createInvoice(int $tenantId, int $subscriptionId): int
    {
        $subscription = $this->db->table('tenant_subscriptions')
            ->where('id', $subscriptionId)
            ->get()
            ->getRowArray();

        if (!$subscription) {
            throw new \RuntimeException('Subscription not found');
        }

        $invoiceNumber = sprintf('INV-%d-%s', $tenantId, date('YmdHis'));
        $taxRate = 0.16; // 16% VAT
        $taxAmount = $subscription['amount'] * $taxRate;
        $totalAmount = $subscription['amount'] + $taxAmount;

        $this->db->table('tenant_invoices')->insert([
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'invoice_number' => $invoiceNumber,
            'amount' => $subscription['amount'],
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => $subscription['currency'],
            'status' => 'pending',
            'billing_period_start' => $subscription['current_period_start'],
            'billing_period_end' => $subscription['current_period_end'],
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'line_items' => json_encode([
                ['description' => 'Subscription fee', 'amount' => $subscription['amount']],
                ['description' => 'VAT (16%)', 'amount' => $taxAmount],
            ]),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Record usage metric.
     */
    public function recordUsage(int $tenantId, string $metricName, int $value): void
    {
        $this->db->table('tenant_usage')->insert([
            'tenant_id' => $tenantId,
            'metric_name' => $metricName,
            'metric_value' => $value,
            'recorded_date' => date('Y-m-d'),
            'recorded_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check quota limits.
     */
    public function checkQuota(int $tenantId, string $quotaType): array
    {
        $tenant = $this->db->table('tenants')
            ->where('id', $tenantId)
            ->get()
            ->getRowArray();

        if (!$tenant) {
            return ['allowed' => false, 'message' => 'Tenant not found'];
        }

        $quota = $tenant[$quotaType . '_quota'] ?? null;
        if ($quota === null) {
            return ['allowed' => true, 'message' => 'Unlimited'];
        }

        // Get current count
        $currentUsage = $this->getCurrentUsage($tenantId, $quotaType);

        return [
            'allowed' => $currentUsage < $quota,
            'current' => $currentUsage,
            'limit' => $quota,
            'remaining' => max(0, $quota - $currentUsage),
        ];
    }

    /**
     * Get current usage for quota type.
     */
    private function getCurrentUsage(int $tenantId, string $quotaType): int
    {
        // This would query the actual tables based on quota type
        return match ($quotaType) {
            'student' => $this->db->table('users')
                ->where('school_id', $tenantId)
                ->where('role', 'student')
                ->countAllResults(),
            'staff' => $this->db->table('users')
                ->where('school_id', $tenantId)
                ->whereIn('role', ['teacher', 'admin', 'staff'])
                ->countAllResults(),
            'storage' => (int) ($this->db->table('tenants')
                ->select('storage_used_mb')
                ->where('id', $tenantId)
                ->get()
                ->getRowArray()['storage_used_mb'] ?? 0),
            default => 0,
        };
    }

    /**
     * Update branding.
     */
    public function updateBranding(int $tenantId, array $branding): bool
    {
        $branding['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->table('tenant_branding')
            ->where('tenant_id', $tenantId)
            ->update($branding);
    }

    /**
     * Get tenant by subdomain.
     */
    public function getBySubdomain(string $subdomain): ?array
    {
        return $this->db->table('tenants')
            ->where('subdomain', $subdomain)
            ->where('status', 'active')
            ->get()
            ->getRowArray();
    }

    /**
     * Get tenant by custom domain.
     */
    public function getByCustomDomain(string $domain): ?array
    {
        return $this->db->table('tenants')
            ->where('custom_domain', $domain)
            ->where('status', 'active')
            ->get()
            ->getRowArray();
    }

    /**
     * Complete onboarding step.
     */
    public function completeOnboardingStep(int $tenantId, string $step, array $data = []): bool
    {
        return $this->db->table('tenant_onboarding')
            ->where('tenant_id', $tenantId)
            ->where('step', $step)
            ->update([
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'data' => json_encode($data),
            ]);
    }

    /**
     * Get onboarding progress.
     */
    public function getOnboardingProgress(int $tenantId): array
    {
        $steps = $this->db->table('tenant_onboarding')
            ->where('tenant_id', $tenantId)
            ->get()
            ->getResultArray();

        $completed = array_filter($steps, fn ($s) => $s['status'] === 'completed');

        return [
            'steps' => $steps,
            'progress_percent' => count($steps) > 0 ? round((count($completed) / count($steps)) * 100) : 0,
            'is_complete' => count($completed) === count($steps),
        ];
    }
}
