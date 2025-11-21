
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-invoice"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_creditmemo')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php if(($siteinfos->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) { ?>
                    <?php if(permissionChecker('creditmemo_add')) { ?>
                        <h5 class="page-header">
                            <a href="<?php echo base_url('creditmemo/add') ?>">
                                <i class="fa fa-plus"></i>
                                <?=$this->lang->line('add_title')?>
                            </a>
                        </h5>
                    <?php } ?>
                <?php } ?>

                <div id="hide-table">
                    <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th><?=$this->lang->line('slno')?></th>
                                <th><?=$this->lang->line('creditmemo_student')?></th>
                                <th><?=$this->lang->line('creditmemo_classesID')?></th>
                                <th><?=$this->lang->line('creditmemo_total')?></th>
                                <th><?=$this->lang->line('creditmemo_date')?></th>
                                <?php if(permissionChecker('creditmemo_view') || permissionChecker('creditmemo_edit') || permissionChecker('creditmemo_delete')) { ?>
                                    <th><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($maincreditmemos)) {$i = 1; foreach($maincreditmemos as $maincreditmemo) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('creditmemo_student')?>">
                                        <?php echo $maincreditmemo->srname; ?>
                                    </td>

                                     <td data-title="<?=$this->lang->line('creditmemo_classesID')?>">
                                        <?php echo $maincreditmemo->srclasses; ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('creditmemo_total')?>">
                                        <?php if(isset($grandtotalandpayment['totalamount'][$maincreditmemo->maincreditmemoID])) { echo number_format($grandtotalandpayment['totalamount'][$maincreditmemo->maincreditmemoID], 2); } else { echo '0.00'; } ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('creditmemo_date')?>">
                                        <?php echo date("d M Y", strtotime($maincreditmemo->maincreditmemodate)) ; ?>
                                    </td>

                                    <?php if(permissionChecker('creditmemo_view') || permissionChecker('creditmemo_edit') || permissionChecker('creditmemo_delete')) { ?>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                        <?php echo btn_view('creditmemo/view/'.$maincreditmemo->maincreditmemoID, $this->lang->line('view')) ?>
                                        <?php if(($siteinfos->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) { ?>
                                            <?php echo btn_edit('creditmemo/edit/'.$maincreditmemo->maincreditmemoID, $this->lang->line('edit')); ?>
                                            <?php echo btn_delete('creditmemo/delete/'.$maincreditmemo->maincreditmemoID, $this->lang->line('delete')); ?>
                                        <?php } ?>

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
