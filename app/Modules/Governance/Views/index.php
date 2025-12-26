<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-gavel text-primary"></i> Governance & Policies
        </h1>
        <a href="<?= site_url('governance/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Policy
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <?php if (!empty($statistics)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Policies</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['total'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['by_status']['approved'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Under Review</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['by_status']['under_review'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Draft</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['by_status']['draft'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Policy List
                <?php if (!empty($policies)): ?>
                    <span class="badge badge-info ml-2"><?= count($policies) ?> policies</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Policy #</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Version</th>
                            <th>Effective Date</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($policies)): ?>
                            <?php foreach ($policies as $policy): ?>
                                <tr>
                                    <td><code><?= esc($policy['policy_number'] ?? 'N/A') ?></code></td>
                                    <td><strong><?= esc($policy['title']) ?></strong></td>
                                    <td><span class="badge badge-secondary"><?= esc($policy['category']) ?></span></td>
                                    <td><?= esc($policy['version'] ?? '1.0') ?></td>
                                    <td><?= esc($policy['effective_date'] ?? 'Not set') ?></td>
                                    <td>
                                        <?php
                                        $statusBadge = match($policy['status'] ?? 'draft') {
                                            'approved' => 'success',
                                            'archived' => 'secondary',
                                            'under_review' => 'warning',
                                            default => 'light'
                                        };
                                ?>
                                        <span class="badge badge-<?= $statusBadge ?>">
                                            <?= esc(ucwords(str_replace('_', ' ', $policy['status'] ?? 'draft'))) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('governance/edit/' . $policy['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('governance/delete/' . $policy['id']) ?>" class="btn btn-sm btn-danger" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this policy?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-gavel fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No policies found.</p>
                                    <a href="<?= site_url('governance/create') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Create First Policy
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
