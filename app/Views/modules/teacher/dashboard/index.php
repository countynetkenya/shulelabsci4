<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <h1>Teacher Dashboard</h1>
    <ul>
        <li><a href="<?= site_url('teacher/classes') ?>">Manage Classes</a></li>
        <li><a href="<?= site_url('teacher/gradebook') ?>">Gradebook</a></li>
        <li><a href="<?= site_url('teacher/attendance') ?>">Attendance</a></li>
    </ul>
</div>
<?= $this->endSection() ?>