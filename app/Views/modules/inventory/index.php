<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Inventory Items</h1>
        <a href="<?= site_url('inventory/items/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Item
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td><?= esc($item->name) ?></td>
                                <td><?= esc($item->category_name) ?></td>
                                <td><?= esc($item->sku) ?></td>
                                <td>
                                    <span class="badge badge-<?= $item->type == 'asset' ? 'info' : 'secondary' ?>">
                                        <?= esc(ucfirst($item->type)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $item->quantity <= $item->reorder_level ? 'text-danger font-weight-bold' : '' ?>">
                                        <?= esc($item->quantity) ?>
                                    </span>
                                </td>
                                <td><?= number_format($item->unit_cost, 2) ?></td>
                                <td><?= esc($item->location) ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info">View</a>
                                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
