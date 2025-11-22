<?php
$title = 'School Setup';
$headerText = 'Create Your Organisation and School';
?>
<?= $this->extend('Modules\Foundation\Views\install\layout') ?>

<?= $this->section('content') ?>

<div class="step-indicator">
    <div class="step completed">
        <div class="step-circle">✓</div>
        <div class="step-label">Environment</div>
    </div>
    <div class="step active">
        <div class="step-circle">2</div>
        <div class="step-label">School Setup</div>
    </div>
    <div class="step">
        <div class="step-circle">3</div>
        <div class="step-label">Admin User</div>
    </div>
</div>

<h2 class="h4 mb-4">Organisation and School Information</h2>

<form method="POST" action="/install/tenants">
    <?= csrf_field() ?>
    
    <div class="mb-4">
        <h5 class="border-bottom pb-2 mb-3">Organisation Details</h5>
        
        <div class="mb-3">
            <label for="organisation_name" class="form-label">Organisation Name <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?= isset($validation) && $validation->hasError('organisation_name') ? 'is-invalid' : '' ?>" 
                   id="organisation_name" 
                   name="organisation_name" 
                   value="<?= esc(old('organisation_name')) ?>"
                   placeholder="e.g., Nairobi Education Trust"
                   required>
            <?php if (isset($validation) && $validation->hasError('organisation_name')): ?>
                <div class="invalid-feedback">
                    <?= esc($validation->getError('organisation_name')) ?>
                </div>
            <?php endif; ?>
            <small class="form-text text-muted">The parent organisation managing this school</small>
        </div>

        <div class="mb-3">
            <label for="organisation_code" class="form-label">Organisation Code (Optional)</label>
            <input type="text" 
                   class="form-control <?= isset($validation) && $validation->hasError('organisation_code') ? 'is-invalid' : '' ?>" 
                   id="organisation_code" 
                   name="organisation_code" 
                   value="<?= esc(old('organisation_code')) ?>"
                   placeholder="e.g., NET or leave blank for auto-generated">
            <?php if (isset($validation) && $validation->hasError('organisation_code')): ?>
                <div class="invalid-feedback">
                    <?= esc($validation->getError('organisation_code')) ?>
                </div>
            <?php endif; ?>
            <small class="form-text text-muted">Unique identifier (letters, numbers, dashes only)</small>
        </div>

        <div class="mb-3">
            <label for="country" class="form-label">Country (Optional)</label>
            <input type="text" 
                   class="form-control" 
                   id="country" 
                   name="country" 
                   value="<?= esc(old('country')) ?>"
                   placeholder="e.g., Kenya">
            <small class="form-text text-muted">Country where the organisation operates</small>
        </div>
    </div>

    <div class="mb-4">
        <h5 class="border-bottom pb-2 mb-3">School Details</h5>
        
        <div class="mb-3">
            <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?= isset($validation) && $validation->hasError('school_name') ? 'is-invalid' : '' ?>" 
                   id="school_name" 
                   name="school_name" 
                   value="<?= esc(old('school_name')) ?>"
                   placeholder="e.g., Green Valley Academy"
                   required>
            <?php if (isset($validation) && $validation->hasError('school_name')): ?>
                <div class="invalid-feedback">
                    <?= esc($validation->getError('school_name')) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="school_code" class="form-label">School Code (Optional)</label>
            <input type="text" 
                   class="form-control <?= isset($validation) && $validation->hasError('school_code') ? 'is-invalid' : '' ?>" 
                   id="school_code" 
                   name="school_code" 
                   value="<?= esc(old('school_code')) ?>"
                   placeholder="e.g., GVA or leave blank for auto-generated">
            <?php if (isset($validation) && $validation->hasError('school_code')): ?>
                <div class="invalid-feedback">
                    <?= esc($validation->getError('school_code')) ?>
                </div>
            <?php endif; ?>
            <small class="form-text text-muted">Unique identifier (letters, numbers, dashes only)</small>
        </div>

        <div class="mb-3">
            <label for="curriculum" class="form-label">Curriculum (Optional)</label>
            <input type="text" 
                   class="form-control" 
                   id="curriculum" 
                   name="curriculum" 
                   value="<?= esc(old('curriculum')) ?>"
                   placeholder="e.g., CBC, IGCSE, IB">
            <small class="form-text text-muted">Educational curriculum followed by the school</small>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            Continue to Admin Setup →
        </button>
        <a href="/install" class="btn btn-outline-secondary">
            ← Back
        </a>
    </div>
</form>

<?= $this->endSection() ?>
