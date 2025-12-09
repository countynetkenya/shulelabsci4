<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shopping-cart text-primary"></i> POS Products
        </h1>
        <a href="<?= site_url('pos/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search/Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('pos') ?>" method="get" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <input type="text" class="form-control" name="search" placeholder="Search product name, SKU..." value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="form-group mr-3 mb-2">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat['category']) ?>" <?= ($filters['category'] ?? '') === $cat['category'] ? 'selected' : '' ?>>
                                    <?= esc($cat['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 mr-2">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?= site_url('pos') ?>" class="btn btn-secondary mb-2">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Product Catalog
                <?php if (!empty($products)): ?>
                    <span class="badge badge-info ml-2"><?= count($products) ?> products</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th class="text-right">Price</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($product['name']) ?></strong>
                                        <?php if (!empty($product['description'])): ?>
                                            <br><small class="text-muted"><?= esc($product['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($product['sku'])): ?>
                                            <code><?= esc($product['sku']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($product['category'])): ?>
                                            <span class="badge badge-secondary"><?= esc($product['category']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Uncategorized</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <strong>KES <?= number_format($product['price'], 2) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-pill badge-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>">
                                            <?= esc($product['stock']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($product['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('pos/edit/' . $product['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('pos/delete/' . $product['id']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No products found</p>
                                    <a href="<?= site_url('pos/create') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Add Your First Product
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
