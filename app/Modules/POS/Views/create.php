<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add POS Product</h1>
        <a href="<?= site_url('pos') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('pos/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name') ?>" required>
                    <?php if (session('errors.name')): ?>
                        <div class="invalid-feedback"><?= session('errors.name') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>" id="price" name="price" value="<?= old('price') ?>" required>
                        <?php if (session('errors.price')): ?>
                            <div class="invalid-feedback"><?= session('errors.price') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control <?= session('errors.stock') ? 'is-invalid' : '' ?>" id="stock" name="stock" value="<?= old('stock') ?>" required>
                        <?php if (session('errors.stock')): ?>
                            <div class="invalid-feedback"><?= session('errors.stock') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= old('description') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Product</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
