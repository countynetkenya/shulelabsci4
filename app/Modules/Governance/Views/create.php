<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-gavel"></i> Create Policy
        </h1>
        <a href="<?= site_url('governance') ?>" class="btn btn-secondary">
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
            <h6 class="m-0 font-weight-bold text-primary">Policy Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('governance/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="title">Policy Title *</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= old('title') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="category">Category *</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?= old('category') ?>" 
                               placeholder="e.g., Academic, Financial, HR" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="summary">Summary</label>
                    <textarea class="form-control" id="summary" name="summary" rows="2"><?= old('summary') ?></textarea>
                    <small class="form-text text-muted">Brief description of the policy (optional)</small>
                </div>

                <div class="form-group">
                    <label for="content">Policy Content *</label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?= old('content') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="version">Version</label>
                        <input type="text" class="form-control" id="version" name="version" value="<?= old('version', '1.0') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="under_review" <?= old('status') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="approved" <?= old('status') === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="archived" <?= old('status') === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="effective_date">Effective Date</label>
                        <input type="date" class="form-control" id="effective_date" name="effective_date" value="<?= old('effective_date') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="review_date">Review Date</label>
                        <input type="date" class="form-control" id="review_date" name="review_date" value="<?= old('review_date') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Policy
                    </button>
                    <a href="<?= site_url('governance') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
