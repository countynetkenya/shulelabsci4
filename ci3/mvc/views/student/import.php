
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-student"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("student/index")?>"><?=$this->lang->line('menu_student')?></a></li>
            <li class="active"><?=$this->lang->line('menu_import')?> <?=$this->lang->line('menu_student')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form action="<?=base_url('bulkimport/student_with_parent_bulkimport');?>" class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="photo" class="col-sm-2 control-label col-xs-8 col-md-2">
                            <?=$this->lang->line("bulkimport_add_student")?>
                            &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Download the student sample csv file first then see the format and make a copy of that file and add your data with exact format which is used in our csv file then upload the file."></i>
                        </label>
                        <div class="col-sm-3 col-xs-4 col-md-3">
                            <input class="form-control student"  id="uploadFile" placeholder="Choose File" disabled />
                        </div>

                        <div class="col-sm-2 col-xs-6 col-md-2">
                            <div class="fileUpload btn btn-success form-control">
                                <span class="fa fa-repeat"></span>
                                <span><?=$this->lang->line("bulkimport_upload")?></span>
                                <input id="uploadBtn" type="file" class="upload studentUpload" name="csvStudentWithParent" data-show-upload="false" data-show-preview="false" required="required"/>
                            </div>
                        </div>

                        <div class="col-md-1 rep-mar">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("bulkimport_import")?>" >
                        </div>
                        <div class="col-md-1 rep-mar">
                            <a class="btn btn-info" href="<?=base_url('assets/csv/sample_student_with_parent.csv')?>"><i class="fa fa-download"></i> <?=$this->lang->line("bulkimport_download_sample")?></a>
                        </div>
                    </div>
                </form>

                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="callout callout-danger">
                      <h4>These data not inserted</h4>
                      <p><?=$this->session->flashdata('msg'); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="callout callout-danger">
                        <p><?=$this->session->flashdata('error'); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($siteinfos->note==1) { ?>
                    <div class="callout callout-danger">
                        <p><b>Note:</b> Create teacher, class, section before create a new student.</p>
                    </div>
                <?php } ?>
            </div> <!-- col-sm-8 -->

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
$( ".select2" ).select2();

</script>
