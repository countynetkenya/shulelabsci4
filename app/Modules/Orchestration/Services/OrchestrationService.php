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
        $total = $this->model->where('school_id', $schoolId)->countAllResults();
        $completed = $this->model->where('school_id', $schoolId)->where('status', 'completed')->countAllResults();
        $running = $this->model->where('school_id', $schoolId)->where('status', 'running')->countAllResults();
        $failed = $this->model->where('school_id', $schoolId)->where('status', 'failed')->countAllResults();
        return ['total_workflows' => $total, 'completed_workflows' => $completed, 'running_workflows' => $running, 'failed_workflows' => $failed];
    }
}
