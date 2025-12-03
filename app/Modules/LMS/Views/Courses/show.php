<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-book"></i> <?= esc($course['title']) ?></h3>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fa fa-laptop"></i> Dashboard</a></li>
            <li><a href="<?= base_url('learning/courses') ?>">Courses</a></li>
            <li class="active">View</li>
        </ol>
    </div><!-- /.box-header -->
    
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="well">
                    <p><strong>Status:</strong> <?= ucfirst($course['status']) ?></p>
                    <p><strong>Description:</strong> <?= esc($course['description']) ?></p>
                    <a href="<?= base_url('learning/courses/' . $course['id'] . '/edit') ?>" class="btn btn-warning btn-sm">Edit Course</a>
                </div>

                <h4 class="page-header">
                    Lessons
                    <a href="<?= base_url('learning/courses/' . $course['id'] . '/lessons/new') ?>" class="btn btn-success btn-xs pull-right">
                        <i class="fa fa-plus"></i> Add Lesson
                    </a>
                </h4>

                <div id="hide-table">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Sequence</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lessons)): ?>
                                <?php foreach ($lessons as $i => $lesson): ?>
                                    <tr>
                                        <td data-title="#"><?= $i + 1 ?></td>
                                        <td data-title="Title"><?= esc($lesson['title']) ?></td>
                                        <td data-title="Sequence"><?= $lesson['sequence_order'] ?></td>
                                        <td data-title="Action">
                                            <a href="<?= base_url('learning/lessons/' . $lesson['id'] . '/edit') ?>" class="btn btn-warning btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No lessons found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
