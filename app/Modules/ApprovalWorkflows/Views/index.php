<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-clipboard-check"></i> Approval Workflows
        </h1>
        <a href="<?= base_url('approvals/create') ?>" class="btn btn-primary btn-sm shadow-sm">
            <i class="fa fa-plus fa-sm text-white-50"></i> New Request
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_requests'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['pending_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['approved_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['rejected_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Approval Requests</h6>
        </div>
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No approval requests found. <a href="<?= base_url('approvals/create') ?>">Create one</a> to get started.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Entity Type</th>
                                <th>Entity ID</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= esc($request['id']) ?></td>
                                    <td><?= esc($request['entity_type']) ?></td>
                                    <td><?= esc($request['entity_id']) ?></td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending'     => 'warning',
                                            'in_progress' => 'info',
                                            'approved'    => 'success',
                                            'rejected'    => 'danger',
                                            'cancelled'   => 'secondary',
                                            'expired'     => 'dark',
                                        ];
                                        $badgeClass = $statusBadges[$request['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= esc(ucwords(str_replace('_', ' ', $request['status']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityBadges = [
                                            'low'    => 'secondary',
                                            'normal' => 'primary',
                                            'high'   => 'warning',
                                            'urgent' => 'danger',
                                        ];
                                        $priorityBadge = $priorityBadges[$request['priority']] ?? 'primary';
                                        ?>
                                        <span class="badge badge-<?= $priorityBadge ?>">
                                            <?= esc(ucfirst($request['priority'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($request['requested_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('approvals/edit/' . $request['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('approvals/delete/' . $request['id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this request?')"
                                           title="Delete">
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
<?= $this->endSection() ?>
