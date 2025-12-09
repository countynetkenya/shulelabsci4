<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-info"></i> Edit Scheduled Job
        </h1>
        <a href="<?= site_url('scheduler') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Scheduler
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Job Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('scheduler/update/' . $job['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Job Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" value="<?= old('name', $job['name']) ?>" 
                                   placeholder="e.g., Daily Attendance Report" required>
                            <?php if (session('errors.name')): ?>
                                <div class="invalid-feedback"><?= session('errors.name') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" <?= old('is_active', $job['is_active']) == '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= old('is_active', $job['is_active']) == '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" 
                              id="description" name="description" rows="2" 
                              placeholder="Brief description of what this job does"><?= old('description', $job['description'] ?? '') ?></textarea>
                    <?php if (session('errors.description')): ?>
                        <div class="invalid-feedback"><?= session('errors.description') ?></div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="job_class">Job Class <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.job_class') ? 'is-invalid' : '' ?>" 
                                   id="job_class" name="job_class" value="<?= old('job_class', $job['job_class']) ?>" 
                                   placeholder="e.g., App\Jobs\Reports\DailyReport" required>
                            <?php if (session('errors.job_class')): ?>
                                <div class="invalid-feedback"><?= session('errors.job_class') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="job_method">Job Method</label>
                            <input type="text" class="form-control <?= session('errors.job_method') ? 'is-invalid' : '' ?>" 
                                   id="job_method" name="job_method" value="<?= old('job_method', $job['job_method'] ?? 'handle') ?>" 
                                   placeholder="handle">
                            <?php if (session('errors.job_method')): ?>
                                <div class="invalid-feedback"><?= session('errors.job_method') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cron_expression">Cron Expression <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.cron_expression') ? 'is-invalid' : '' ?>" 
                                   id="cron_expression" name="cron_expression" value="<?= old('cron_expression', $job['cron_expression']) ?>" 
                                   placeholder="e.g., 0 8 * * * (Every day at 8 AM)" required>
                            <small class="form-text text-muted">
                                Examples: <code>0 8 * * *</code> (8 AM daily), <code>0 * * * *</code> (Every hour)
                            </small>
                            <?php if (session('errors.cron_expression')): ?>
                                <div class="invalid-feedback"><?= session('errors.cron_expression') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="timezone">Timezone</label>
                            <input type="text" class="form-control <?= session('errors.timezone') ? 'is-invalid' : '' ?>" 
                                   id="timezone" name="timezone" value="<?= old('timezone', $job['timezone'] ?? 'Africa/Nairobi') ?>" 
                                   placeholder="Africa/Nairobi">
                            <?php if (session('errors.timezone')): ?>
                                <div class="invalid-feedback"><?= session('errors.timezone') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="max_retries">Max Retries</label>
                            <input type="number" min="0" max="10" class="form-control" 
                                   id="max_retries" name="max_retries" value="<?= old('max_retries', $job['max_retries'] ?? 3) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="retry_delay_seconds">Retry Delay (seconds)</label>
                            <input type="number" min="0" class="form-control" 
                                   id="retry_delay_seconds" name="retry_delay_seconds" value="<?= old('retry_delay_seconds', $job['retry_delay_seconds'] ?? 60) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="timeout_seconds">Timeout (seconds)</label>
                            <input type="number" min="0" class="form-control" 
                                   id="timeout_seconds" name="timeout_seconds" value="<?= old('timeout_seconds', $job['timeout_seconds'] ?? 3600) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="parameters">Parameters (JSON)</label>
                    <textarea class="form-control <?= session('errors.parameters') ? 'is-invalid' : '' ?>" 
                              id="parameters" name="parameters" rows="3" 
                              placeholder='{"key": "value"}'><?= old('parameters', $job['parameters'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Optional JSON parameters to pass to the job</small>
                    <?php if (session('errors.parameters')): ?>
                        <div class="invalid-feedback"><?= session('errors.parameters') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="overlap_prevention" name="overlap_prevention" value="1" <?= old('overlap_prevention', $job['overlap_prevention'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="overlap_prevention">
                            Prevent Overlap (Don't run if previous execution is still running)
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Scheduled Job
                    </button>
                    <a href="<?= site_url('scheduler') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
