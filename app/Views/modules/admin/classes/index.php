<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <h1>Classes</h1>
    <p class="text-muted">List of classes</p>
    <a href="<?= site_url('admin/classes/create') ?>" class="btn btn-primary">Add Class</a>
    <div class="mt-3">
        <div class="alert alert-info">Placeholder list view. Connect to data.</div>
    </div>
</div>
<?= $this->endSection() ?>