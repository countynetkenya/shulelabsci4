<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate text-primary"></i> Students
        </h1>
        <a href="<?= site_url('students/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Student
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search/Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('students') ?>" method="get" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <input type="text" class="form-control" name="search" placeholder="Search name, admission..." value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="form-group mr-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="graduated" <?= ($filters['status'] ?? '') === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                        <option value="transferred" <?= ($filters['status'] ?? '') === 'transferred' ? 'selected' : '' ?>>Transferred</option>
                        <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 mr-2">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?= site_url('students') ?>" class="btn btn-secondary mb-2">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
    </div>

    <!-- Students Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Student Directory
                <?php if (!empty($students)): ?>
                    <span class="badge badge-info ml-2"><?= count($students) ?> students</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Admission #</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Parent Contact</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <?php if ($student['admission_number']): ?>
                                            <code><?= esc($student['admission_number']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($student['first_name']) ?> <?= esc($student['last_name']) ?></strong>
                                    </td>
                                    <td><?= $student['gender'] ? ucfirst(esc($student['gender'])) : '—' ?></td>
                                    <td>
                                        <?php if ($student['parent_name']): ?>
                                            <?= esc($student['parent_name']) ?><br>
                                            <small class="text-muted"><?= esc($student['parent_phone'] ?? '') ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = match($student['status']) {
                                            'active' => 'success',
                                            'inactive' => 'secondary',
                                            'graduated' => 'info',
                                            'transferred' => 'warning',
                                            'suspended' => 'danger',
                                            default => 'secondary'
                                        };
                                ?>
                                        <span class="badge badge-<?= $badgeClass ?>"><?= ucfirst(esc($student['status'])) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('students/edit/' . $student['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('students/delete/' . $student['id']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this student?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No students found.</p>
                                    <a href="<?= site_url('students/create') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Add Your First Student
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
