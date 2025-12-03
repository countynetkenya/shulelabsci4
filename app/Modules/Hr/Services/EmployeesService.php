<?php

declare(strict_types=1);

namespace Modules\Hr\Services;

use Modules\Hr\Models\EmployeeModel;

class EmployeesService
{
    protected EmployeeModel $model;

    public function __construct()
    {
        $this->model = new EmployeeModel();
    }

    /**
     * Paginate employees.
     */
    public function paginate(int $page = 1, int $perPage = 20): array
    {
        return [
            'data' => $this->model->paginate($perPage, 'default', $page),
            'pager' => $this->model->pager,
        ];
    }

    /**
     * Find employee by ID.
     */
    public function findById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Create a new employee.
     */
    public function create(array $data): int
    {
        $this->model->insert($data);
        return $this->model->getInsertID();
    }

    /**
     * Update an employee.
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Delete an employee.
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Validate employee data.
     */
    public function validate(array $data, ?int $id = null): array
    {
        if (!$this->model->validate($data)) {
            return $this->model->errors();
        }
        return [];
    }
}
