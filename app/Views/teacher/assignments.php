<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header">
        <i class="fa fa-tasks"></i> Assignments
        <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#createAssignmentModal">
            <i class="fa fa-plus"></i> Create Assignment
        </button>
    </h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> All Assignments</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Due Date</th>
                        <th>Total Marks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($assignments) && !empty($assignments)): ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= esc($assignment['title']) ?></td>
                                <td>Class #<?= esc($assignment['class_id']) ?></td>
                                <td><?= esc($assignment['due_date']) ?></td>
                                <td><?= esc($assignment['total_marks']) ?></td>
                                <td>
                                    <?php if ($assignment['status'] === 'active'): ?>
                                        <span class="label label-success">Active</span>
                                    <?php else: ?>
                                        <span class="label label-default">Closed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No assignments created yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/teacher/assignment/create" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus"></i> Create New Assignment</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Class *</label>
                        <select name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php if (isset($classes)): ?>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['classesID'] ?? $class['id'] ?>">
                                        <?= esc($class['class_name'] ?? $class['classes']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Due Date *</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Total Marks *</label>
                        <input type="number" name="total_marks" class="form-control" value="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
