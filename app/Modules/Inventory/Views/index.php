<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Inventory Module</h3>
                </div>
                <div class="card-body">
                    <p>Welcome to the Inventory Module Dashboard.</p>
                    <a href="<?= site_url('inventory/items') ?>" class="btn btn-primary">Manage Items</a>
                    <a href="<?= site_url('inventory/stock') ?>" class="btn btn-secondary">Stock Levels</a>
                    <a href="<?= site_url('inventory/transfer') ?>" class="btn btn-info">Stock Transfer</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
