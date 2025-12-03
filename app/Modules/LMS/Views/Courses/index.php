<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-book"></i> Courses</h3>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fa fa-laptop"></i> Dashboard</a></li>
            <li class="active">Courses</li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="page-header">
                    <a href="<?= base_url('learning/courses/new') ?>">
                        <i class="fa fa-plus"></i>
                        Add a Course
                    </a>
                </h5>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($courses)): ?>
                                <?php foreach ($courses as $i => $course): ?>
                                    <tr>
                                        <td data-title="#"><?= $i + 1 ?></td>
                                        <td data-title="Title"><?= esc($course['title']) ?></td>
                                        <td data-title="Status">
                                            <?php if ($course['status'] == 'published'): ?>
                                                <span class="label label-success">Published</span>
                                            <?php elseif ($course['status'] == 'draft'): ?>
                                                <span class="label label-warning">Draft</span>
                                            <?php else: ?>
                                                <span class="label label-default">Archived</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-title="Created At"><?= date('d M Y', strtotime($course['created_at'])) ?></td>
                                        <td data-title="Action">
                                            <a href="<?= base_url('learning/courses/' . $course['id']) ?>" class="btn btn-primary btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye"></i></a>
                                            <a href="<?= base_url('learning/courses/' . $course['id'] . '/edit') ?>" class="btn btn-warning btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div> <!-- col-sm-12 -->
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->
<?= $this->endSection() ?>
