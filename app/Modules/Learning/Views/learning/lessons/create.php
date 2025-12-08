<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Lesson to: <?= esc($course['title']) ?></h1>
        <a href="/learning/courses/<?= $course['id'] ?>" class="btn btn-secondary">Back</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="/learning/courses/<?= $course['id'] ?>/lessons" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="title">Lesson Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="content">Content (HTML/Markdown)</label>
                    <textarea class="form-control" id="content" name="content" rows="5"></textarea>
                </div>

                <div class="form-group">
                    <label for="video_url">Video URL (Optional)</label>
                    <input type="url" class="form-control" id="video_url" name="video_url">
                </div>

                <div class="form-group">
                    <label for="sequence_order">Sequence Order</label>
                    <input type="number" class="form-control" id="sequence_order" name="sequence_order" value="0">
                </div>

                <button type="submit" class="btn btn-primary">Add Lesson</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
