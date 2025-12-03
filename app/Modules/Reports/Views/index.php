<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Available Reports</h6>
                </div>
                <div class="card-body">
                    <p>Select a report to generate.</p>
                    <a href="#" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-download"></i>
                        </span>
                        <span class="text">Generate Summary</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
