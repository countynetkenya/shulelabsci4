<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                <i class="fa fa-users"></i> User Management
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#createUserModal">
                    <i class="fa fa-plus"></i> Create New User
                </button>
            </h1>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-list"></i> All Users
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($users) && !empty($users)): ?>
                                    <?php foreach ($users as $index => $u): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= esc($u['username']) ?></td>
                                            <td><?= esc($u['full_name']) ?></td>
                                            <td><?= esc($u['email']) ?></td>
                                            <td>
                                                <span class="label label-info">
                                                    <?= esc($u['role_name'] ?? 'No Role') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($u['is_active']): ?>
                                                    <span class="label label-success">Active</span>
                                                <?php else: ?>
                                                    <span class="label label-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editUserModal<?= $u['id'] ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                                <a href="/admin/users/delete/<?= $u['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                    <i class="fa fa-trash"></i> Deactivate
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/users/create" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-user-plus"></i> Create New User</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            <?php if (isset($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= esc($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>School ID (optional)</label>
                        <input type="text" name="schoolID" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
