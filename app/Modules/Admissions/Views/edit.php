<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Application
        </h1>
        <a href="<?= site_url('admissions') ?>" class="btn btn-secondary">
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
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Application Details</h6>
            <span class="badge badge-info"><?= esc($application['application_number'] ?? 'N/A') ?></span>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admissions/update/' . $application['id']) ?>" method="post">
                <?= csrf_field() ?>

                <h5 class="mb-3 text-primary">Student Information</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="student_first_name">First Name *</label>
                        <input type="text" class="form-control" id="student_first_name" name="student_first_name" 
                               value="<?= old('student_first_name', $application['student_first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="student_last_name">Last Name *</label>
                        <input type="text" class="form-control" id="student_last_name" name="student_last_name" 
                               value="<?= old('student_last_name', $application['student_last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="class_applied">Class Applied For *</label>
                        <input type="number" class="form-control" id="class_applied" name="class_applied" 
                               value="<?= old('class_applied', $application['class_applied'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="status">Status *</label>
                        <select class="form-control" id="status" name="status">
                            <?php $currentStatus = old('status', $application['status'] ?? 'submitted'); ?>
                            <option value="submitted" <?= $currentStatus === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                            <option value="under_review" <?= $currentStatus === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="interview_scheduled" <?= $currentStatus === 'interview_scheduled' ? 'selected' : '' ?>>Interview Scheduled</option>
                            <option value="test_scheduled" <?= $currentStatus === 'test_scheduled' ? 'selected' : '' ?>>Test Scheduled</option>
                            <option value="accepted" <?= $currentStatus === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="waitlisted" <?= $currentStatus === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
                            <option value="enrolled" <?= $currentStatus === 'enrolled' ? 'selected' : '' ?>>Enrolled</option>
                        </select>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3 text-primary">Parent/Guardian Information</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="parent_first_name">First Name *</label>
                        <input type="text" class="form-control" id="parent_first_name" name="parent_first_name" 
                               value="<?= old('parent_first_name', $application['parent_first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="parent_last_name">Last Name *</label>
                        <input type="text" class="form-control" id="parent_last_name" name="parent_last_name" 
                               value="<?= old('parent_last_name', $application['parent_last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="parent_email">Email *</label>
                        <input type="email" class="form-control" id="parent_email" name="parent_email" 
                               value="<?= old('parent_email', $application['parent_email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parent_phone">Phone *</label>
                        <input type="tel" class="form-control" id="parent_phone" name="parent_phone" 
                               value="<?= old('parent_phone', $application['parent_phone'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parent_relationship">Relationship *</label>
                        <select class="form-control" id="parent_relationship" name="parent_relationship" required>
                            <?php $currentRel = old('parent_relationship', $application['parent_relationship'] ?? ''); ?>
                            <option value="father" <?= $currentRel === 'father' ? 'selected' : '' ?>>Father</option>
                            <option value="mother" <?= $currentRel === 'mother' ? 'selected' : '' ?>>Mother</option>
                            <option value="guardian" <?= $currentRel === 'guardian' ? 'selected' : '' ?>>Guardian</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2"><?= old('address', $application['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="decision_notes">Decision Notes</label>
                    <textarea class="form-control" id="decision_notes" name="decision_notes" rows="3"><?= old('decision_notes', $application['decision_notes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Application
                    </button>
                    <a href="<?= site_url('admissions') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
