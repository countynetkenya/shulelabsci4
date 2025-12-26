<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-plug"></i> Edit Integration
        </h1>
        <a href="<?= base_url('integrations') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Integration Configuration</h6>
        </div>
        <div class="card-body">
            <form method="post" action="<?= base_url('integrations/update/' . $integration['id']) ?>">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Integration Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="<?= old('name', $integration['name']) ?>" required>
                            <small class="form-text text-muted">Unique identifier for this integration.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Integration Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="">Select Type</option>
                                <?php foreach ($types as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" 
                                            <?= old('type', $integration['type']) === $value ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="adapter_class">Adapter Class <span class="text-danger">*</span></label>
                            <input type="text" name="adapter_class" id="adapter_class" class="form-control" 
                                   value="<?= old('adapter_class', $integration['adapter_class']) ?>" required>
                            <small class="form-text text-muted">Fully qualified class name of the adapter.</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1" <?= old('is_active', $integration['is_active']) == '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= old('is_active', $integration['is_active']) == '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="config_json">Configuration (JSON) <span class="text-danger">*</span></label>
                    <textarea name="config_json" id="config_json" class="form-control" rows="8"><?php
                        $configValue = old('config_json', $integration['config_json'] ?? '{}');
// Pretty print if it's valid JSON
$decoded = json_decode($configValue, true);
if ($decoded !== null) {
    echo esc(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
} else {
    echo esc($configValue);
}
?></textarea>
                    <small class="form-text text-muted">Integration-specific configuration in JSON format. Credentials will be encrypted.</small>
                </div>

                <div class="alert alert-warning">
                    <strong><i class="fa fa-exclamation-triangle"></i> Security Note:</strong>
                    Sensitive credentials in the configuration will be encrypted before storage.
                </div>

                <div class="alert alert-info">
                    <strong><i class="fa fa-info-circle"></i> Created:</strong>
                    <?= date('Y-m-d H:i:s', strtotime($integration['created_at'])) ?>
                    <?php if (!empty($integration['updated_at'])): ?>
                        <br>
                        <strong>Last Updated:</strong> <?= date('Y-m-d H:i:s', strtotime($integration['updated_at'])) ?>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Integration
                    </button>
                    <a href="<?= base_url('integrations') ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
