<div class="row">
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-plus-square-o"></i> <?=$this->lang->line('add')?></h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
                <form role="form" method="POST" enctype="multipart/form-data">
                    <div class="form-group <?=form_error('title') ? 'has-error' : '' ?>">
                        <label for="title"><?=$this->lang->line('tutorial_title')?> <span class="text-red">*</span></label>
                        <input type="text" name="title" class="form-control" id="title" value="<?=set_value('title')?>">
                        <span class="text-red">
                            <?=form_error('title')?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('lesson_provider') ? 'has-error' : '' ?>">
                        <label for="lesson_provider"><?=$this->lang->line('tutorial_lesson_provider')?> </label>
                        <?php
                        $lessonProviderArray = array(
                            '0'  => $this->lang->line('tutorial_select_lesson_provider'),
                            '5'  => $this->lang->line('tutorial_youtube'),
                            '10' => $this->lang->line('tutorial_vimeo'),
                            '15' => $this->lang->line('tutorial_video_file'),
                        );
                        echo form_dropdown("lesson_provider", $lessonProviderArray, set_value("lesson_provider"), "id='lesson_provider' class='form-control'");
                        ?>
                        <span class="text-red">
                            <?=form_error('lesson_provider')?>
                        </span>
                    </div>
                    <div class="form-group <?=form_error('video_file') ? 'has-error' : '' ?>" id="video_file_div">
                        <label for="video_file"><?=$this->lang->line('tutorial_video_file')?></label>
                        <div class="input-group video-preview">
                            <input type="text" class="form-control video-preview-filename" disabled="disabled">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default video-preview-clear" style="display:none;">
                                    <span class="fa fa-remove"></span>
                                    <?=$this->lang->line('tutorial_clear')?>
                                </button>
                                <div class="btn btn-success image-preview-input video-preview-input">
                                    <span class="fa fa-repeat"></span>
                                    <span class="video-preview-input-title">
                                    <?=$this->lang->line('tutorial_file_browse')?></span>
                                    <input type="file" id="video_file" name="video_file"/>
                                </div>
                            </span>
                        </div>
                    </div>


                    <div class="form-group <?=form_error('video_url') ? 'has-error' : '' ?>" id="video_url_div">
                        <label for="video_url"><?=$this->lang->line('tutorial_video_url')?> (<?=$this->lang->line('tutorial_embed_link')?>) </label>
                        <input type="text" name="video_url" class="form-control" id="video_url" value="<?=set_value('video_url')?>">
                        <span class="text-red">
                            <?=form_error('video_url')?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('duration') ? 'has-error' : '' ?>">
                        <label for="duration"><?=$this->lang->line('tutorial_duration')?></label>
                        <input type="text" name="duration" class="form-control" id="duration" value="<?=set_value('duration')?>">
                        <span class="text-red">
                            <?=form_error('duration')?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('file') ? 'has-error' : '' ?>">
                        <label for="file"><?=$this->lang->line('tutorial_file')?></label>
                        <div class="input-group image-preview">
                            <input type="text" class="form-control image-preview-filename" disabled="disabled">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                    <span class="fa fa-remove"></span>
                                    <?=$this->lang->line('tutorial_clear')?>
                                </button>
                                <div class="btn btn-success image-preview-input" id="image-preview-input">
                                    <span class="fa fa-repeat"></span>
                                    <span class="image-preview-input-title">
                                    <?=$this->lang->line('tutorial_file_browse')?></span>
                                    <input type="file" id="file" name="file"/>
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="form-group <?=form_error('description') ? 'has-error' : '' ?>">
                        <label for="description"><?=$this->lang->line('tutorial_description')?></label>
                        <textarea class="form-control" name="description" id="description" cols="30" rows="5"><?=set_value('description')?></textarea>
                        <span class="text-red">
                            <?=form_error('description')?>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-success"><?=$this->lang->line('add')?></button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-table"></i> <?=$this->lang->line('tutorial_list')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("tutorial/index")?>"><?=$this->lang->line('menu_tutorial')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_lesson')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="col-sm-1">#</th>
                            <th class="col-sm-6"><?=$this->lang->line('tutorial_title')?></th>
                            <th class="col-sm-2"><?=$this->lang->line('tutorial_lesson_provider')?></th>
                            <th class="col-sm-1"><?=$this->lang->line('tutorial_duration')?></th>
                            <?php if(permissionChecker('tutorial_edit') || permissionChecker('tutorial_delete') || permissionChecker('tutorial_view')) { ?>
                                <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        unset($lessonProviderArray[0]);
                        if(customCompute($lessons)) { $i = 1; foreach($lessons as $lesson) { ?>
                            <tr>
                                <td data-title="#">
                                    <?=$i?>
                                </td>
                                <td data-title="<?=$this->lang->line('tutorial_title')?>">
                                    <?=namesorting($lesson->title, 55) ?>
                                </td>

                                <td data-title="<?=$this->lang->line('tutorial_lesson_provider')?>">
                                    <?=isset($lessonProviderArray[$lesson->lesson_provider]) ? $lessonProviderArray[$lesson->lesson_provider] : '' ?>
                                </td>

                                <td data-title="<?=$this->lang->line('tutorial_duration')?>">
                                    <?=$lesson->duration ?>
                                </td>

                                <?php if(permissionChecker('tutorial_edit') || permissionChecker('tutorial_delete') || permissionChecker('tutorial_view')) { ?>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                        <?php
                                        if(permissionChecker('tutorial_view')) {
                                            echo btn_view_show('tutorial/viewlesson/'.$lesson->lesson_id, $this->lang->line('view'));
                                        }
                                        if(permissionChecker('tutorial_edit')) {
                                            echo btn_edit_show('tutorial/editlesson/'.$lesson->tutorial_id.'/'.$lesson->lesson_id, $this->lang->line('edit'));
                                        }
                                        if(permissionChecker('tutorial_delete')) {
                                            echo btn_delete_show('tutorial/deletelesson/'.$lesson->lesson_id, $this->lang->line('delete'));
                                        }
                                        ?>
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    $(function() {

        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");

        $('.image-preview').popover({
            trigger:'manual',
            html:true,
            title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
            content: "There's no image",
            placement:'bottom'
        });

        $('.image-preview-clear').click(function(){
            $('.image-preview').attr("data-content","").popover('hide');
            $('.image-preview-filename').val("");
            $('.image-preview-clear').hide();
            $('#image-preview-input input:file').val("");
            $(".image-preview-input-title").text("<?=$this->lang->line('tutorial_file_browse')?>");
        });

        $("#image-preview-input input:file").change(function (){
            var img = $('<img/>', {
                id: 'dynamic',
                width:250,
                height:200,
                overflow:'hidden'
            });

            var file = this.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                $(".image-preview-input-title").text("<?=$this->lang->line('tutorial_file_browse')?>");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);
            }
            reader.readAsDataURL(file);
        });
    });
    $(function() {
        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");

        $('.video-preview').popover({
            trigger:'manual',
            html:true,
            title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
            content: "There's no video",
            placement:'bottom'
        });

        $('.video-preview-clear').click(function(){
            $('.video-preview').attr("data-content","").popover('hide');
            $('.video-preview-filename').val("");
            $('.video-preview-clear').hide();
            $('.video-preview-input input:file').val("");
            $(".video-preview-input-title").text("<?=$this->lang->line('tutorial_file_browse')?>");
        });

        $(".video-preview-input input:file").change(function (){
            var img = $('<img/>', {
                id: 'dynamic',
                width:250,
                height:200,
                overflow:'hidden'
            });

            var file = this.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                $(".video-preview-input-title").text("<?=$this->lang->line('tutorial_file_browse')?>");
                $(".video-preview-clear").show();
                $(".video-preview-filename").val(file.name);
            }
            reader.readAsDataURL(file);
        });
    });

</script>
