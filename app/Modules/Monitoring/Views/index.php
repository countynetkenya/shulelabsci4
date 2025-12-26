<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-heartbeat"></i> System Monitoring
        </h1>
        <a href="<?= base_url('monitoring/create') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Add Metric
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success"><?= session('success') ?></div>
    <?php endif; ?>
    
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Metrics</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('monitoring') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Metric Name</label>
                            <select name="metric_name" class="form-control">
                                <option value="">All Metrics</option>
                                <?php foreach ($metricNames as $name): ?>
                                    <option value="<?= esc($name) ?>" <?= ($filters['metric_name'] ?? '') === $name ? 'selected' : '' ?>>
                                        <?= esc($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Metric Type</label>
                            <select name="metric_type" class="form-control">
                                <option value="">All Types</option>
                                <?php foreach ($metricTypes as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= ($filters['metric_type'] ?? '') === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(esc($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="datetime-local" name="date_from" class="form-control" 
                                   value="<?= esc($filters['date_from'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="datetime-local" name="date_to" class="form-control" 
                                   value="<?= esc($filters['date_to'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Metrics Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
        </div>
        <div class="card-body">
            <?php if (empty($metrics)): ?>
                <div class="text-center py-5">
                    <i class="fa fa-heartbeat fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No metrics found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Metric Name</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Labels</th>
                                <th>Recorded At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics as $metric): ?>
                                <tr>
                                    <td><?= esc($metric['id']) ?></td>
                                    <td><strong><?= esc($metric['metric_name']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= $this->getMetricTypeBadgeClass($metric['metric_type']) ?>">
                                            <?= esc($metric['metric_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($metric['value'], 2) ?></td>
                                    <td>
                                        <?php if (!empty($metric['labels']) && is_array($metric['labels'])): ?>
                                            <small><?= esc(json_encode($metric['labels'])) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i:s', strtotime($metric['recorded_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('monitoring/edit/' . $metric['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('monitoring/delete/' . $metric['id']) ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this metric?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function for badge colors
function getMetricTypeBadgeClass($type)
{
    $classes = [
        'counter' => 'success',
        'gauge' => 'info',
        'histogram' => 'warning',
        'summary' => 'primary',
    ];
    return $classes[$type] ?? 'secondary';
}
?>
<?= $this->endSection() ?>
