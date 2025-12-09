<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus"></i> New Application
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Application Form</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admissions/store') ?>" method="post">
                <?= csrf_field() ?>

                <h5 class="mb-3 text-primary">Student Information</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="student_first_name">First Name *</label>
                        <input type="text" class="form-control <?= session('errors.student_first_name') ? 'is-invalid' : '' ?>" 
                               id="student_first_name" name="student_first_name" value="<?= old('student_first_name') ?>" required>
                        <?php if (session('errors.student_first_name')): ?>
                            <div class="invalid-feedback"><?= session('errors.student_first_name') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="student_last_name">Last Name *</label>
                        <input type="text" class="form-control <?= session('errors.student_last_name') ? 'is-invalid' : '' ?>" 
                               id="student_last_name" name="student_last_name" value="<?= old('student_last_name') ?>" required>
                        <?php if (session('errors.student_last_name')): ?>
                            <div class="invalid-feedback"><?= session('errors.student_last_name') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="student_dob">Date of Birth *</label>
                        <input type="date" class="form-control <?= session('errors.student_dob') ? 'is-invalid' : '' ?>" 
                               id="student_dob" name="student_dob" value="<?= old('student_dob') ?>" required>
                        <?php if (session('errors.student_dob')): ?>
                            <div class="invalid-feedback"><?= session('errors.student_dob') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="student_gender">Gender *</label>
                        <select class="form-control <?= session('errors.student_gender') ? 'is-invalid' : '' ?>" 
                                id="student_gender" name="student_gender" required>
                            <option value="">Select...</option>
                            <option value="male" <?= old('student_gender') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= old('student_gender') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= old('student_gender') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                        <?php if (session('errors.student_gender')): ?>
                            <div class="invalid-feedback"><?= session('errors.student_gender') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="class_applied">Class Applied For *</label>
                        <input type="number" class="form-control <?= session('errors.class_applied') ? 'is-invalid' : '' ?>" 
                               id="class_applied" name="class_applied" value="<?= old('class_applied') ?>" required>
                        <?php if (session('errors.class_applied')): ?>
                            <div class="invalid-feedback"><?= session('errors.class_applied') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="previous_school">Previous School</label>
                    <input type="text" class="form-control" id="previous_school" name="previous_school" value="<?= old('previous_school') ?>">
                </div>

                <hr>

                <h5 class="mb-3 text-primary">Parent/Guardian Information</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="parent_first_name">First Name *</label>
                        <input type="text" class="form-control <?= session('errors.parent_first_name') ? 'is-invalid' : '' ?>" 
                               id="parent_first_name" name="parent_first_name" value="<?= old('parent_first_name') ?>" required>
                        <?php if (session('errors.parent_first_name')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_first_name') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="parent_last_name">Last Name *</label>
                        <input type="text" class="form-control <?= session('errors.parent_last_name') ? 'is-invalid' : '' ?>" 
                               id="parent_last_name" name="parent_last_name" value="<?= old('parent_last_name') ?>" required>
                        <?php if (session('errors.parent_last_name')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_last_name') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="parent_email">Email *</label>
                        <input type="email" class="form-control <?= session('errors.parent_email') ? 'is-invalid' : '' ?>" 
                               id="parent_email" name="parent_email" value="<?= old('parent_email') ?>" required>
                        <?php if (session('errors.parent_email')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_email') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parent_phone">Phone *</label>
                        <input type="tel" class="form-control <?= session('errors.parent_phone') ? 'is-invalid' : '' ?>" 
                               id="parent_phone" name="parent_phone" value="<?= old('parent_phone') ?>" required>
                        <?php if (session('errors.parent_phone')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_phone') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parent_relationship">Relationship *</label>
                        <select class="form-control <?= session('errors.parent_relationship') ? 'is-invalid' : '' ?>" 
                                id="parent_relationship" name="parent_relationship" required>
                            <option value="">Select...</option>
                            <option value="father" <?= old('parent_relationship') === 'father' ? 'selected' : '' ?>>Father</option>
                            <option value="mother" <?= old('parent_relationship') === 'mother' ? 'selected' : '' ?>>Mother</option>
                            <option value="guardian" <?= old('parent_relationship') === 'guardian' ? 'selected' : '' ?>>Guardian</option>
                        </select>
                        <?php if (session('errors.parent_relationship')): ?>
                            <div class="invalid-feedback"><?= session('errors.parent_relationship') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2"><?= old('address') ?></textarea>
                </div>

                <hr>

                <h5 class="mb-3 text-primary">Application Details</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="academic_year">Academic Year *</label>
                        <input type="text" class="form-control <?= session('errors.academic_year') ? 'is-invalid' : '' ?>" 
                               id="academic_year" name="academic_year" value="<?= old('academic_year', $currentYear ?? date('Y')) ?>" 
                               placeholder="2024" required>
                        <?php if (session('errors.academic_year')): ?>
                            <div class="invalid-feedback"><?= session('errors.academic_year') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="term">Term</label>
                        <select class="form-control" id="term" name="term">
                            <option value="">Not specified</option>
                            <option value="1" <?= old('term') === '1' ? 'selected' : '' ?>>Term 1</option>
                            <option value="2" <?= old('term') === '2' ? 'selected' : '' ?>>Term 2</option>
                            <option value="3" <?= old('term') === '3' ? 'selected' : '' ?>>Term 3</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Submit Application
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
