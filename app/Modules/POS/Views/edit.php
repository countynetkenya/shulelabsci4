<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-info"></i> Edit POS Product
        </h1>
        <a href="<?= site_url('pos') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to POS
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('pos/update/' . $product['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" value="<?= old('name', $product['name']) ?>" 
                                   placeholder="Enter product name" required>
                            <?php if (session('errors.name')): ?>
                                <div class="invalid-feedback"><?= session('errors.name') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" class="form-control <?= session('errors.category') ? 'is-invalid' : '' ?>" 
                                   id="category" name="category" value="<?= old('category', $product['category'] ?? '') ?>" 
                                   placeholder="e.g., Uniforms, Books"
                                   list="categoryList">
                            <datalist id="categoryList">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= esc($cat['category']) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </datalist>
                            <?php if (session('errors.category')): ?>
                                <div class="invalid-feedback"><?= session('errors.category') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="price">Price (KES) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>" 
                                   id="price" name="price" value="<?= old('price', $product['price']) ?>" 
                                   placeholder="0.00" required>
                            <?php if (session('errors.price')): ?>
                                <div class="invalid-feedback"><?= session('errors.price') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" min="0" class="form-control <?= session('errors.stock') ? 'is-invalid' : '' ?>" 
                                   id="stock" name="stock" value="<?= old('stock', $product['stock']) ?>" 
                                   placeholder="0">
                            <?php if (session('errors.stock')): ?>
                                <div class="invalid-feedback"><?= session('errors.stock') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" class="form-control <?= session('errors.sku') ? 'is-invalid' : '' ?>" 
                                   id="sku" name="sku" value="<?= old('sku', $product['sku'] ?? '') ?>" 
                                   placeholder="Stock Keeping Unit">
                            <?php if (session('errors.sku')): ?>
                                <div class="invalid-feedback"><?= session('errors.sku') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="barcode">Barcode</label>
                    <input type="text" class="form-control <?= session('errors.barcode') ? 'is-invalid' : '' ?>" 
                           id="barcode" name="barcode" value="<?= old('barcode', $product['barcode'] ?? '') ?>" 
                           placeholder="Enter barcode number">
                    <?php if (session('errors.barcode')): ?>
                        <div class="invalid-feedback"><?= session('errors.barcode') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" 
                              id="description" name="description" rows="3" 
                              placeholder="Optional product description"><?= old('description', $product['description'] ?? '') ?></textarea>
                    <?php if (session('errors.description')): ?>
                        <div class="invalid-feedback"><?= session('errors.description') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="<?= site_url('pos') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
