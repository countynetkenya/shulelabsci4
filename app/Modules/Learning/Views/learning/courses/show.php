<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($course['title']) ?></h1>
        <div>
            <a href="/learning/courses/<?= $course['id'] ?>/lessons/create" class="btn btn-primary">Add Lesson</a>
            <a href="/learning/courses" class="btn btn-secondary">Back to Courses</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong><br> <?= esc($course['description']) ?></p>
                    <p><strong>Status:</strong> <span class="badge badge-<?= $course['status'] == 'published' ? 'success' : 'warning' ?>"><?= ucfirst($course['status']) ?></span></p>
                    <p><strong>Created:</strong> <?= date('M d, Y', strtotime($course['created_at'])) ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lessons</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($lessons)): ?>
                        <p class="text-center text-muted">No lessons added yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($lessons as $lesson): ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?= esc($lesson['sequence_order']) ?>. <?= esc($lesson['title']) ?></h5>
                                        <small><?= !empty($lesson['video_url']) ? '<i class="fas fa-video"></i>' : '' ?></small>
                                    </div>
                                    <p class="mb-1"><?= character_limiter(strip_tags($lesson['content']), 100) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
