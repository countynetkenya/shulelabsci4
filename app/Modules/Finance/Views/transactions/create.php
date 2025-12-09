<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-money-bill-wave"></i> Record Payment
        </h1>
        <a href="<?= base_url('finance/transactions') ?>" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fa fa-arrow-left fa-sm"></i> Back to List
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Validation Errors -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Create Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('finance/transactions/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_id">Invoice <span class="text-danger">*</span></label>
                            <select name="invoice_id" id="invoice_id" class="form-control" required>
                                <option value="">-- Select Invoice --</option>
                                <?php foreach ($invoices as $invoice): ?>
                                    <option value="<?= esc($invoice['id']) ?>" <?= old('invoice_id') == $invoice['id'] ? 'selected' : '' ?>>
                                        Invoice #<?= esc($invoice['reference_number']) ?> - 
                                        Student ID: <?= esc($invoice['student_id']) ?> - 
                                        Balance: KES <?= number_format($invoice['balance'], 2) ?>
                                        (<?= esc(ucfirst($invoice['status'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Select the invoice for this payment</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">KES</span>
                                </div>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control" 
                                       id="amount" 
                                       name="amount" 
                                       value="<?= old('amount') ?>" 
                                       placeholder="0.00"
                                       required>
                            </div>
                            <small class="form-text text-muted">Enter the payment amount</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="method">Payment Method <span class="text-danger">*</span></label>
                            <select name="method" id="method" class="form-control" required>
                                <option value="">-- Select Method --</option>
                                <option value="cash" <?= old('method') === 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="bank_transfer" <?= old('method') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="mobile_money" <?= old('method') === 'mobile_money' ? 'selected' : '' ?>>Mobile Money (M-Pesa)</option>
                                <option value="cheque" <?= old('method') === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                            </select>
                            <small class="form-text text-muted">How was the payment made?</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reference_code">Reference Code</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="reference_code" 
                                   name="reference_code" 
                                   value="<?= old('reference_code') ?>" 
                                   maxlength="100"
                                   placeholder="e.g., M-Pesa code, Cheque number">
                            <small class="form-text text-muted">Transaction reference (optional)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="paid_at">Payment Date & Time</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   id="paid_at" 
                                   name="paid_at" 
                                   value="<?= old('paid_at', date('Y-m-d\TH:i')) ?>">
                            <small class="form-text text-muted">When was the payment received? (defaults to now)</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Record Payment
                    </button>
                    <a href="<?= base_url('finance/transactions') ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
