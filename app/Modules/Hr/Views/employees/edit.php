<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Employee</h1>
        <a href="<?= site_url('hr/employees') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <form action="<?= site_url('hr/employees/edit/' . $employee['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id">User ID</label>
                            <input type="number" class="form-control" id="user_id" name="user_id" value="<?= old('user_id', $employee['user_id']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="employee_number">Employee Number</label>
                            <input type="text" class="form-control" id="employee_number" name="employee_number" value="<?= old('employee_number', $employee['employee_number']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="department_id">Department ID</label>
                            <input type="number" class="form-control" id="department_id" name="department_id" value="<?= old('department_id', $employee['department_id']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="designation_id">Designation ID</label>
                            <input type="number" class="form-control" id="designation_id" name="designation_id" value="<?= old('designation_id', $employee['designation_id']) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="employment_type">Employment Type</label>
                            <select class="form-control" id="employment_type" name="employment_type">
                                <option value="permanent" <?= old('employment_type', $employee['employment_type']) === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                <option value="contract" <?= old('employment_type', $employee['employment_type']) === 'contract' ? 'selected' : '' ?>>Contract</option>
                                <option value="intern" <?= old('employment_type', $employee['employment_type']) === 'intern' ? 'selected' : '' ?>>Intern</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="join_date">Joining Date</label>
                            <input type="date" class="form-control" id="join_date" name="join_date" value="<?= old('join_date', $employee['join_date']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="basic_salary">Basic Salary</label>
                            <input type="number" step="0.01" class="form-control" id="basic_salary" name="basic_salary" value="<?= old('basic_salary', $employee['basic_salary']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="active" <?= old('status', $employee['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status', $employee['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="terminated" <?= old('status', $employee['status']) === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Employee</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
