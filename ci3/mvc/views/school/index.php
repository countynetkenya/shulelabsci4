
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-credittypes"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_school')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php if(permissionChecker('school_add')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('school/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?=$this->lang->line('add_title')?>
                        </a>
                    </h5>
                <?php } ?>

                <div id="hide-table">
                    <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-2"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-8"><?=$this->lang->line('school_name')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($schools)) {$i = 1; foreach($schools as $school) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('school_name')?>">
                                        <?php echo $school->name; ?>
                                    </td>
                                    <td>
                                      <?php if(permissionChecker('school_edit')) {
                                        echo btn_edit('school/edit/'.$school->schoolID, $this->lang->line('edit'));
                                      } ?>
                                      <form style="display:inline" action="<?php echo base_url('school/select') ?>" method="post">
                                        <input type="hidden" name="schoolID" value="<?=$school->schoolID?>">
                                        <button type="submit" class="btn btn-xs btn-success mrg" data-toggle="tooltip" data-original-title="Sign in"><i class="fa fa-sign-in"></i></button>
                                      </form>
                                    </td>
                                </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
