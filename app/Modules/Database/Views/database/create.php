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

    <!-- Create Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Backup Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('database/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name">Backup Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           value="<?= old('name') ?>" 
                           placeholder="e.g., Weekly Backup - <?= date('Y-m-d') ?>"
                           required>
                    <small class="form-text text-muted">Enter a descriptive name for this backup</small>
                </div>

                <div class="form-group">
                    <label for="type">Backup Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <option value="full" <?= old('type') === 'full' ? 'selected' : '' ?>>Full Backup</option>
                        <option value="incremental" <?= old('type') === 'incremental' ? 'selected' : '' ?>>Incremental Backup</option>
                        <option value="differential" <?= old('type') === 'differential' ? 'selected' : '' ?>>Differential Backup</option>
                    </select>
                    <small class="form-text text-muted">Choose the type of backup to perform</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Backup
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
