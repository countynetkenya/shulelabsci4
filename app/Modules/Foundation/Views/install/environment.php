<?php
$title = 'Environment Check';
$headerText = 'System Requirements Check';
?>
<?= $this->extend('Modules\Foundation\Views\install\layout') ?>

<?= $this->section('content') ?>

<div class="step-indicator">
    <div class="step active">
        <div class="step-circle">1</div>
        <div class="step-label">Environment</div>
    </div>
    <div class="step">
        <div class="step-circle">2</div>
        <div class="step-label">School Setup</div>
    </div>
    <div class="step">
        <div class="step-circle">3</div>
        <div class="step-label">Admin User</div>
    </div>
</div>

<h2 class="h4 mb-4">Pre-Installation Checks</h2>

<div class="mb-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <?php if ($dbConnected): ?>
                    ✅ Database Connection
                <?php else: ?>
                    ❌ Database Connection
                <?php endif; ?>
            </h5>
            <p class="card-text mb-0">
                <?php if ($dbConnected): ?>
                    Successfully connected to the database.
                <?php else: ?>
                    <span class="text-danger">Unable to connect to the database.</span><br>
                    <small>Please verify your database credentials in the <code>.env</code> file.</small>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<?php if ($dbConnected): ?>
<div class="mb-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <?php if ($migrationsOk): ?>
                    ✅ Database Migrations
                <?php else: ?>
                    ❌ Database Migrations
                <?php endif; ?>
            </h5>
            <p class="card-text mb-0">
                <?php if ($migrationsOk): ?>
                    All required database tables are present.
                <?php else: ?>
                    <span class="text-danger">Required database tables are missing.</span><br>
                    <small>Please run migrations before continuing:</small>
                    <pre class="mt-2 p-2 bg-light border rounded"><code>php bin/migrate/latest</code></pre>
                    <?php if (!empty($missingTables)): ?>
                        <small class="text-muted">Missing tables: <?= esc(implode(', ', $missingTables)) ?></small>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($dbConnected && $migrationsOk): ?>
<div class="d-grid">
    <a href="/install/tenants" class="btn btn-primary btn-lg">
        Continue to School Setup →
    </a>
</div>
<?php else: ?>
<div class="alert alert-warning" role="alert">
    <strong>Action Required:</strong> Please fix the issues above before continuing with the installation.
</div>
<div class="d-grid">
    <a href="/install" class="btn btn-outline-secondary">
        Refresh Checks
    </a>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
