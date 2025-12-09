<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Application</h1>
        <a href="<?= site_url('admissions') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('admissions/update/' . $application['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="applicant_name">Applicant Name</label>
                    <input type="text" class="form-control <?= session('errors.applicant_name') ? 'is-invalid' : '' ?>" id="applicant_name" name="applicant_name" value="<?= old('applicant_name', $application['applicant_name']) ?>" required>
                    <?php if (session('errors.applicant_name')): ?>
                        <div class="invalid-feedback"><?= session('errors.applicant_name') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="grade_applied">Grade Applied For</label>
                        <input type="text" class="form-control <?= session('errors.grade_applied') ? 'is-invalid' : '' ?>" id="grade_applied" name="grade_applied" value="<?= old('grade_applied', $application['grade_applied']) ?>" required>
                        <?php if (session('errors.grade_applied')): ?>
                            <div class="invalid-feedback"><?= session('errors.grade_applied') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="parent_contact">Parent Contact</label>
                        <input type="text" class="form-control <?= session('errors.parent_contact') ? 'is-invalid' : '' ?>" id="parent_contact" name="parent_contact" value="<?= old('parent_contact', $application['parent_contact']) ?>" required>
                        <?php if (session('errors.parent_contact')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_contact') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="pending" <?= $application['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="accepted" <?= $application['status'] == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                        <option value="rejected" <?= $application['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes', $application['notes']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Application</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
