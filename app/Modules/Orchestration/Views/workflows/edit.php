<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= base_url('orchestration') ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <?php if (session()->has('errors')): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach (session('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Workflow Details</h6></div>
        <div class="card-body">
            <form action="<?= base_url('orchestration/update/' . $workflow['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group"><label>Workflow ID</label><input type="text" class="form-control" value="<?= esc($workflow['workflow_id']) ?>" readonly></div>
                <div class="form-group"><label>Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" value="<?= old('name', $workflow['name']) ?>" required></div>
                <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="3"><?= old('description', $workflow['description']) ?></textarea></div>
                <div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="pending" <?= old('status', $workflow['status']) === 'pending' ? 'selected' : '' ?>>Pending</option><option value="running" <?= old('status', $workflow['status']) === 'running' ? 'selected' : '' ?>>Running</option><option value="completed" <?= old('status', $workflow['status']) === 'completed' ? 'selected' : '' ?>>Completed</option><option value="failed" <?= old('status', $workflow['status']) === 'failed' ? 'selected' : '' ?>>Failed</option><option value="paused" <?= old('status', $workflow['status']) === 'paused' ? 'selected' : '' ?>>Paused</option></select></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Workflow</button>
                    <a href="<?= base_url('orchestration') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
