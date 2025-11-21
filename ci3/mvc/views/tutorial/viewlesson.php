<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-leanpub"></i> <?=$this->lang->line('tutorial_lesson')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("tutorial/index")?>"><?=$this->lang->line('menu_tutorial')?></a></li>
                    <li><a href="<?=base_url("tutorial/addlesson/".$lesson->tutorial_id)?>"><?=$this->lang->line('menu_lesson')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_view')?></li>
                </ol>
            </div><!-- /.box-header -->
        </div>
        <!-- form start -->

        <?php if($lesson->file != '' && $lesson->video_url == '') { ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-body">
                            <h3><?=$lesson->title?> <?=($lesson->duration != '') ? ' - '.$lesson->duration : ''?></h3>
                            <a href="<?=base_url('tutorial/lessondownload/'.$lesson->lesson_id)?>" class="btn btn-success"><?=$this->lang->line('tutorial_download')?></a>
                            <p style="margin-top: 10px"><?=$lesson->description?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="row">
                <?php if($lesson->video_url != '') { ?>
                    <div class="col-sm-8">
                        <div class="box">
                            <div class="box-body">
                                <div class="embed-responsive embed-responsive-16by9">
                                    <?=htmlspecialchars_decode($lesson->video_url)?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }elseif ($lesson->video_file != '') {?>
                    <div class="col-sm-6">
                        <div class="box">
                            <div class="box-body">
                                <video src="<?=base_url('uploads/videos/'.$lesson->video_file);?>" controls autoplay width='380px' height='320px' ></video>
                            </div>
                        </div>
                    </div>
                <?php }?>
                <div class="<?=$lesson->video_url == '' ? 'col-sm-12' : 'col-sm-4'?>">
                    <div class="box">
                        <div class="box-body">
                            <h3><?=$lesson->title?> <?=($lesson->duration != '') ? ' - '.$lesson->duration : ''?></h3>
                            <?php if($lesson->file != '') { ?>
                                <a href="<?=base_url('tutorial/lessondownload/'.$lesson->lesson_id)?>" class="btn btn-success"><?=$this->lang->line('tutorial_download')?></a>
                            <?php } ?>
                            <p style="margin-top: 10px"><?=$lesson->description?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>
</div>
