<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Report</h1>
        <a href="<?= base_url('reports') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('reports/' . $report['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Report Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?= old('name', $report['name']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="template">Template <span class="text-danger">*</span></label>
                            <select class="form-control" id="template" name="template" required>
                                <option value="">-- Select Template --</option>
                                <?php foreach ($templates as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" 
                                            <?= old('template', $report['template']) === $key ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $report['description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="format">Output Format <span class="text-danger">*</span></label>
                            <select class="form-control" id="format" name="format" required>
                                <option value="pdf" <?= old('format', $report['format']) === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                <option value="excel" <?= old('format', $report['format']) === 'excel' ? 'selected' : '' ?>>Excel</option>
                                <option value="csv" <?= old('format', $report['format']) === 'csv' ? 'selected' : '' ?>>CSV</option>
                                <option value="html" <?= old('format', $report['format']) === 'html' ? 'selected' : '' ?>>HTML</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="draft" <?= old('status', $report['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="active" <?= old('status', $report['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="archived" <?= old('status', $report['status']) === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="is_scheduled">Scheduled?</label>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="is_scheduled" name="is_scheduled" value="1"
                                       <?= old('is_scheduled', $report['is_scheduled']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_scheduled">Enable Scheduling</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="schedule">Schedule (Cron Format)</label>
                    <input type="text" class="form-control" id="schedule" name="schedule" 
                           placeholder="e.g., 0 9 * * 1 (Every Monday at 9am)"
                           value="<?= old('schedule', $report['schedule']) ?>">
                    <small class="form-text text-muted">Leave empty for manual generation only.</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Report
                    </button>
                    <a href="<?= base_url('reports') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
