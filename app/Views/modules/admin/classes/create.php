<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <h1>Create Class</h1>
    <form method="post" action="<?= site_url('admin/classes/store') ?>">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Grade/Level</label>
            <input type="text" name="level" class="form-control" />
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>
</div>
<?= $this->endSection() ?>