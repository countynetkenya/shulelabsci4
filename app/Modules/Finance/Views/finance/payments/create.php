<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-lg mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Record Payment</h2>
        
        <?php if (session()->has('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form action="/finance/payments" method="POST">
            <?= csrf_field() ?>
            
            <?php if ($invoice): ?>
                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                <input type="hidden" name="student_id" value="<?= $invoice['student_id'] ?>">
                
                <div class="mb-4 p-4 bg-gray-100 rounded">
                    <p class="text-sm text-gray-600">Recording payment for Invoice:</p>
                    <p class="font-bold text-lg"><?= esc($invoice['reference_number']) ?></p>
                    <p class="text-sm">Student: <strong><?= esc($invoice['student_name']) ?></strong></p>
                    <p class="text-sm">Balance Due: <strong class="text-red-600"><?= number_format($invoice['balance'], 2) ?></strong></p>
                </div>
            <?php else: ?>
                <!-- Fallback if no invoice selected, though we primarily link from invoice -->
                <div class="mb-4">
                    <p class="text-red-500">Error: No invoice selected.</p>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
                    Payment Amount
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="amount" name="amount" type="number" step="0.01" placeholder="0.00" value="<?= old('amount', $invoice ? $invoice['balance'] : '') ?>" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="method">
                    Payment Method
                </label>
                <div class="relative">
                    <select class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline" id="method" name="method" required>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="cheque">Cheque</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="reference_number">
                    Transaction Reference (Optional)
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="reference_number" name="reference_number" type="text" placeholder="e.g. MPESA Code" value="<?= old('reference_number') ?>">
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Record Payment
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="<?= $invoice ? '/finance/invoices/show/' . $invoice['id'] : '/finance/invoices' ?>">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
