<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="fas fa-book-reader"></i> Edit Course</h1>
    <div class="card shadow">
        <div class="card-body">
            <form action="<?= site_url('learning/courses/' . $course['id'] . '/update') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group"><label>Title *</label><input type="text" class="form-control" name="title" value="<?= esc($course['title']) ?>" required></div>
                <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="4"><?= esc($course['description']) ?></textarea></div>
                <div class="form-group"><label>Status</label>
                    <select class="form-control" name="status">
                        <option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $course['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= $course['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Course</button>
                    <a href="<?= site_url('learning/courses') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
