<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="fas fa-chalkboard-teacher"></i> Edit Teacher</h1>
    <div class="card shadow">
        <div class="card-body">
            <form action="<?= site_url('teachers/update/' . $teacher['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="row"><div class="col-md-6"><div class="form-group"><label>First Name *</label><input type="text" class="form-control" name="first_name" value="<?= esc($teacher['first_name']) ?>" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Last Name *</label><input type="text" class="form-control" name="last_name" value="<?= esc($teacher['last_name']) ?>" required></div></div></div>
                <div class="row"><div class="col-md-6"><div class="form-group"><label>Employee ID</label><input type="text" class="form-control" name="employee_id" value="<?= esc($teacher['employee_id']) ?>"></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Department</label><input type="text" class="form-control" name="department" value="<?= esc($teacher['department']) ?>"></div></div></div>
                <div class="row"><div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" value="<?= esc($teacher['email']) ?>"></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Phone</label><input type="text" class="form-control" name="phone" value="<?= esc($teacher['phone']) ?>"></div></div></div>
                <div class="form-group mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Teacher</button>
                    <a href="<?= site_url('teachers') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a></div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
