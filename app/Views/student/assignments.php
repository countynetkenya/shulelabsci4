<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-tasks"></i> My Assignments</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-warning">
                <div class="panel-heading"><i class="fa fa-clock"></i> Pending Assignments</div>
                <div class="panel-body">
                    <?php if (isset($assignments) && !empty($assignments)): ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <h4><?= esc($assignment['title']) ?></h4>
                                    <p><?= esc($assignment['description'] ?? '') ?></p>
                                    <p>
                                        <strong>Due:</strong> <?= esc($assignment['due_date']) ?><br>
                                        <strong>Marks:</strong> <?= esc($assignment['total_marks']) ?>
                                    </p>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#submitModal<?= $assignment['assignmentsID'] ?? $assignment['id'] ?>">
                                        <i class="fa fa-upload"></i> Submit Assignment
                                    </button>
                                </div>
                            </div>

                            <!-- Submit Modal -->
                            <div class="modal fade" id="submitModal<?= $assignment['assignmentsID'] ?? $assignment['id'] ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="/student/assignment/submit" method="post" enctype="multipart/form-data">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="assignment_id" value="<?= $assignment['assignmentsID'] ?? $assignment['id'] ?>">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4>Submit: <?= esc($assignment['title']) ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Submission Text</label>
                                                    <textarea name="submission_text" class="form-control" rows="5"></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Upload File</label>
                                                    <input type="file" name="submission_file" class="form-control">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-upload"></i> Submit
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No pending assignments</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel panel-success">
                <div class="panel-heading"><i class="fa fa-check"></i> Submitted Assignments</div>
                <div class="panel-body">
                    <?php if (isset($submitted_assignments) && !empty($submitted_assignments)): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submitted_assignments as $sub): ?>
                                    <tr>
                                        <td><?= esc($sub['title']) ?></td>
                                        <td><?= esc($sub['submitted_at']) ?></td>
                                        <td><span class="label label-success">Submitted</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No submissions yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
