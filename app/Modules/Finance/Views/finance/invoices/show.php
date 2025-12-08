<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800">Invoice Details</h2>
            <span class="text-gray-500 text-sm">Ref: <?= esc($invoice['reference_number']) ?></span>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-gray-600 text-sm uppercase font-bold">Student</p>
                <p class="text-lg font-semibold"><?= esc($invoice['student_name']) ?></p>
                <p class="text-gray-500"><?= esc($invoice['student_email']) ?></p>
            </div>
            <div class="text-right">
                <p class="text-gray-600 text-sm uppercase font-bold">Status</p>
                <?php
                    $statusClass = match($invoice['status']) {
                        'paid' => 'text-green-600',
                        'partial' => 'text-yellow-600',
                        'unpaid' => 'text-red-600',
                        'overdue' => 'text-gray-600',
                        default => 'text-gray-600'
                    };
                ?>
                <p class="text-lg font-bold uppercase <?= $statusClass ?>"><?= esc($invoice['status']) ?></p>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex justify-between border-b py-2">
                <span class="text-gray-600">Amount Due</span>
                <span class="font-bold"><?= number_format($invoice['amount'], 2) ?></span>
            </div>
            <div class="flex justify-between border-b py-2">
                <span class="text-gray-600">Balance Pending</span>
                <span class="font-bold text-red-600"><?= number_format($invoice['balance'], 2) ?></span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-600">Due Date</span>
                <span><?= date('M d, Y', strtotime($invoice['due_date'])) ?></span>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <a href="/finance/invoices" class="text-blue-500 hover:text-blue-800 font-bold">
                &larr; Back to Invoices
            </a>
            <?php if ($invoice['balance'] > 0): ?>
                <a href="/finance/payments/create?invoice_id=<?= $invoice['id'] ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Record Payment
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
