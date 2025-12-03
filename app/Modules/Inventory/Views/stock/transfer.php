<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= site_url('inventory/stock') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Stock
        </a>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif ?>
    
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert alert-danger">
            <ul>
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('inventory/transfer/process') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group mb-3">
                    <label for="item_id">Item</label>
                    <select name="item_id" id="item_id" class="form-control" required>
                        <option value="">Select Item</option>
                        <?php foreach ($items as $item) : ?>
                            <option value="<?= $item->id ?>" <?= old('item_id') == $item->id ? 'selected' : '' ?>>
                                <?= esc($item->name) ?> (SKU: <?= esc($item->sku) ?>)
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="from_location_id">From Location</label>
                            <select name="from_location_id" id="from_location_id" class="form-control" required>
                                <option value="">Select Source Location</option>
                                <?php foreach ($locations as $location) : ?>
                                    <option value="<?= $location['id'] ?>" <?= old('from_location_id') == $location['id'] ? 'selected' : '' ?>>
                                        <?= esc($location['name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="to_location_id">To Location</label>
                            <select name="to_location_id" id="to_location_id" class="form-control" required>
                                <option value="">Select Destination Location</option>
                                <?php foreach ($locations as $location) : ?>
                                    <option value="<?= $location['id'] ?>" <?= old('to_location_id') == $location['id'] ? 'selected' : '' ?>>
                                        <?= esc($location['name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="quantity">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="<?= old('quantity') ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Initiate Transfer</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
