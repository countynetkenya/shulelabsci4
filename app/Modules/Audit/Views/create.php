<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-history"></i> Create Manual Audit Entry
        </h1>
        <a href="<?= base_url('audit') ?>" class="btn btn-secondary btn-sm">
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
            <h6 class="m-0 font-weight-bold text-primary">Audit Event Details</h6>
        </div>
        <div class="card-body">
            <form method="post" action="<?= base_url('audit/store') ?>">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="event_type">Event Type <span class="text-danger">*</span></label>
                            <select name="event_type" id="event_type" class="form-control" required>
                                <option value="">Select Event Type</option>
                                <?php foreach ($eventTypes as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= old('event_type') === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(esc($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="entity_type">Entity Type</label>
                            <select name="entity_type" id="entity_type" class="form-control">
                                <option value="">None</option>
                                <?php foreach ($entityTypes as $type): ?>
                                    <option value="<?= esc($type) ?>" <?= old('entity_type') === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(esc($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="entity_id">Entity ID</label>
                            <input type="number" name="entity_id" id="entity_id" class="form-control" 
                                   value="<?= old('entity_id') ?>" placeholder="e.g., 123">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ip_address">IP Address</label>
                            <input type="text" name="ip_address" id="ip_address" class="form-control" 
                                   value="<?= old('ip_address') ?>" placeholder="Will auto-detect if empty">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Audit Entry
                    </button>
                    <a href="<?= base_url('audit') ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
