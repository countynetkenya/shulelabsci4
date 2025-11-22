<?php

declare(strict_types=1);

namespace Modules\Reports\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Report Fields Configuration
 * 
 * Defines reportable fields from each module for the metadata-driven
 * reporting engine. Each module contributes its own set of reportable
 * fields with metadata about type, aggregation, and display.
 */
class ReportFields extends BaseConfig
{
    /**
     * Reportable fields by module
     * 
     * @var array<string, array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>>
     */
    public array $fields = [
        'finance' => [
            'invoice_number' => [
                'label'        => 'Invoice Number',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'invoices',
            ],
            'invoice_amount' => [
                'label'        => 'Invoice Amount',
                'type'         => 'decimal',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'invoices',
                'alias'        => 'total_amount',
            ],
            'invoice_status' => [
                'label'        => 'Invoice Status',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'invoices',
                'alias'        => 'status',
            ],
            'payment_date' => [
                'label'        => 'Payment Date',
                'type'         => 'date',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'payments',
            ],
            'payment_method' => [
                'label'        => 'Payment Method',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'payments',
            ],
        ],
        'hr' => [
            'employee_id' => [
                'label'        => 'Employee ID',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'employees',
            ],
            'employee_name' => [
                'label'        => 'Employee Name',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'employees',
            ],
            'department' => [
                'label'        => 'Department',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'employees',
            ],
            'salary' => [
                'label'        => 'Salary',
                'type'         => 'decimal',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'payslips',
            ],
            'hire_date' => [
                'label'        => 'Hire Date',
                'type'         => 'date',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'employees',
            ],
        ],
        'inventory' => [
            'item_code' => [
                'label'        => 'Item Code',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'items',
            ],
            'item_name' => [
                'label'        => 'Item Name',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'items',
            ],
            'quantity' => [
                'label'        => 'Quantity',
                'type'         => 'integer',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'inventory',
            ],
            'unit_price' => [
                'label'        => 'Unit Price',
                'type'         => 'decimal',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'items',
            ],
            'category' => [
                'label'        => 'Category',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'items',
            ],
        ],
        'learning' => [
            'course_id' => [
                'label'        => 'Course ID',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'courses',
            ],
            'course_name' => [
                'label'        => 'Course Name',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'courses',
            ],
            'student_id' => [
                'label'        => 'Student ID',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'enrollments',
            ],
            'enrollment_date' => [
                'label'        => 'Enrollment Date',
                'type'         => 'date',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'enrollments',
            ],
            'grade' => [
                'label'        => 'Grade',
                'type'         => 'decimal',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'grades',
            ],
        ],
        'library' => [
            'document_id' => [
                'label'        => 'Document ID',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'documents',
            ],
            'document_title' => [
                'label'        => 'Document Title',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'documents',
            ],
            'category' => [
                'label'        => 'Category',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'documents',
            ],
            'downloads' => [
                'label'        => 'Downloads',
                'type'         => 'integer',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'documents',
            ],
        ],
        'threads' => [
            'thread_id' => [
                'label'        => 'Thread ID',
                'type'         => 'string',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'threads',
            ],
            'message_count' => [
                'label'        => 'Message Count',
                'type'         => 'integer',
                'aggregatable' => true,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'threads',
            ],
            'created_date' => [
                'label'        => 'Created Date',
                'type'         => 'date',
                'aggregatable' => false,
                'filterable'   => true,
                'sortable'     => true,
                'table'        => 'threads',
                'alias'        => 'created_at',
            ],
        ],
    ];

    /**
     * Get reportable fields for a module
     * 
     * @param string $module
     * @return array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>
     */
    public function getFieldsForModule(string $module): array
    {
        return $this->fields[$module] ?? [];
    }

    /**
     * Get a specific field configuration
     * 
     * @param string $module
     * @param string $field
     * @return array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}|null
     */
    public function getField(string $module, string $field): ?array
    {
        return $this->fields[$module][$field] ?? null;
    }

    /**
     * Get all aggregatable fields for a module
     * 
     * @param string $module
     * @return array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>
     */
    public function getAggregatableFields(string $module): array
    {
        $fields = $this->getFieldsForModule($module);
        return array_filter($fields, static fn($field) => $field['aggregatable']);
    }

    /**
     * Get all filterable fields for a module
     * 
     * @param string $module
     * @return array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>
     */
    public function getFilterableFields(string $module): array
    {
        $fields = $this->getFieldsForModule($module);
        return array_filter($fields, static fn($field) => $field['filterable']);
    }
}
