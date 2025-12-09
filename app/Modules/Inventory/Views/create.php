<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Inventory Item</h1>
        <a href="<?= site_url('inventory') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <form action="<?= site_url('inventory/store') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?= old('sku') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="physical" <?= old('type') == 'physical' ? 'selected' : '' ?>>Physical</option>
                                <option value="service" <?= old('type') == 'service' ? 'selected' : '' ?>>Service</option>
                                <option value="bundle" <?= old('type') == 'bundle' ? 'selected' : '' ?>>Bundle</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Unit Cost</label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control" value="<?= old('unit_cost') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Reorder Level</label>
                            <input type="number" name="reorder_level" class="form-control" value="<?= old('reorder_level', 10) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Item</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
