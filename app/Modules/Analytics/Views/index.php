<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> Analytics Dashboards
        </h1>
        <a href="<?= site_url('analytics/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Dashboard
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Dashboard List
                <?php if (!empty($dashboards)): ?>
                    <span class="badge badge-info ml-2"><?= count($dashboards) ?> dashboards</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-center">Default</th>
                            <th class="text-center">Shared</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dashboards)): ?>
                            <?php foreach ($dashboards as $dashboard): ?>
                                <tr>
                                    <td><strong><?= esc($dashboard['name']) ?></strong></td>
                                    <td><?= esc($dashboard['description'] ?? 'No description') ?></td>
                                    <td class="text-center">
                                        <?php if ($dashboard['is_default']): ?>
                                            <span class="badge badge-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-light">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($dashboard['is_shared']): ?>
                                            <span class="badge badge-info">Shared</span>
                                        <?php else: ?>
                                            <span class="badge badge-light">Private</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('analytics/edit/' . $dashboard['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('analytics/delete/' . $dashboard['id']) ?>" class="btn btn-sm btn-danger" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this dashboard?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No dashboards found.</p>
                                    <a href="<?= site_url('analytics/create') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Create First Dashboard
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
