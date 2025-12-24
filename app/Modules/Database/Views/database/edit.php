<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= base_url('database') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Validation Errors -->
    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Backup Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('database/update/' . $backup['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="backup_id">Backup ID</label>
                    <input type="text" 
                           class="form-control" 
                           id="backup_id" 
                           value="<?= esc($backup['backup_id']) ?>" 
                           readonly>
                    <small class="form-text text-muted">Auto-generated backup identifier</small>
                </div>

                <div class="form-group">
                    <label for="name">Backup Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           value="<?= old('name', $backup['name']) ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="pending" <?= old('status', $backup['status']) === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= old('status', $backup['status']) === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= old('status', $backup['status']) === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= old('status', $backup['status']) === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="path">Path</label>
                    <input type="text" 
                           class="form-control" 
                           id="path" 
                           value="<?= esc($backup['path']) ?>" 
                           readonly>
                </div>

                <div class="form-group">
                    <label for="size">Size (bytes)</label>
                    <input type="text" 
                           class="form-control" 
                           id="size" 
                           value="<?= esc($backup['size']) ?> bytes (<?= round($backup['size'] / (1024 * 1024), 2) ?> MB)" 
                           readonly>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Backup
                    </button>
                    <a href="<?= base_url('database') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
