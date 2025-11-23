<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-money"></i> Finance Management</h1>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-file-text fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $finance_summary['total_invoices'] ?? 0 ?></div>
                            <div>Total Invoices</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-money fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= number_format($finance_summary['paid_amount'] ?? 0, 2) ?></div>
                            <div>Paid Amount</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> Recent Invoices</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($invoices) && !empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?= esc($invoice['invoiceID'] ?? $invoice['id']) ?></td>
                                <td><?= number_format($invoice['total_amount'] ?? 0, 2) ?></td>
                                <td>
                                    <?php if (($invoice['status'] ?? '') === 'paid'): ?>
                                        <span class="label label-success">Paid</span>
                                    <?php else: ?>
                                        <span class="label label-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($invoice['created_at'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No invoices found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
