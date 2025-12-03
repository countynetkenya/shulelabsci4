<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employees</h1>
        <a href="<?= site_url('hr/employees/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Employee
        </a>
    </div>

    <?php if (session()->has('message')) : ?>
        <div class="alert alert-success">
            <?= session('message') ?>
        </div>
    <?php endif ?>

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
