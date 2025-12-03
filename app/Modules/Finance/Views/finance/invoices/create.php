<!DOCTYPE html>
<html>
<head>
    <title>Create Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Create New Invoice</h1>
        
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form action="<?= site_url('finance/invoices') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="student_id" class="form-label">Student</label>
                <select name="student_id" id="student_id" class="form-control" required>
                    <option value="">Select Student</option>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>" <?= old('student_id') == $student['id'] ? 'selected' : '' ?>>
                                <?= esc($student['full_name']) ?> (<?= esc($student['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="fee_structure_id" class="form-label">Fee Structure (Optional)</label>
                <select name="fee_structure_id" id="fee_structure_id" class="form-control">
                    <option value="">Select Fee Structure</option>
                    <?php if (!empty($feeStructures)): ?>
                        <?php foreach ($feeStructures as $fee): ?>
                            <option value="<?= $fee['id'] ?>" <?= old('fee_structure_id') == $fee['id'] ? 'selected' : '' ?>>
                                <?= esc($fee['name']) ?> - <?= esc($fee['amount']) ?>
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
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control" value="<?= old('due_date') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Invoice</button>
            <a href="<?= site_url('finance') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
