<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employees</h1>
        <a href="<?= site_url('hr/employees/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Employee
        </a>
    </div>

    <?= $this->extend('layouts/app') ?>

<?= $this->section('header') ?>
    Employees
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <p class="mt-2 text-sm text-gray-700">A list of all employees including their name, department, and status.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="<?= site_url('hr/employees/create') ?>" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                Add Employee
            </a>
        </div>
    </div>
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee #</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User ID</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Department</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Designation</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php if (empty($employees)): ?>
                                <tr>
                                    <td colspan="7" class="py-4 pl-4 pr-3 text-sm text-center text-gray-500 sm:pl-6">No employees found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"><?= esc($employee['employee_number']) ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= esc($employee['user_id']) ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= esc($employee['department_id']) ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= esc($employee['designation_id']) ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?= esc(ucfirst($employee['employment_type'])) ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?= $employee['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= esc(ucfirst($employee['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a href="<?= site_url('hr/employees/edit/' . $employee['id']) ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee #</th>
                            <th>Name</th> <!-- Assuming user relation or name field exists, but schema has user_id. We might need to join users table in service. For now, I'll just show ID or placeholder -->
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No employees found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?= esc($employee['employee_number']) ?></td>
                                    <td>User ID: <?= esc($employee['user_id']) ?></td> <!-- TODO: Join with users table -->
                                    <td><?= esc($employee['department_id']) ?></td> <!-- TODO: Join with departments -->
                                    <td><?= esc($employee['designation_id']) ?></td> <!-- TODO: Join with designations -->
                                    <td><?= esc(ucfirst($employee['employment_type'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $employee['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= esc(ucfirst($employee['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('hr/employees/edit/' . $employee['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('hr/employees/delete/' . $employee['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <?= $pager->links() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
