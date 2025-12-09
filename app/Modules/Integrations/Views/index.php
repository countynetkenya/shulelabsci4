<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-plug"></i> Integrations
        </h1>
        <a href="<?= base_url('integrations/create') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Add Integration
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success"><?= session('success') ?></div>
    <?php endif; ?>
    
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Integrations</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('integrations') ?>">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Integration Type</label>
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <?php foreach ($types as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Integrations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Third-Party Integrations</h6>
        </div>
        <div class="card-body">
            <?php if (empty($integrations)): ?>
                <div class="text-center py-5">
                    <i class="fa fa-plug fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No integrations configured yet.</p>
                    <a href="<?= base_url('integrations/create') ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Your First Integration
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Adapter Class</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($integrations as $integration): ?>
                                <tr>
                                    <td><?= esc($integration['id']) ?></td>
                                    <td><strong><?= esc($integration['name']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= esc($types[$integration['type']] ?? ucfirst($integration['type'])) ?>
                                        </span>
                                    </td>
                                    <td><code><?= esc($integration['adapter_class']) ?></code></td>
                                    <td>
                                        <?php if ($integration['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($integration['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('integrations/edit/' . $integration['id']) ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('integrations/delete/' . $integration['id']) ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this integration?')">
                                            <i class="fa fa-trash"></i>
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
