<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TenantModel - Base model with automatic tenant scoping.
 *
 * All models extending this class will automatically scope queries
 * to the current school/tenant context.
 */
abstract class TenantModel extends Model
{
    /**
     * Whether to use tenant scoping.
     */
    protected bool $useTenant = true;

    /**
     * Column name for tenant ID (usually 'school_id').
     */
    protected string $tenantColumn = 'school_id';

    /**
     * Automatically scope queries to current tenant.
     */
    protected function scopeToTenant(): self
    {
        if ($this->useTenant && $this->hasColumn($this->tenantColumn)) {
            $tenantService = service('tenant');
            $schoolId      = $tenantService->getCurrentSchoolId();

            if ($schoolId) {
                $this->where($this->table . '.' . $this->tenantColumn, $schoolId);
            }
        }

        return $this;
    }

    /**
     * Override find to include tenant scope.
     *
     * @param array|int|string|null $id
     *
     * @return array|object|null
     */
    public function find($id = null)
    {
        $this->scopeToTenant();

        return parent::find($id);
    }

    /**
     * Override findAll to include tenant scope.
     *
     * @return array
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->scopeToTenant();

        return parent::findAll($limit, $offset);
    }

    /**
     * Override first to include tenant scope.
     *
     * @return array|object|null
     */
    public function first()
    {
        $this->scopeToTenant();

        return parent::first();
    }

    /**
     * Override insert to add tenant ID automatically.
     *
     * @param array|null $data
     * @param bool       $returnID
     *
     * @return bool|int|string
     */
    public function insert($data = null, bool $returnID = true)
    {
        if ($this->useTenant && $this->hasColumn($this->tenantColumn)) {
            $tenantService = service('tenant');
            $schoolId      = $tenantService->getCurrentSchoolId();

            if ($schoolId && is_array($data) && ! isset($data[$this->tenantColumn])) {
                $data[$this->tenantColumn] = $schoolId;
            }
        }

        return parent::insert($data, $returnID);
    }

    /**
     * Check if table has a specific column.
     *
     * @param string $column Column name
     */
    protected function hasColumn(string $column): bool
    {
        try {
            $fields = $this->db->getFieldNames($this->table);

            return in_array($column, $fields, true);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Disable tenant scoping for specific query.
     *
     * Use this when you need to query across all tenants (e.g., SuperAdmin reports)
     */
    public function withoutTenant(): self
    {
        $this->useTenant = false;

        return $this;
    }

    /**
     * Scope query to specific school.
     *
     * @param int $schoolId School ID
     */
    public function forSchool(int $schoolId): self
    {
        if ($this->hasColumn($this->tenantColumn)) {
            $this->where($this->table . '.' . $this->tenantColumn, $schoolId);
        }

        return $this;
    }

    /**
     * Scope query to multiple schools.
     *
     * @param array $schoolIds Array of school IDs
     */
    public function forSchools(array $schoolIds): self
    {
        if ($this->hasColumn($this->tenantColumn)) {
            $this->whereIn($this->table . '.' . $this->tenantColumn, $schoolIds);
        }

        return $this;
    }

    /**
     * Get all records across all tenants (SuperAdmin only).
     *
     * @return array
     */
    public function getAllTenants(int $limit = 0, int $offset = 0)
    {
        return $this->withoutTenant()->findAll($limit, $offset);
    }
}
