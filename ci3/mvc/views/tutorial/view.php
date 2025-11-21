<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-leanpub"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("tutorial/index")?>"></i> <?=$this->lang->line('menu_tutorial')?></a></li>
            <li class="active"><?=$this->lang->line('menu_view')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
        	<div class="col-lg-12">
                <?php if(customCompute($lessons)) { ?>
                    <div class="item__play__box">
                        <div class="item__header" id="mainItem">
                            <?php foreach ($lessons as $lesson) { ?>
                                <?php if($lesson->video_url == '') { ?>
                                    <h3><?=$lesson->title?> <?=($lesson->duration != '') ? ' - '.$lesson->duration : ''?></h3>
                                <?php } ?>

                                <?php if($lesson->video_url != '') { ?>
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <?php echo htmlspecialchars_decode($lesson->video_url); ?>
                                    </div>
                                <?php } ?>

                                <?php if($lesson->file != '') { ?>
                                    <a href="<?=base_url('tutorial/lessondownload/'.$lesson->lesson_id)?>" class="btn btn-success"><?=$this->lang->line('tutorial_download')?></a>
                                <?php } ?>

                                <?php if($lesson->description != '') { ?>
                                    <p style="margin-top: 10px"><?=$lesson->description?></p>
                                <?php } ?>

                                <?php break; ?>
                            <?php } ?>
                        </div>

                        <ul class="item__playlist">
                            <?php $i = 0; foreach ($lessons as $lesson) { $i++; ?>
                                <li class="item__playlist_item <?=$i==1 ? 'active' : '' ?>" data-lessonid="<?=$lesson->lesson_id?>">
                                    <div class="playlist__item">
                                        <div class="item__sl"><?=$i?>.</div>
                                        <div class="item__title"><?=$lesson->title?></div>
                                        <div class="item__length"><?=$lesson->duration?></div>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } else {
                    echo "<h3>".$this->lang->line('tutorial_data_not_found')."<h3>";
                } ?>
        	</div>
        </div>
    </div>
</div>

<script>
    const LESSONURL = "<?=base_url('tutorial/getlesson')?>";
</script>