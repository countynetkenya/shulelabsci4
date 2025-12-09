<?php

namespace App\Modules\MultiTenant\Models;

use CodeIgniter\Model;

/**
 * TenantModel - Manages tenant (school) registry
 * 
 * Handles multi-tenant school/organization management.
 */
class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uuid', 'name', 'subdomain', 'custom_domain', 'status', 'tier',
        'settings', 'features', 'storage_quota_mb', 'storage_used_mb',
        'student_quota', 'staff_quota', 'trial_ends_at', 'activated_at',
        'suspended_at', 'cancelled_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'      => 'required|max_length[200]',
        'subdomain' => 'required|max_length[63]|is_unique[tenants.subdomain,id,{id}]',
        'status'    => 'permit_empty|in_list[pending,active,suspended,cancelled]',
        'tier'      => 'permit_empty|in_list[free,starter,professional,enterprise]',
    ];

    protected $validationMessages   = [
        'subdomain' => [
            'is_unique' => 'This subdomain is already taken.',
        ],
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['generateUuid'];
    protected $beforeUpdate = [];

    /**
     * Generate UUID before insert
     */
    protected function generateUuid(array $data): array
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = $this->generateUuidV4();
        }
        return $data;
    }

    /**
     * Generate a v4 UUID
     */
    protected function generateUuidV4(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get active tenants only
     */
    public function getActive(): array
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get tenants by tier
     */
    public function getByTier(string $tier): array
    {
        return $this->where('tier', $tier)->findAll();
    }

    /**
     * Get tenant by subdomain
     */
    public function getBySubdomain(string $subdomain): ?array
    {
        return $this->where('subdomain', $subdomain)->first();
    }

    /**
     * Get tenant by custom domain
     */
    public function getByCustomDomain(string $domain): ?array
    {
        return $this->where('custom_domain', $domain)->first();
    }
}
