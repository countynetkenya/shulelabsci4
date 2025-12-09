<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Create Dashboard
        </h1>
        <a href="<?= site_url('analytics') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <strong>Validation Errors:</strong>
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Dashboard Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('analytics/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name">Dashboard Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= old('name') ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= old('description') ?></textarea>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" <?= old('is_default') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_default">
                        Set as default dashboard
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_shared" name="is_shared" value="1" <?= old('is_shared') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_shared">
                        Share with other users
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Dashboard
                    </button>
                    <a href="<?= site_url('analytics') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
