<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <h1>Student Dashboard</h1>
    <ul>
        <li><a href="<?= site_url('student/assignments') ?>">Assignments</a></li>
        <li><a href="<?= site_url('student/grades') ?>">Grades</a></li>
        <li><a href="<?= site_url('student/library') ?>">Library</a></li>
        <li><a href="<?= site_url('student/attendance') ?>">Attendance</a></li>
    </ul>
</div>
<?= $this->endSection() ?>