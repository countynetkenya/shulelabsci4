<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Select School</h3>
                </div>
                
                <div class="box-body">
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>
                    
                    <p>You have access to multiple schools. Please select which school you want to access:</p>
                    
                    <form method="post" action="<?= base_url('school/select') ?>">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <?php if (!empty($schools)) : ?>
                                <?php foreach ($schools as $school) : ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="school-card" style="border: 2px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; cursor: pointer;" onclick="selectSchool(<?= $school->schoolID ?>)">
                                            <h4><?= esc($school->sname) ?></h4>
                                            <p class="text-muted"><?= esc($school->address ?? '') ?></p>
                                            <button type="submit" name="schoolID" value="<?= $school->schoolID ?>" class="btn btn-primary">
                                                Select
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="col-md-12">
                                    <p class="text-danger">No schools found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectSchool(schoolID) {
    // Optional: Add visual feedback when card is clicked
}
</script>
<?= $this->endSection() ?>
