
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-list-ol"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_examranking')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="page-header">
                    <?php if(permissionChecker('examranking_add')) { ?>
                        <a href="<?php echo base_url('examranking/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?=$this->lang->line('add_examranking')?>
                        </a>
                    <?php } ?>

                    <div class="col-lg-2 col-sm-2 col-md-2 col-xs-12 pull-right drop-marg">
                        <?php
                            $array = array("0" => $this->lang->line("examranking_select_class"));
                            if(customCompute($classes)) {
                                foreach ($classes as $classa) {
                                    $array[$classa->classesID] = $classa->classes;
                                }
                            }
                            echo form_dropdown("classesID", $array, set_value("classesID", $set), "id='classesID' class='form-control select2'");
                        ?>
                    </div>
                </h5>

                <div id="hide-table">
                    <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-2"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('examranking_examranking')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('examranking_subjects')?></th>
                                <?php if(permissionChecker('examranking_edit') || permissionChecker('examranking_delete')) { ?>
                                <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($examrankings)) {$i = 1; foreach($examrankings as $examranking) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('examranking_examranking')?>">
                                        <?php echo $examranking->examranking; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('examranking_subjects')?>">
                                        <?php $subjects = [];
                                        $examranking->subjects = explode(",", $examranking->subjects);
                                        foreach($examranking->subjects as $subject) {
                                          $subjects[] = $subjectsArr[$subject];
                                        }
                                        echo implode(", ", $subjects); ?>
                                    </td>
                                    <?php if(permissionChecker('examranking_edit') || permissionChecker('examranking_delete')) { ?>
                                        <td data-title="<?=$this->lang->line('action')?>">
                                            <?php echo btn_edit('examranking/edit/'.$examranking->examrankingID.'/'.$examranking->classesID, $this->lang->line('edit')) ?>
                                            <?php echo btn_delete('examranking/delete/'.$examranking->examrankingID.'/'.$examranking->classesID, $this->lang->line('delete')) ?>
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

<script type="text/javascript">
    $('.select2').select2();
    $('#classesID').change(function() {
        var classesID = $(this).val();
        /*if(classesID == 0) {
            $('#hide-table').hide();
        } else {*/
            $.ajax({
                type: 'POST',
                url: "<?=base_url('examranking/subject_list')?>",
                data: "id=" + classesID,
                dataType: "html",
                success: function(data) {
                    window.location.href = data;
                }
            });
        //}
    });
</script>
