<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus text-primary"></i> Admissions Applications
        </h1>
        <a href="<?= site_url('admissions/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Application
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

    <!-- Statistics Cards -->
    <?php if (!empty($statistics)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Applications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['total'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Accepted</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['by_status']['accepted'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Under Review</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['by_status']['under_review'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Submitted</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['by_status']['submitted'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Applications Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Applications List
                <?php if (!empty($applications)): ?>
                    <span class="badge badge-info ml-2"><?= count($applications) ?> applications</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>App #</th>
                            <th>Student Name</th>
                            <th>Class Applied</th>
                            <th>Parent Contact</th>
                            <th>Academic Year</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($applications)): ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><code><?= esc($app['application_number'] ?? 'N/A') ?></code></td>
                                    <td>
                                        <strong><?= esc($app['student_first_name'] ?? '') ?> <?= esc($app['student_last_name'] ?? '') ?></strong>
                                    </td>
                                    <td><?= esc($app['class_applied'] ?? 'N/A') ?></td>
                                    <td>
                                        <?= esc($app['parent_email'] ?? '') ?><br>
                                        <small class="text-muted"><?= esc($app['parent_phone'] ?? '') ?></small>
                                    </td>
                                    <td><?= esc($app['academic_year'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        $statusBadge = match($app['status'] ?? 'submitted') {
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            'under_review' => 'warning',
                                            'interview_scheduled', 'test_scheduled' => 'info',
                                            'waitlisted' => 'secondary',
                                            'enrolled' => 'primary',
                                            default => 'light'
                                        };
                                ?>
                                        <span class="badge badge-<?= $statusBadge ?>">
                                            <?= esc(ucwords(str_replace('_', ' ', $app['status'] ?? 'submitted'))) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('admissions/edit/' . $app['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('admissions/delete/' . $app['id']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this application?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No applications found.</p>
                                    <a href="<?= site_url('admissions/create') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Create First Application
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
