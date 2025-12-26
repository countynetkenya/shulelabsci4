<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-school text-primary"></i> Tenant Management
        </h1>
        <a href="<?= site_url('multitenant/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Tenant
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Tenants</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('multitenant') ?>" method="get" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($filter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="active" <?= ($filter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= ($filter ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="cancelled" <?= ($filter ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if ($filter ?? ''): ?>
                    <a href="<?= site_url('multitenant') ?>" class="btn btn-secondary mb-2 ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tenants List</h6>
        </div>
        <div class="card-body">
            <?php if (empty($tenants)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> No tenants found. <a href="<?= site_url('multitenant/create') ?>">Create one now</a>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subdomain</th>
                                <th>Status</th>
                                <th>Tier</th>
                                <th>Storage</th>
                                <th>Quotas</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenants as $tenant): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($tenant['name']) ?></strong>
                                        <?php if ($tenant['custom_domain']): ?>
                                            <br><small class="text-muted"><?= esc($tenant['custom_domain']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?= esc($tenant['subdomain']) ?></code>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending' => 'warning',
                                            'active' => 'success',
                                            'suspended' => 'danger',
                                            'cancelled' => 'secondary',
                                        ];
                                $badgeClass = $statusBadges[$tenant['status']] ?? 'secondary';
                                ?>
                                        <span class="badge badge-<?= $badgeClass ?>"><?= esc(ucfirst($tenant['status'])) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                $tierBadges = [
                                    'free' => 'light',
                                    'starter' => 'info',
                                    'professional' => 'primary',
                                    'enterprise' => 'dark',
                                ];
                                $tierClass = $tierBadges[$tenant['tier']] ?? 'light';
                                ?>
                                        <span class="badge badge-<?= $tierClass ?>"><?= esc(ucfirst($tenant['tier'])) ?></span>
                                    </td>
                                    <td>
                                        <?= number_format($tenant['storage_used_mb']) ?> / <?= number_format($tenant['storage_quota_mb']) ?> MB
                                        <?php
                                $usagePercent = $tenant['storage_quota_mb'] > 0
                                    ? ($tenant['storage_used_mb'] / $tenant['storage_quota_mb']) * 100
                                    : 0;
                                ?>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar <?= $usagePercent > 80 ? 'bg-danger' : 'bg-success' ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= min($usagePercent, 100) ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <small>
                                            <?php if ($tenant['student_quota']): ?>
                                                <i class="fas fa-user-graduate"></i> <?= number_format($tenant['student_quota']) ?><br>
                                            <?php endif; ?>
                                            <?php if ($tenant['staff_quota']): ?>
                                                <i class="fas fa-users"></i> <?= number_format($tenant['staff_quota']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="<?= site_url('multitenant/edit/' . $tenant['id']) ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($tenant['status'] === 'pending'): ?>
                                            <a href="<?= site_url('multitenant/activate/' . $tenant['id']) ?>" 
                                               class="btn btn-sm btn-success" 
                                               title="Activate"
                                               onclick="return confirm('Activate this tenant?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($tenant['status'] === 'active'): ?>
                                            <a href="<?= site_url('multitenant/suspend/' . $tenant['id']) ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Suspend"
                                               onclick="return confirm('Suspend this tenant?');">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= site_url('multitenant/delete/' . $tenant['id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this tenant? This cannot be undone!');">
                                            <i class="fas fa-trash"></i>
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
