<?php
namespace Modules\Orchestration\Services;
use Modules\Orchestration\Models\WorkflowModel;
class OrchestrationService
{
    protected WorkflowModel $model;
    public function __construct() { $this->model = new WorkflowModel(); }
    public function getAll(int $schoolId): array {
        return $this->model->where('school_id', $schoolId)->orderBy('created_at', 'DESC')->findAll();
    }
    public function getById(int $id, int $schoolId): ?array {
        return $this->model->where('id', $id)->where('school_id', $schoolId)->first();
    }
    public function create(array $data): int|false {
        if (empty($data['workflow_id'])) {
            $data['workflow_id'] = 'wf_' . date('YmdHis') . '_' . uniqid();
        }
        return $this->model->insert($data);
    }
    public function update(int $id, array $data): bool {
        return $this->model->update($id, $data);
    }
    public function delete(int $id): bool {
        return $this->model->delete($id);
    }
    public function getStatistics(int $schoolId): array {
        $db = \Config\Database::connect();
        
        // Single optimized query with conditional counting
        $result = $db->query("
            SELECT 
                COUNT(*) as total_workflows,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_workflows,
                SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_workflows,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_workflows
            FROM workflows 
            WHERE school_id = ?
        ", [$schoolId])->getRowArray();

        return [
            'total_workflows' => (int)($result['total_workflows'] ?? 0),
            'completed_workflows' => (int)($result['completed_workflows'] ?? 0),
            'running_workflows' => (int)($result['running_workflows'] ?? 0),
            'failed_workflows' => (int)($result['failed_workflows'] ?? 0),
        ];
    }
}
