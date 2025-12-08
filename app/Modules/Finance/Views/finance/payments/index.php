<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Payments</h1>
        <!-- Optional: Add manual payment button if needed, usually payments are linked to invoices -->
    </div>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Ref #</th>
                    <th class="py-3 px-6 text-left">Invoice Ref</th>
                    <th class="py-3 px-6 text-left">Student</th>
                    <th class="py-3 px-6 text-right">Amount</th>
                    <th class="py-3 px-6 text-center">Method</th>
                    <th class="py-3 px-6 text-center">Date</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="py-3 px-6 text-center">No payments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">
                                <span class="font-medium"><?= esc($payment['reference_number']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-left">
                                <span><?= esc($payment['invoice_ref']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-left">
                                <span><?= esc($payment['student_name']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-right">
                                <span><?= number_format($payment['amount'], 2) ?></span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <span class="bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs uppercase"><?= esc($payment['method']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <span><?= date('M d, Y', strtotime($payment['transaction_date'])) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
