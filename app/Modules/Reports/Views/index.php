<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reports</h1>
        <a href="<?= base_url('reports/create') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Report
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('reports') ?>">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search reports..." 
                               value="<?= esc($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="format" class="form-control">
                            <option value="">All Formats</option>
                            <option value="pdf" <?= ($filters['format'] ?? '') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                            <option value="excel" <?= ($filters['format'] ?? '') === 'excel' ? 'selected' : '' ?>>Excel</option>
                            <option value="csv" <?= ($filters['format'] ?? '') === 'csv' ? 'selected' : '' ?>>CSV</option>
                            <option value="html" <?= ($filters['format'] ?? '') === 'html' ? 'selected' : '' ?>>HTML</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Reports</h6>
        </div>
        <div class="card-body">
            <?php if (empty($reports)): ?>
                <p class="text-center text-muted">No reports found. <a href="<?= base_url('reports/create') ?>">Create your first report</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Template</th>
                                <th>Format</th>
                                <th>Status</th>
                                <th>Last Generated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?= esc($report['name']) ?></td>
                                    <td><?= esc($templates[$report['template']] ?? $report['template']) ?></td>
                                    <td><span class="badge badge-info"><?= strtoupper(esc($report['format'])) ?></span></td>
                                    <td>
                                        <?php if ($report['status'] === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php elseif ($report['status'] === 'draft'): ?>
                                            <span class="badge badge-secondary">Draft</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Archived</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $report['last_generated_at'] ? date('Y-m-d H:i', strtotime($report['last_generated_at'])) : 'Never' ?></td>
                                    <td>
                                        <a href="<?= base_url('reports/' . $report['id'] . '/edit') ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= base_url('reports/' . $report['id'] . '/delete') ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this report?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
