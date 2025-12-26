<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-money-bill-wave"></i> Finance Transactions
        </h1>
        <a href="<?= base_url('finance/transactions/create') ?>" class="btn btn-primary btn-sm shadow-sm">
            <i class="fa fa-plus fa-sm text-white-50"></i> Record Payment
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($summary['total_transactions'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-receipt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Amount</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                KES <?= number_format($summary['total_amount'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Amount</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                KES <?= number_format($summary['average_amount'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-chart-line fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Largest Payment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                KES <?= number_format($summary['max_amount'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fa fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Transactions</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('finance/transactions') ?>" class="form-inline">
                <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search reference..." value="<?= esc($filters['search'] ?? '') ?>">
                
                <select name="method" class="form-control mr-2 mb-2">
                    <option value="">All Methods</option>
                    <option value="cash" <?= ($filters['method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank_transfer" <?= ($filters['method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="mobile_money" <?= ($filters['method'] ?? '') === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                    <option value="cheque" <?= ($filters['method'] ?? '') === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                </select>

                <input type="date" name="date_from" class="form-control mr-2 mb-2" placeholder="From Date" value="<?= esc($filters['date_from'] ?? '') ?>">
                <input type="date" name="date_to" class="form-control mr-2 mb-2" placeholder="To Date" value="<?= esc($filters['date_to'] ?? '') ?>">
                
                <button type="submit" class="btn btn-primary mr-2 mb-2"><i class="fa fa-filter"></i> Filter</button>
                <a href="<?= base_url('finance/transactions') ?>" class="btn btn-secondary mb-2"><i class="fa fa-refresh"></i> Clear</a>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transaction List</h6>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No transactions found. <a href="<?= base_url('finance/transactions/create') ?>">Record a payment</a> to get started.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Invoice ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Paid At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?= esc($transaction['id']) ?></td>
                                    <td><?= esc($transaction['invoice_id']) ?></td>
                                    <td class="text-right font-weight-bold">KES <?= number_format($transaction['amount'], 2) ?></td>
                                    <td>
                                        <?php
                                        $methodBadges = [
                                            'cash'          => 'success',
                                            'bank_transfer' => 'info',
                                            'mobile_money'  => 'warning',
                                            'cheque'        => 'secondary',
                                        ];
                                $badgeClass = $methodBadges[$transaction['method']] ?? 'secondary';
                                ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= esc(ucwords(str_replace('_', ' ', $transaction['method']))) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($transaction['reference_code'] ?? 'N/A') ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($transaction['paid_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('finance/transactions/edit/' . $transaction['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('finance/transactions/delete/' . $transaction['id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this transaction?')"
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
