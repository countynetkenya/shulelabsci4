<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Invoices</h1>
        <a href="/finance/invoices/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New Invoice
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Ref #</th>
                    <th class="py-3 px-6 text-left">Student</th>
                    <th class="py-3 px-6 text-right">Amount</th>
                    <th class="py-3 px-6 text-right">Balance</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-center">Due Date</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="py-3 px-6 text-center">No invoices found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">
                                <span class="font-medium"><?= esc($invoice['reference_number']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-left">
                                <span><?= esc($invoice['student_name']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-right">
                                <span><?= number_format($invoice['amount'], 2) ?></span>
                            </td>
                            <td class="py-3 px-6 text-right">
                                <span><?= number_format($invoice['balance'], 2) ?></span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <?php
                                    $statusClass = match($invoice['status']) {
                                        'paid' => 'bg-green-200 text-green-600',
                                        'partial' => 'bg-yellow-200 text-yellow-600',
                                        'unpaid' => 'bg-red-200 text-red-600',
                                        'overdue' => 'bg-gray-200 text-gray-600',
                                        default => 'bg-gray-200 text-gray-600'
                                    };
                        ?>
                                <span class="<?= $statusClass ?> py-1 px-3 rounded-full text-xs uppercase"><?= esc($invoice['status']) ?></span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <span><?= date('M d, Y', strtotime($invoice['due_date'])) ?></span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="/finance/invoices/show/<?= $invoice['id'] ?>" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
