<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle text-success"></i> Add Library Book
        </h1>
        <a href="<?= site_url('library') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Library
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
            <h6 class="m-0 font-weight-bold text-primary">Book Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('library/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>" 
                                   id="title" name="title" value="<?= old('title') ?>" 
                                   placeholder="Enter book title" required>
                            <?php if (session('errors.title')): ?>
                                <div class="invalid-feedback"><?= session('errors.title') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" class="form-control <?= session('errors.isbn') ? 'is-invalid' : '' ?>" 
                                   id="isbn" name="isbn" value="<?= old('isbn') ?>" 
                                   placeholder="e.g., 978-3-16-148410-0">
                            <?php if (session('errors.isbn')): ?>
                                <div class="invalid-feedback"><?= session('errors.isbn') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="author">Author <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.author') ? 'is-invalid' : '' ?>" 
                                   id="author" name="author" value="<?= old('author') ?>" 
                                   placeholder="Enter author name" required>
                            <?php if (session('errors.author')): ?>
                                <div class="invalid-feedback"><?= session('errors.author') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" class="form-control <?= session('errors.category') ? 'is-invalid' : '' ?>" 
                                   id="category" name="category" value="<?= old('category') ?>" 
                                   placeholder="e.g., Fiction, Science, History"
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
                            <label for="total_copies">Number of Copies</label>
                            <input type="number" class="form-control <?= session('errors.total_copies') ? 'is-invalid' : '' ?>" 
                                   id="total_copies" name="total_copies" value="<?= old('total_copies', 1) ?>" 
                                   min="1" placeholder="1">
                            <?php if (session('errors.total_copies')): ?>
                                <div class="invalid-feedback"><?= session('errors.total_copies') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">How many copies of this book?</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Book
                    </button>
                    <a href="<?= site_url('library') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
