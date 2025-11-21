<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-leanpub"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_tutorial')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="page-header">
                    <?php if(permissionChecker('tutorial_add')) { ?>
                        <a href="<?php echo base_url('tutorial/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?=$this->lang->line('add_title')?>
                        </a>
                    <?php } ?>
                </h5>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                        <tr>
                            <th class="col-sm-1">#</th>
                            <th class="col-sm-3"><?=$this->lang->line('tutorial_title')?></th>
                            <th class="col-sm-2"><?=$this->lang->line('tutorial_class')?></th>
                            <th class="col-sm-2"><?=$this->lang->line('tutorial_section')?></th>
                            <th class="col-sm-2"><?=$this->lang->line('tutorial_subject')?></th>
                            <?php if(permissionChecker('tutorial_edit') || permissionChecker('tutorial_delete') || permissionChecker('tutorial_view')) { ?>
                                <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(customCompute($tutorials)) {$i = 1;
                            foreach($tutorials as $tutorial) { ?>
                                <tr>
                                    <td data-title="#">
                                        <?=$i?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('tutorial_title')?>">
                                        <?=namesorting($tutorial->title, 80)?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('tutorial_class')?>">
                                        <?=isset($classes[$tutorial->classesID]) ? $classes[$tutorial->classesID] : '' ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('tutorial_section')?>">
                                        <?=isset($sections[$tutorial->sectionID]) ? $sections[$tutorial->sectionID] : ''?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('tutorial_subject')?>">
                                        <?=isset($subjects[$tutorial->subjectID]) ? $subjects[$tutorial->subjectID] : ''?>
                                    </td>
                                    <?php if(permissionChecker('tutorial_edit') || permissionChecker('tutorial_delete') || permissionChecker('tutorial_view')) { ?>
                                        <td data-title="<?=$this->lang->line('action')?>">
                                            <?php if(permissionChecker('tutorial_add')) { ?>
                                                <a href="<?=base_url('tutorial/addlesson/'.$tutorial->tutorial_id)?>" class="btn btn-xs btn-primary" data-placement="top" data-toggle="tooltip" data-original-title="Add Lesson"><i class="fa fa-list-alt"></i></a>
                                            <?php }
                                            echo btn_view('tutorial/view/'.$tutorial->tutorial_id, $this->lang->line('view'));
                                            echo btn_edit('tutorial/edit/'.$tutorial->tutorial_id, $this->lang->line('edit'));
                                            echo btn_delete('tutorial/delete/'.$tutorial->tutorial_id, $this->lang->line('delete'));
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
