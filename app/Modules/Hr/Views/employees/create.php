<?= $this->extend('layouts/app') ?>

<?= $this->section('header') ?>
    Add Employee
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">New Employee Details</h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="<?= site_url('hr/employees') ?>" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Back</a>
        </div>
    </div>

    <form action="<?= site_url('hr/employees/create') ?>" method="post" class="space-y-8 divide-y divide-gray-200 bg-white p-6 shadow rounded-lg">
        <?= csrf_field() ?>
        
        <?php if (session()->has('errors')) : ?>
            <div class="rounded-md bg-red-50 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul role="list" class="list-disc pl-5 space-y-1">
                                <?php foreach (session('errors') as $error) : ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="user_id" class="block text-sm font-medium text-gray-700">User ID (Temporary)</label>
                        <div class="mt-1">
                            <input type="number" name="user_id" id="user_id" value="<?= old('user_id') ?>" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">In a real scenario, this would be a user search/select.</p>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="employee_number" class="block text-sm font-medium text-gray-700">Employee Number</label>
                        <div class="mt-1">
                            <input type="text" name="employee_number" id="employee_number" value="<?= old('employee_number') ?>" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department ID</label>
                        <div class="mt-1">
                            <input type="number" name="department_id" id="department_id" value="<?= old('department_id') ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="designation_id" class="block text-sm font-medium text-gray-700">Designation ID</label>
                        <div class="mt-1">
                            <input type="number" name="designation_id" id="designation_id" value="<?= old('designation_id') ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="employment_type" class="block text-sm font-medium text-gray-700">Employment Type</label>
                        <div class="mt-1">
                            <select id="employment_type" name="employment_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="permanent" <?= old('employment_type') === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                <option value="contract" <?= old('employment_type') === 'contract' ? 'selected' : '' ?>>Contract</option>
                                <option value="intern" <?= old('employment_type') === 'intern' ? 'selected' : '' ?>>Intern</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-1">
                            <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="terminated" <?= old('status') === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="join_date" class="block text-sm font-medium text-gray-700">Joining Date</label>
                        <div class="mt-1">
                            <input type="date" name="join_date" id="join_date" value="<?= old('join_date') ?>" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="basic_salary" class="block text-sm font-medium text-gray-700">Basic Salary</label>
                        <div class="mt-1">
                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" value="<?= old('basic_salary') ?>" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5">
            <div class="flex justify-end">
                <a href="<?= site_url('hr/employees') ?>" class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Save Employee</button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
