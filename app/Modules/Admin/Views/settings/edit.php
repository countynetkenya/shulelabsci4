<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog text-primary"></i> Edit Setting
        </h1>
        <a href="<?= site_url('admin/settings') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Settings
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
            <h6 class="m-0 font-weight-bold text-primary">Setting Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admin/settings/update/' . $setting['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="class">Category (Class) <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="class" 
                           name="class" 
                           value="<?= old('class', $setting['class']) ?>" 
                           required
                           placeholder="e.g., mail, payment, general"
                           list="classList">
                    <datalist id="classList">
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?= esc($cls) ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                    <small class="form-text text-muted">Group or category for this setting.</small>
                </div>

                <div class="form-group">
                    <label for="key">Key <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="key" 
                           name="key" 
                           value="<?= old('key', $setting['key']) ?>" 
                           required
                           placeholder="e.g., smtp_host, tax_rate">
                    <small class="form-text text-muted">Unique identifier within the category.</small>
                </div>

                <div class="form-group">
                    <label for="value">Value</label>
                    <textarea class="form-control" 
                              id="value" 
                              name="value" 
                              rows="3"
                              placeholder="Setting value..."><?= old('value', $setting['value']) ?></textarea>
                    <small class="form-text text-muted">The configuration value.</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="string" <?= old('type', $setting['type'] ?? 'string') === 'string' ? 'selected' : '' ?>>String</option>
                            <option value="boolean" <?= old('type', $setting['type'] ?? 'string') === 'boolean' ? 'selected' : '' ?>>Boolean</option>
                            <option value="integer" <?= old('type', $setting['type'] ?? 'string') === 'integer' ? 'selected' : '' ?>>Integer</option>
                            <option value="json" <?= old('type', $setting['type'] ?? 'string') === 'json' ? 'selected' : '' ?>>JSON</option>
                        </select>
                        <small class="form-text text-muted">Data type of the value.</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="context">Context</label>
                        <select class="form-control" id="context" name="context">
                            <option value="app" <?= old('context', $setting['context'] ?? 'app') === 'app' ? 'selected' : '' ?>>App</option>
                            <option value="user" <?= old('context', $setting['context'] ?? 'app') === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="system" <?= old('context', $setting['context'] ?? 'app') === 'system' ? 'selected' : '' ?>>System</option>
                        </select>
                        <small class="form-text text-muted">Scope of the setting.</small>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Setting
                    </button>
                    <a href="<?= site_url('admin/settings') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
