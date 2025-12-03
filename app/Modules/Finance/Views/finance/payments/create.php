<!DOCTYPE html>
<html>
<head>
    <title>Record Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Record Payment</h1>
        
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger">
                <?= session('error') ?>
            </div>
        <?php endif ?>

        <form action="<?= site_url('finance/payments') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="invoice_id" class="form-label">Invoice</label>
                <select name="invoice_id" id="invoice_id" class="form-control" required>
                    <option value="">Select Invoice</option>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <option value="<?= $invoice['id'] ?>" <?= old('invoice_id') == $invoice['id'] ? 'selected' : '' ?>>
                                <?= esc($invoice['reference_number']) ?> - Bal: <?= esc($invoice['balance']) ?> (<?= esc($invoice['student_name'] ?? 'Student #' . $invoice['student_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="<?= old('amount') ?>" required>
            </div>

            <div class="mb-3">
                <label for="method" class="form-label">Payment Method</label>
                <select name="method" id="method" class="form-control" required>
                    <option value="cash" <?= old('method') == 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank_transfer" <?= old('method') == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="mobile_money" <?= old('method') == 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                    <option value="cheque" <?= old('method') == 'cheque' ? 'selected' : '' ?>>Cheque</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="reference_code" class="form-label">Reference Code (Optional)</label>
                <input type="text" name="reference_code" id="reference_code" class="form-control" value="<?= old('reference_code') ?>">
            </div>

            <div class="mb-3">
                <label for="paid_at" class="form-label">Payment Date</label>
                <input type="date" name="paid_at" id="paid_at" class="form-control" value="<?= old('paid_at', date('Y-m-d')) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Record Payment</button>
            <a href="<?= site_url('finance') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
