<?php
$title = 'Admin Account';
$headerText = 'Create Your Administrator Account';
?>
<?= $this->extend('Modules\Foundation\Views\install\layout') ?>

<?= $this->section('content') ?>

<div class="step-indicator">
    <div class="step completed">
        <div class="step-circle">✓</div>
        <div class="step-label">Environment</div>
    </div>
    <div class="step completed">
        <div class="step-circle">✓</div>
        <div class="step-label">School Setup</div>
    </div>
    <div class="step active">
        <div class="step-circle">3</div>
        <div class="step-label">Admin User</div>
    </div>
</div>

<h2 class="h4 mb-4">Administrator Account</h2>

<p class="text-muted mb-4">
    Create your administrator account. This will be the primary account with full system access.
</p>

<form method="POST" action="/install/admin">
    <?= csrf_field() ?>
    
    <div class="mb-3">
        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control <?= isset($validation) && $validation->hasError('full_name') ? 'is-invalid' : '' ?>" 
               id="full_name" 
               name="full_name" 
               value="<?= esc(old('full_name')) ?>"
               placeholder="e.g., John Doe"
               required>
        <?php if (isset($validation) && $validation->hasError('full_name')): ?>
            <div class="invalid-feedback">
                <?= esc($validation->getError('full_name')) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
        <input type="email" 
               class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>" 
               id="email" 
               name="email" 
               value="<?= esc(old('email')) ?>"
               placeholder="admin@example.com"
               required>
        <?php if (isset($validation) && $validation->hasError('email')): ?>
            <div class="invalid-feedback">
                <?= esc($validation->getError('email')) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control <?= isset($validation) && $validation->hasError('username') ? 'is-invalid' : '' ?>" 
               id="username" 
               name="username" 
               value="<?= esc(old('username')) ?>"
               placeholder="admin"
               required>
        <?php if (isset($validation) && $validation->hasError('username')): ?>
            <div class="invalid-feedback">
                <?= esc($validation->getError('username')) ?>
            </div>
        <?php endif; ?>
        <small class="form-text text-muted">Letters, numbers, dashes, and underscores only</small>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
        <input type="password" 
               class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>" 
               id="password" 
               name="password" 
               required>
        <?php if (isset($validation) && $validation->hasError('password')): ?>
            <div class="invalid-feedback">
                <?= esc($validation->getError('password')) ?>
            </div>
        <?php endif; ?>
        <small class="form-text text-muted">Minimum 8 characters</small>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
        <input type="password" 
               class="form-control <?= isset($validation) && $validation->hasError('password_confirm') ? 'is-invalid' : '' ?>" 
               id="password_confirm" 
               name="password_confirm" 
               required>
        <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
            <div class="invalid-feedback">
                <?= esc($validation->getError('password_confirm')) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="alert alert-info" role="alert">
        <strong>Note:</strong> This account will be created with Super Administrator privileges, giving you full system access.
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            Complete Installation ✓
        </button>
        <a href="/install/tenants" class="btn btn-outline-secondary">
            ← Back
        </a>
    </div>
</form>

<?= $this->endSection() ?>
