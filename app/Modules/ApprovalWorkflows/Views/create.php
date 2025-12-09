<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-clipboard-check"></i> New Approval Request
        </h1>
        <a href="<?= base_url('approvals') ?>" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fa fa-arrow-left fa-sm"></i> Back to List
        </a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Request Information</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('approvals/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="workflow_id">Workflow ID <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="workflow_id" name="workflow_id" value="<?= old('workflow_id', 1) ?>" required>
                            <small class="form-text text-muted">ID of the approval workflow to use</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="entity_type">Entity Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="entity_type" name="entity_type" value="<?= old('entity_type') ?>" maxlength="100" required>
                            <small class="form-text text-muted">Type of entity (e.g., purchase_request, leave_request)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="entity_id">Entity ID <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="entity_id" name="entity_id" value="<?= old('entity_id') ?>" required>
                            <small class="form-text text-muted">ID of the entity being approved</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="normal" <?= old('priority', 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="request_data">Request Data (JSON)</label>
                    <textarea class="form-control" id="request_data" name="request_data" rows="4" placeholder='{"key": "value"}'><?= old('request_data', '{}') ?></textarea>
                    <small class="form-text text-muted">Additional request data in JSON format</small>
                </div>

                <hr>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Request
                    </button>
                    <a href="<?= base_url('approvals') ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
