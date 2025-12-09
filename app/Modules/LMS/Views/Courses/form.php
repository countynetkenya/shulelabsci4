<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-book"></i> <?= isset($course) ? 'Edit Course' : 'Add Course' ?></h3>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fa fa-laptop"></i> Dashboard</a></li>
            <li><a href="<?= base_url('learning/courses') ?>">Courses</a></li>
            <li class="active"><?= isset($course) ? 'Edit' : 'Add' ?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form class="form-horizontal" role="form" method="post" action="<?= isset($course) ? base_url('learning/courses/' . $course['id']) : base_url('learning/courses') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="form-group <?= session('errors.title') ? 'has-error' : '' ?>">
                        <label for="title" class="col-sm-2 control-label">
                            Title <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="title" name="title" value="<?= old('title', $course['title'] ?? '') ?>" >
                            <span class="help-block"><?= session('errors.title') ?></span>
                        </div>
                    </div>

                    <div class="form-group <?= session('errors.description') ? 'has-error' : '' ?>">
                        <label for="description" class="col-sm-2 control-label">
                            Description
                        </label>
                        <div class="col-sm-6">
                            <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $course['description'] ?? '') ?></textarea>
                            <span class="help-block"><?= session('errors.description') ?></span>
                        </div>
                    </div>

                    <div class="form-group <?= session('errors.status') ? 'has-error' : '' ?>">
                        <label for="status" class="col-sm-2 control-label">
                            Status <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $status = old('status', $course['status'] ?? 'draft');
$options = ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'];
?>
                            <select name="status" id="status" class="form-control">
                                <?php foreach ($options as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $status == $key ? 'selected' : '' ?>><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-block"><?= session('errors.status') ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?= isset($course) ? 'Update Course' : 'Add Course' ?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
