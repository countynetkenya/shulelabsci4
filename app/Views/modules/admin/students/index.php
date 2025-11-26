<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <h1>Students</h1>
    <p class="text-muted">List of students</p>
    <a href="<?= site_url('admin/students/create') ?>" class="btn btn-primary">Add Student</a>
    <div class="mt-3">
        <div class="alert alert-info">This is a placeholder list view. Hook to your model/data source.</div>
    </div>
</div>
<?= $this->endSection() ?>