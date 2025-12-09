<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-school text-primary"></i> Edit Tenant
        </h1>
        <a href="<?= site_url('multitenant') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tenants
        </a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tenant Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('multitenant/update/' . $tenant['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Tenant Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="<?= old('name', $tenant['name']) ?>" 
                               required
                               placeholder="e.g., Nairobi High School">
                        <small class="form-text text-muted">Official name of the school/organization.</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="subdomain">Subdomain <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="subdomain" 
                                   name="subdomain" 
                                   value="<?= old('subdomain', $tenant['subdomain']) ?>" 
                                   required
                                   pattern="[a-z0-9-]+"
                                   placeholder="nairobi-high">
                            <div class="input-group-append">
                                <span class="input-group-text">.shulelabs.com</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Lowercase, alphanumeric and dashes only.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="custom_domain">Custom Domain (Optional)</label>
                    <input type="text" 
                           class="form-control" 
                           id="custom_domain" 
                           name="custom_domain" 
                           value="<?= old('custom_domain', $tenant['custom_domain']) ?>" 
                           placeholder="school.example.com">
                    <small class="form-text text-muted">Custom domain for white-label branding.</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="pending" <?= old('status', $tenant['status']) === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= old('status', $tenant['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= old('status', $tenant['status']) === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="cancelled" <?= old('status', $tenant['status']) === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="tier">Subscription Tier</label>
                        <select class="form-control" id="tier" name="tier">
                            <option value="free" <?= old('tier', $tenant['tier']) === 'free' ? 'selected' : '' ?>>Free</option>
                            <option value="starter" <?= old('tier', $tenant['tier']) === 'starter' ? 'selected' : '' ?>>Starter</option>
                            <option value="professional" <?= old('tier', $tenant['tier']) === 'professional' ? 'selected' : '' ?>>Professional</option>
                            <option value="enterprise" <?= old('tier', $tenant['tier']) === 'enterprise' ? 'selected' : '' ?>>Enterprise</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="storage_quota_mb">Storage Quota (MB)</label>
                        <input type="number" 
                               class="form-control" 
                               id="storage_quota_mb" 
                               name="storage_quota_mb" 
                               value="<?= old('storage_quota_mb', $tenant['storage_quota_mb']) ?>" 
                               min="100">
                        <small class="form-text text-muted">Default: 5000 MB (5 GB)</small>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="student_quota">Student Quota</label>
                        <input type="number" 
                               class="form-control" 
                               id="student_quota" 
                               name="student_quota" 
                               value="<?= old('student_quota', $tenant['student_quota']) ?>" 
                               min="1"
                               placeholder="Unlimited if empty">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="staff_quota">Staff Quota</label>
                        <input type="number" 
                               class="form-control" 
                               id="staff_quota" 
                               name="staff_quota" 
                               value="<?= old('staff_quota', $tenant['staff_quota']) ?>" 
                               min="1"
                               placeholder="Unlimited if empty">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Tenant
                    </button>
                    <a href="<?= site_url('multitenant') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Additional Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tenant Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>UUID:</strong> <code><?= esc($tenant['uuid']) ?></code></p>
                    <p><strong>Storage Used:</strong> <?= number_format($tenant['storage_used_mb']) ?> MB</p>
                    <p><strong>Created:</strong> <?= esc($tenant['created_at']) ?></p>
                </div>
                <div class="col-md-6">
                    <?php if ($tenant['activated_at']): ?>
                        <p><strong>Activated:</strong> <?= esc($tenant['activated_at']) ?></p>
                    <?php endif; ?>
                    <?php if ($tenant['suspended_at']): ?>
                        <p><strong>Suspended:</strong> <?= esc($tenant['suspended_at']) ?></p>
                    <?php endif; ?>
                    <?php if ($tenant['trial_ends_at']): ?>
                        <p><strong>Trial Ends:</strong> <?= esc($tenant['trial_ends_at']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
