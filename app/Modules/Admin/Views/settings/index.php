<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog text-primary"></i> System Settings
        </h1>
        <a href="<?= site_url('admin/settings/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Setting
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

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Settings</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admin/settings') ?>" method="get" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <select name="class" class="form-control">
                        <option value="">All Categories</option>
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?= esc($cls) ?>" <?= ($filter ?? '') === $cls ? 'selected' : '' ?>>
                                    <?= esc($cls) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if ($filter ?? ''): ?>
                    <a href="<?= site_url('admin/settings') ?>" class="btn btn-secondary mb-2 ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Settings Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Settings List</h6>
        </div>
        <div class="card-body">
            <?php if (empty($settings)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> No settings found. <a href="<?= site_url('admin/settings/create') ?>">Create one now</a>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Category (Class)</th>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Type</th>
                                <th>Context</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($settings as $setting): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-info"><?= esc($setting['class']) ?></span>
                                    </td>
                                    <td><code><?= esc($setting['key']) ?></code></td>
                                    <td>
                                        <?php
                                        $value = $setting['value'];
                                        if (strlen($value) > 50) {
                                            echo '<span title="' . esc($value) . '">' . esc(substr($value, 0, 50)) . '...</span>';
                                        } else {
                                            echo esc($value);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= esc($setting['type'] ?? 'string') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light"><?= esc($setting['context'] ?? 'app') ?></span>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="<?= site_url('admin/settings/edit/' . $setting['id']) ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('admin/settings/delete/' . $setting['id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this setting?');">
                                            <i class="fas fa-trash"></i>
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
