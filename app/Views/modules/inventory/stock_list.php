<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= site_url('inventory/transfer') ?>" class="btn btn-primary">
            <i class="fas fa-exchange-alt"></i> Initiate Transfer
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stock) && is_array($stock)) : ?>
                            <?php foreach ($stock as $item) : ?>
                                <tr>
                                    <td><?= esc($item['item_name']) ?></td>
                                    <td><?= esc($item['location_name']) ?></td>
                                    <td><?= esc($item['quantity']) ?></td>
                                    <td><?= esc($item['updated_at'] ?? $item['created_at']) ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center">No stock records found.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <?= $pager->links() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
