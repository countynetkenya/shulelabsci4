<?php

namespace App\Modules\Analytics\Models;

use CodeIgniter\Model;

/**
 * AnalyticsDashboardModel - Manages analytics_dashboards table
 */
class AnalyticsDashboardModel extends Model
{
    protected $table = 'analytics_dashboards';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'school_id',
        'name',
        'description',
        'layout',
        'is_default',
        'is_shared',
        'shared_with_roles',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'school_id' => 'required|integer',
        'name' => 'required|min_length[2]|max_length[150]',
        'layout' => 'permit_empty',
        'created_by' => 'required|integer',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Dashboard name is required.',
            'min_length' => 'Dashboard name must be at least 2 characters.',
        ],
    ];

    /**
     * Get dashboards by school
     */
    public function getDashboardsBySchool(int $schoolId, ?int $userId = null): array
    {
        $builder = $this->where('school_id', $schoolId);

        if ($userId !== null) {
            $builder->groupStart()
                ->where('created_by', $userId)
                ->orWhere('is_shared', 1)
                ->groupEnd();
        }

        return $builder->orderBy('is_default', 'DESC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
