<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-history"></i> Audit Logs
        </h1>
        <a href="<?= base_url('audit/create') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Add Manual Entry
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success"><?= session('success') ?></div>
    <?php endif; ?>
    
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <?php if (session()->has('warning')): ?>
        <div class="alert alert-warning"><?= session('warning') ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Audit Logs</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('audit') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Event Type</label>
                            <select name="event_type" class="form-control">
                                <option value="">All Types</option>
                                <?php foreach ($eventTypes as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= ($filters['event_type'] ?? '') === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(esc($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Entity Type</label>
                            <select name="entity_type" class="form-control">
                                <option value="">All Entities</option>
                                <?php foreach ($entityTypes as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= ($filters['entity_type'] ?? '') === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(esc($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
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

    <!-- Audit Events Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Audit Events</h6>
        </div>
        <div class="card-body">
            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <i class="fa fa-history fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No audit events found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Type</th>
                                <th>Entity</th>
                                <th>User ID</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= esc($event['id']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $this->getEventTypeBadgeClass($event['event_type']) ?>">
                                            <?= esc($event['event_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($event['entity_type']): ?>
                                            <?= esc($event['entity_type']) ?> #<?= esc($event['entity_id']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($event['user_id'] ?? 'System') ?></td>
                                    <td><?= esc($event['ip_address'] ?? 'N/A') ?></td>
                                    <td><?= date('Y-m-d H:i:s', strtotime($event['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('audit/edit/' . $event['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View
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
function getEventTypeBadgeClass($type)
{
    $classes = [
        'create' => 'success',
        'update' => 'info',
        'delete' => 'danger',
        'view' => 'secondary',
        'access' => 'primary',
        'login' => 'success',
        'logout' => 'warning',
        'export' => 'info',
        'import' => 'primary',
        'configure' => 'dark',
    ];
    return $classes[$type] ?? 'secondary';
}
?>
<?= $this->endSection() ?>
