<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-leanpub"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("tutorial/index")?>"></i> <?=$this->lang->line('menu_tutorial')?></a></li>
            <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('menu_tutorial')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post">

                    <div class="form-group <?=form_error('title') ? 'has-error' : ''?>">
                        <label for="title" class="col-sm-2 control-label">
                            <?=$this->lang->line("tutorial_title")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="title" name="title" value="<?=set_value('title')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('title')?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('classesID') ? 'has-error' : ''?>">
                        <label for="classesID" class="col-sm-2 control-label">
                            <?=$this->lang->line("tutorial_class")?>
                        </label>
                        <div class="col-sm-6">
                            
                            <?php
                                $classArray[0] = $this->lang->line("tutorial_select_class");
                                foreach ($classes as $classa) {
                                    $classArray[$classa->classesID] = $classa->classes;
                                }
                                echo form_dropdown("classesID", $classArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('classesID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('sectionID') ? 'has-error' : ''?>">
                        <label for="sectionID" class="col-sm-2 control-label">
                            <?=$this->lang->line("tutorial_section")?> 
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $sectionArray[0] = $this->lang->line("tutorial_select_section");
                                foreach ($sections as $section) {
                                    $sectionArray[$section->sectionID] = $section->section;
                                }
                                echo form_dropdown("sectionID", $sectionArray, set_value("sectionID"), "id='sectionID' class='form-control select2'");
                            ?>
                        </div>
                 
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('sectionID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('subjectID') ? 'has-error' : ''?>">
                        <label for="subjectID" class="col-sm-2 control-label">
                            <?=$this->lang->line("tutorial_subject")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $subjectArray[0] = $this->lang->line("tutorial_select_subject");
                                foreach ($subjects as $subject) {
                                    $subjectArray[$subject->subjectID] = $subject->subject;
                                }
                                echo form_dropdown("subjectID", $subjectArray, set_value("subjectID"), "id='subjectID' class='form-control select2'");
                            ?>   
                        </div>
                        
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('subjectID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('is_public') ? 'has-error' : ''?>">
                        <label for="is_public" class="col-sm-2 control-label">
                            <?=$this->lang->line("tutorial_is_public")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="checkbox" name="is_public" />
                        </div>
                        
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('is_public'); ?>
                        </span>
                    </div>

                    
                    <div class="form-group <?php if(form_error('cover_photo')) { echo 'has-error'; } ?>" >
                        <label for="cover_photo" class="col-sm-2 control-label">
                            <?=$this->lang->line("tutorial_cover_photo")?>
                        </label>
                        <div class="col-sm-6">
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename" disabled="disabled">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                        <span class="fa fa-remove"></span>
                                        <?=$this->lang->line('tutorial_clear')?>
                                    </button>
                                    <div class="btn btn-success image-preview-input">
                                        <span class="fa fa-repeat"></span>
                                        <span class="image-preview-input-title">
                                        <?=$this->lang->line('tutorial_file_browse')?></span>
                                        <input type="file" name="cover_photo"/>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <span class="col-sm-4 control-label">
                            <?php echo form_error('cover_photo'); ?>
                        </span>
                    </div>


                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("add_tutorial")?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(".select2" ).select2();

    $('#classesID').change(function(event) {
        var classesID = $(this).val();
        if(classesID === '0') {
            $('#subjectID').val(0);
            $('#sectionID').val('');
        } else {
            $('#sectionID').val('');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('tutorial/subjectcall')?>",
                data: "id=" + classesID,
                dataType: "html",
                success: function(data) {
                   $('#subjectID').html(data);
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?=base_url('tutorial/sectioncall')?>",
                data: "id=" + classesID,
                dataType: "html",
                success: function(data) {
                   $('#sectionID').html(data);
                }
            });
        }
    });

    $(document).on('click', '#close-preview', function(){
        $('.image-preview').popover('hide');
        // Hover befor close the preview
        $('.image-preview').hover(
            function () {
               $('.image-preview').popover('show');
               $('.content').css('padding-bottom', '100px');
            },
             function () {
               $('.image-preview').popover('hide');
               $('.content').css('padding-bottom', '20px');
            }
        );
    });

    $(function() {
        // Create the close button
        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");
        // Set the popover default content
        $('.image-preview').popover({
            trigger:'manual',
            html:true,
            title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
            content: "There's no image",
            placement:'bottom'
        });
        // Clear event
        $('.image-preview-clear').click(function(){
            $('.image-preview').attr("data-content","").popover('hide');
            $('.image-preview-filename').val("");
            $('.image-preview-clear').hide();
            $('.image-preview-input input:file').val("");
            $(".image-preview-input-title").text("<?=$this->lang->line('tutorial_file_browse')?>");
        });
        // Create the preview image
        $(".image-preview-input input:file").change(function (){
            var img = $('<img/>', {
                id: 'dynamic',
                width:250,
                height:200,
                overflow:'hidden'
            });
            var file = this.files[0];
            var reader = new FileReader();
            // Set preview image into the popover data-content
            reader.onload = function (e) {
                $(".image-preview-input-title").text("<?=$this->lang->line('tutorial_file_browse')?>");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);
                img.attr('src', e.target.result);
                $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
                $('.content').css('padding-bottom', '100px');
            }
            reader.readAsDataURL(file);
        });
    });

</script>
