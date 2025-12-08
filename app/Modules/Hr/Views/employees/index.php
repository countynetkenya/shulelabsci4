<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employees</h1>
        <a href="<?= site_url('hr/employees/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Employee
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee #</th>
                            <th>Name</th>
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
                                    <td><?= esc($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                                    <td><?= esc($employee['department_id']) ?></td>
                                    <td><?= esc($employee['designation_id']) ?></td>
                                    <td><?= esc($employee['employment_type']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $employee['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($employee['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('hr/employees/edit/' . $employee['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
<?= $this->endSection() ?>
