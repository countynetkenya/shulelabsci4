<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">LMS Module</h3>
                </div>
                <div class="card-body">
                    <p>Welcome to the Learning Management System Dashboard.</p>
                    <a href="<?= site_url('lms/courses') ?>" class="btn btn-primary">Manage Courses</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
