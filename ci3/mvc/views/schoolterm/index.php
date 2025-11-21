
<div class="box">
    <div class="box-header">
        <h3 class="box-title">
            <i class="fa fa-calendar-plus-o"></i>
            <?=$this->lang->line('panel_title');?>
        </h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_schoolterm')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">


                    <h5 class="page-header">
                        <?php if(permissionChecker('schoolterm_add')) { ?>
                            <a href="<?=base_url('schoolterm/add') ?>">
                                <i class="fa fa-plus"></i>
                                <?=$this->lang->line('add_title')?>
                            </a>
                        <?php } ?>
                    </h5>


                <div id="hide-table">
                    <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-2"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('schoolterm_schooltermtitle')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('schoolterm_startingdate')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('schoolterm_endingdate')?></th>
                                <?php if(permissionChecker('schoolterm_edit') || permissionChecker('schoolterm_delete')) { ?>
                                <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if(customCompute($schoolterms)) {$i = 1; foreach($schoolterms as $schoolterm) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?=$i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('schoolterm_schooltermtitle')?>">
                                        <?=$schoolterm->schooltermtitle; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('schoolterm_startingdate')?>">
                                        <?php
                                            if($schoolterm->startingdate) {
                                                $startingdate = date("d-m-Y", strtotime($schoolterm->startingdate));
                                            }
                                            echo $startingdate;
                                        ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('schoolterm_endingdate')?>">
                                        <?php
                                            if($schoolterm->endingdate) {
                                                $endingdate = date("d-m-Y", strtotime($schoolterm->endingdate));
                                            }
                                            echo $endingdate;
                                        ?>
                                    </td>
                                    <?php if(permissionChecker('schoolterm_edit') || permissionChecker('schoolterm_delete')) { ?>
                                        <td data-title="<?=$this->lang->line('action')?>">
                                            <?=btn_edit('schoolterm/edit/'.$schoolterm->schooltermID, $this->lang->line('edit')) ?>
                                            <?=(($schoolterm->schooltermID!=1) ? btn_delete('schoolterm/delete/'.$schoolterm->schooltermID, $this->lang->line('delete')) : '')?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
