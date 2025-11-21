<?php if($lesson->video_url == '') { ?>
    <h3><?=$lesson->title?> <?=($lesson->duration != '') ? ' - '.$lesson->duration : ''?></h3>
<?php } ?>

<?php if($lesson->video_url != '') { ?>
    <div class="embed-responsive embed-responsive-16by9">
        <?php echo htmlspecialchars_decode($lesson->video_url); ?>
    </div>
<?php } ?>
<?php if($lesson->video_file != '') { ?>
    <div class="embed-responsive embed-responsive-16by9" style="height: 450px !important;">
        <video src="<?=base_url('uploads/videos/'.$lesson->video_file);?>" controls autoplay width='380px' height='120px' ></video>
    </div>
<?php } ?>

<?php if($lesson->file != '') { ?>
    <a href="<?=base_url('tutorial/lessondownload/'.$lesson->lesson_id)?>" class="btn btn-success"><?=$this->lang->line('tutorial_download')?></a>
<?php } ?>

<?php if($lesson->description != '') { ?>
    <p style="margin-top: 10px"><?=$lesson->description?></p>
<?php } ?>
