<?php

namespace Modules\Reports\Models;

use CodeIgniter\Model;

/**
 * ReportModel - Manages report definitions
 */
class ReportModel extends Model
{
    protected $table            = 'reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id',
        'name',
        'description',
        'template',
        'parameters',
        'format',
        'schedule',
        'is_scheduled',
        'last_generated_at',
        'file_path',
        'status',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'school_id'  => 'required|integer',
        'name'       => 'required|max_length[255]',
        'template'   => 'required|max_length[100]',
        'format'     => 'required|in_list[pdf,excel,csv,html]',
        'status'     => 'permit_empty|in_list[draft,active,archived]',
        'created_by' => 'required|integer',
    ];

    protected $validationMessages = [
        'school_id' => [
            'required' => 'School ID is required',
        ],
        'name' => [
            'required' => 'Report name is required',
        ],
        'template' => [
            'required' => 'Template is required',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get reports for a specific school
     */
    public function getReportsBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('name', $filters['search'])
                ->orLike('description', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['format'])) {
            $builder->where('format', $filters['format']);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get available templates
     */
    public function getTemplates(): array
    {
        return [
            'student_performance' => 'Student Performance Report',
            'attendance_summary' => 'Attendance Summary',
            'financial_overview' => 'Financial Overview',
            'inventory_status' => 'Inventory Status',
            'library_circulation' => 'Library Circulation',
            'custom' => 'Custom Report',
        ];
    }
}
