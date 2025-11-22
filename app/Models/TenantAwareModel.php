<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TenantAwareModel Base Class
 * 
 * Provides automatic tenant scoping for all queries to prevent cross-tenant data leaks.
 * All models that handle tenant-specific data should extend this class.
 * 
 * @package App\Models
 */
class TenantAwareModel extends Model
{
    /**
     * The column name used for tenant scoping
     * Override in child classes if using different column (e.g., 'organisation_id', 'warehouse_id')
     * 
     * @var string
     */
    protected string $tenantColumn = 'school_id';
    
    /**
     * Current tenant ID
     * Set via setTenantId() before querying
     * 
     * @var string|null
     */
    protected ?string $tenantId = null;
    
    /**
     * Whether tenant scoping is enforced
     * When true, queries without tenant ID will throw exception
     * 
     * @var bool
     */
    protected bool $enforceTenantScoping = true;
    
    /**
     * Set the current tenant ID
     * 
     * @param string $tenantId The tenant identifier
     * @return self
     */
    public function setTenantId(string $tenantId): self
    {
        $this->tenantId = $tenantId;
        return $this;
    }
    
    /**
     * Get the current tenant ID
     * 
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
    
    /**
     * Apply tenant scope to query builder
     * 
     * @throws \RuntimeException If tenant ID is not set and enforcement is enabled
     */
    protected function applyTenantScope(): void
    {
        if ($this->enforceTenantScoping && !$this->tenantId) {
            throw new \RuntimeException(
                sprintf('Tenant ID must be set before querying %s. Call setTenantId() first.', static::class)
            );
        }
        
        if ($this->tenantId) {
            $this->builder()->where($this->tenantColumn, $this->tenantId);
        }
    }
    
    /**
     * Find a single record by ID (tenant-scoped)
     * 
     * @param int|string|array|null $id
     * @return array|object|null
     */
    public function find($id = null)
    {
        $this->applyTenantScope();
        return parent::find($id);
    }
    
    /**
     * Find all records (tenant-scoped)
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(int $limit = 0, int $offset = 0): array
    {
        $this->applyTenantScope();
        return parent::findAll($limit, $offset);
    }
    
    /**
     * Find records by column value (tenant-scoped)
     * 
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public function findColumn(string $column, $value = null): array
    {
        $this->applyTenantScope();
        return parent::findColumn($column, $value);
    }
    
    /**
     * Get first record (tenant-scoped)
     * 
     * @return array|object|null
     */
    public function first()
    {
        $this->applyTenantScope();
        return parent::first();
    }
    
    /**
     * Insert a new record with automatic tenant ID injection
     * 
     * @param array|object|null $data
     * @param bool $returnID
     * @return bool|int|string
     */
    public function insert($data = null, bool $returnID = true)
    {
        // Inject tenant ID if not already set
        if ($this->tenantId) {
            if (is_array($data)) {
                $data[$this->tenantColumn] = $data[$this->tenantColumn] ?? $this->tenantId;
            } elseif (is_object($data)) {
                $data->{$this->tenantColumn} = $data->{$this->tenantColumn} ?? $this->tenantId;
            }
        }
        
        return parent::insert($data, $returnID);
    }
    
    /**
     * Update records (tenant-scoped)
     * 
     * @param int|string|array|null $id
     * @param array|object|null $data
     * @return bool
     */
    public function update($id = null, $data = null): bool
    {
        $this->applyTenantScope();
        
        // Prevent changing tenant_id via update
        if (is_array($data) && isset($data[$this->tenantColumn])) {
            unset($data[$this->tenantColumn]);
        } elseif (is_object($data) && isset($data->{$this->tenantColumn})) {
            unset($data->{$this->tenantColumn});
        }
        
        return parent::update($id, $data);
    }
    
    /**
     * Delete records (tenant-scoped)
     * 
     * @param int|string|array|null $id
     * @param bool $purge
     * @return bool
     */
    public function delete($id = null, bool $purge = false): bool
    {
        $this->applyTenantScope();
        return parent::delete($id, $purge);
    }
    
    /**
     * Soft delete records (tenant-scoped)
     * 
     * @param int|string|array $id
     * @return bool
     */
    public function softDelete($id): bool
    {
        $this->applyTenantScope();
        
        if ($this->useSoftDeletes) {
            return $this->update($id, [$this->deletedField => $this->setDate()]);
        }
        
        return $this->delete($id, false);
    }
    
    /**
     * Count all results (tenant-scoped)
     * 
     * @param bool $reset
     * @return int
     */
    public function countAllResults(bool $reset = true): int
    {
        $this->applyTenantScope();
        return parent::countAllResults($reset);
    }
    
    /**
     * Get paginated results (tenant-scoped)
     * 
     * @param int|null $perPage
     * @param string $group
     * @param int|null $page
     * @param int $segment
     * @return array
     */
    public function paginate(?int $perPage = null, string $group = 'default', ?int $page = null, int $segment = 0): array
    {
        $this->applyTenantScope();
        return parent::paginate($perPage, $group, $page, $segment);
    }
    
    /**
     * Temporarily disable tenant scoping for a single query
     * Use with extreme caution - only for administrative operations
     * 
     * @return self
     */
    public function withoutTenantScope(): self
    {
        $this->enforceTenantScoping = false;
        $this->tenantId = null;
        return $this;
    }
    
    /**
     * Re-enable tenant scoping
     * 
     * @return self
     */
    public function withTenantScope(): self
    {
        $this->enforceTenantScoping = true;
        return $this;
    }
    
    /**
     * Verify a record belongs to the current tenant
     * 
     * @param int|string $id
     * @return bool
     */
    public function belongsToTenant($id): bool
    {
        if (!$this->tenantId) {
            throw new \RuntimeException('Tenant ID must be set to verify ownership');
        }
        
        $record = $this->builder()
            ->where($this->primaryKey, $id)
            ->where($this->tenantColumn, $this->tenantId)
            ->get()
            ->getRow();
        
        return $record !== null;
    }
    
    /**
     * Get builder instance with tenant scope applied
     * Override to customize query building
     * 
     * @param string|null $table
     * @return \CodeIgniter\Database\BaseBuilder
     */
    public function builder(?string $table = null)
    {
        $builder = parent::builder($table);
        
        // Note: Scope is applied in individual methods to allow chaining
        // If you want automatic scoping on builder(), uncomment:
        // if ($this->tenantId) {
        //     $builder->where($this->tenantColumn, $this->tenantId);
        // }
        
        return $builder;
    }
}
