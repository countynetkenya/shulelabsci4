<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clock text-primary"></i> Scheduled Jobs
        </h1>
        <a href="<?= site_url('scheduler/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Job
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

    <!-- Scheduled Jobs Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Background Jobs
                <?php if (!empty($jobs)): ?>
                    <span class="badge badge-info ml-2"><?= count($jobs) ?> jobs</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Job Class</th>
                            <th>Schedule</th>
                            <th class="text-center">Status</th>
                            <th>Next Run</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($jobs)): ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($job['name']) ?></strong>
                                        <?php if (!empty($job['description'])): ?>
                                            <br><small class="text-muted"><?= esc($job['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= esc($job['job_class']) ?></code></td>
                                    <td>
                                        <code><?= esc($job['cron_expression']) ?></code>
                                        <br><small class="text-muted"><?= esc($job['timezone'] ?? 'Africa/Nairobi') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($job['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($job['next_run_at'])): ?>
                                            <?= date('Y-m-d H:i', strtotime($job['next_run_at'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('scheduler/edit/' . $job['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('scheduler/delete/' . $job['id']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this scheduled job?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No scheduled jobs found.</p>
                                    <a href="<?= site_url('scheduler/create') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Create Your First Job
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
