<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-wallet"></i> Wallets
        </h1>
        <a href="<?= base_url('wallets/create') ?>" class="btn btn-primary btn-sm shadow-sm">
            <i class="fa fa-plus fa-sm text-white-50"></i> Create Wallet
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
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Wallets</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['total_wallets'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Balance</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">KES <?= number_format($summary['total_balance'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Balance</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">KES <?= number_format($summary['average_balance'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Wallets</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['active_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallets Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Wallet List</h6>
        </div>
        <div class="card-body">
            <?php if (empty($wallets)): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No wallets found. <a href="<?= base_url('wallets/create') ?>">Create one</a> to get started.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Wallet Type</th>
                                <th>Balance</th>
                                <th>Currency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wallets as $wallet): ?>
                                <tr>
                                    <td><?= esc($wallet['id']) ?></td>
                                    <td><?= esc($wallet['user_id']) ?></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?= esc(ucfirst($wallet['wallet_type'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        <?= esc($wallet['currency']) ?> <?= number_format($wallet['balance'], 2) ?>
                                    </td>
                                    <td><?= esc($wallet['currency']) ?></td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'active'    => 'success',
                                            'suspended' => 'warning',
                                            'closed'    => 'danger',
                                        ];
                                        $badgeClass = $statusBadges[$wallet['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= esc(ucfirst($wallet['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('wallets/edit/' . $wallet['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('wallets/delete/' . $wallet['id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this wallet?')"
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
