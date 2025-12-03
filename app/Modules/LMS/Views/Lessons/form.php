<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> <?= isset($lesson) ? 'Edit Lesson' : 'Add Lesson' ?></h3>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fa fa-laptop"></i> Dashboard</a></li>
            <li><a href="<?= base_url('learning/courses') ?>">Courses</a></li>
            <li><a href="<?= base_url('learning/courses/' . $course['id']) ?>"><?= esc($course['title']) ?></a></li>
            <li class="active"><?= isset($lesson) ? 'Edit Lesson' : 'Add Lesson' ?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form class="form-horizontal" role="form" method="post" action="<?= isset($lesson) ? base_url('learning/lessons/' . $lesson['id']) : base_url('learning/courses/' . $course['id'] . '/lessons') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="form-group <?= session('errors.title') ? 'has-error' : '' ?>">
                        <label for="title" class="col-sm-2 control-label">
                            Title <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="title" name="title" value="<?= old('title', $lesson['title'] ?? '') ?>" >
                            <span class="help-block"><?= session('errors.title') ?></span>
                        </div>
                    </div>

                    <div class="form-group <?= session('errors.sequence_order') ? 'has-error' : '' ?>">
                        <label for="sequence_order" class="col-sm-2 control-label">
                            Sequence Order <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" id="sequence_order" name="sequence_order" value="<?= old('sequence_order', $lesson['sequence_order'] ?? 0) ?>" >
                            <span class="help-block"><?= session('errors.sequence_order') ?></span>
                        </div>
                    </div>

                    <div class="form-group <?= session('errors.video_url') ? 'has-error' : '' ?>">
                        <label for="video_url" class="col-sm-2 control-label">
                            Video URL
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="video_url" name="video_url" value="<?= old('video_url', $lesson['video_url'] ?? '') ?>" placeholder="https://youtube.com/..." >
                            <span class="help-block"><?= session('errors.video_url') ?></span>
                        </div>
                    </div>

                    <div class="form-group <?= session('errors.content') ? 'has-error' : '' ?>">
                        <label for="content" class="col-sm-2 control-label">
                            Content
                        </label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="content" name="content" rows="10"><?= old('content', $lesson['content'] ?? '') ?></textarea>
                            <span class="help-block"><?= session('errors.content') ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?= isset($lesson) ? 'Update Lesson' : 'Add Lesson' ?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
