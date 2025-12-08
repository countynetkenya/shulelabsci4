<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Student Wallets</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Wallet Accounts</h6>
        </div>
        <div class="card-body">
            <?php if (session()->has('message')): ?>
                <div class="alert alert-success"><?= session('message') ?></div>
            <?php endif ?>
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif ?>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Type</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wallets as $wallet): ?>
                            <tr>
                                <td><?= esc($wallet['user_id']) ?></td>
                                <td><?= esc($wallet['wallet_type']) ?></td>
                                <td>
                                    <span class="text-<?= $wallet['balance'] < 0 ? 'danger' : 'success' ?>">
                                        KES <?= number_format($wallet['balance'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= isset($wallet['status']) && $wallet['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= isset($wallet['status']) ? ucfirst($wallet['status']) : 'Unknown' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('wallets/topup/' . $wallet['id']) ?>" class="btn btn-sm btn-success"><i class="fas fa-plus-circle"></i> Top Up</a>
                                    <button class="btn btn-sm btn-info"><i class="fas fa-history"></i> History</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
