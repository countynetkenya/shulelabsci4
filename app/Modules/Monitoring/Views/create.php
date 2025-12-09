<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-heartbeat"></i> Create Metric
        </h1>
        <a href="<?= base_url('monitoring') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Metric Details</h6>
        </div>
        <div class="card-body">
            <form method="post" action="<?= base_url('monitoring/store') ?>">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="metric_name">Metric Name <span class="text-danger">*</span></label>
                            <input type="text" name="metric_name" id="metric_name" class="form-control" 
                                   value="<?= old('metric_name') ?>" required
                                   placeholder="e.g., active_users, response_time_ms">
                            <small class="form-text text-muted">Use snake_case for metric names.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="metric_type">Metric Type <span class="text-danger">*</span></label>
                            <select name="metric_type" id="metric_type" class="form-control" required>
                                <option value="">Select Type</option>
                                <?php foreach ($metricTypes as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= old('metric_type') === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(esc($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Counter: Incrementing values | Gauge: Current value | Histogram: Distribution | Summary: Aggregated stats
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="value">Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" name="value" id="value" class="form-control" 
                                   value="<?= old('value') ?>" required
                                   placeholder="e.g., 123.45">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="recorded_at">Recorded At</label>
                            <input type="datetime-local" name="recorded_at" id="recorded_at" class="form-control" 
                                   value="<?= old('recorded_at') ?>">
                            <small class="form-text text-muted">Leave empty to use current timestamp.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="labels">Labels (JSON)</label>
                    <textarea name="labels" id="labels" class="form-control" rows="3" 
                              placeholder='{"environment": "production", "server": "web-01"}'><?= old('labels') ?></textarea>
                    <small class="form-text text-muted">Optional metadata in JSON format.</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Metric
                    </button>
                    <a href="<?= base_url('monitoring') ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
