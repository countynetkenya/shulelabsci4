<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Policy
        </h1>
        <a href="<?= site_url('governance') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Policy Details</h6>
            <span class="badge badge-info"><?= esc($policy['policy_number'] ?? 'N/A') ?></span>
        </div>
        <div class="card-body">
            <form action="<?= site_url('governance/update/' . $policy['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="title">Policy Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= old('title', $policy['title'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="category">Category *</label>
                        <input type="text" class="form-control" id="category" name="category" 
                               value="<?= old('category', $policy['category'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="summary">Summary</label>
                    <textarea class="form-control" id="summary" name="summary" rows="2"><?= old('summary', $policy['summary'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="content">Policy Content *</label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?= old('content', $policy['content'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="version">Version</label>
                        <input type="text" class="form-control" id="version" name="version" 
                               value="<?= old('version', $policy['version'] ?? '1.0') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <?php $currentStatus = old('status', $policy['status'] ?? 'draft'); ?>
                            <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="under_review" <?= $currentStatus === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="approved" <?= $currentStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="archived" <?= $currentStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="effective_date">Effective Date</label>
                        <input type="date" class="form-control" id="effective_date" name="effective_date" 
                               value="<?= old('effective_date', $policy['effective_date'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="review_date">Review Date</label>
                        <input type="date" class="form-control" id="review_date" name="review_date" 
                               value="<?= old('review_date', $policy['review_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Policy
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
