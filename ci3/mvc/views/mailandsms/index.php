<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-mailandsms"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_mailandsms')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php if(permissionChecker('mailandsms_add')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('mailandsms/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?=$this->lang->line('add_title')?>
                        </a>
                        &nbsp&nbsp
                        <a href="<?php echo base_url('mailandsms/review') ?>">
                            <i class="fa fa-edit"></i>
                            <?=$this->lang->line('review_title')?>
                        </a>
                    </h5>
                <?php } ?>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                        <label for="date from" class="control-label">
                            <?=$this->lang->line('mailandsms_from')?>
                        </label>
                      <input name="dateFrom" id="dateFrom" type="date" class="form-control" value="<?=$set_dateFrom?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                        <label for="date to" class="control-label">
                            <?=$this->lang->line('mailandsms_to')?>
                        </label>
                      <input name="dateTo" id="dateTo" type="date" class="form-control" value="<?=$set_dateTo?>">
                    </div>
                  </div>
                </div>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th><?=$this->lang->line('slno')?></th>
                                <th><?=$this->lang->line('mailandsms_usertype')?></th>
                                <th><?=$this->lang->line('mailandsms_users')?></th>
                                <th><?=$this->lang->line('mailandsms_type')?></th>
                                <th><?=$this->lang->line('mailandsms_dateandtime')?></th>
                                <th><?=$this->lang->line('mailandsms_message')?></th>
                                <th><?=$this->lang->line('mailandsms_sentby')?></th>
                                <th><?=$this->lang->line('status')?></th>
                                <?php if(permissionChecker('mailandsms_view') || permissionChecker('mailandsms_resend')) { ?>
                                <th><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($mailandsmss)) {$i = 1; foreach($mailandsmss as $mailandsms) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_usertype')?>">
                                        <?=($mailandsms->usertypeID !== NULL) ? $mailandsms->usertype : $this->lang->line('mailandsms_guest_user')?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('mailandsms_users')?>">
                                        <?php
                                            if(strlen($mailandsms->users) > 36) {
                                                echo substr($mailandsms->users, 0, 36). "..";
                                            } else {
                                                echo $mailandsms->users;
                                            }
                                        ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_type')?>">
                                        <?php echo $mailandsms->type; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_dateandtime')?>">
                                        <?php echo date("d M Y h:i:s a", strtotime($mailandsms->sent_date));?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_message')?>">
                                        <?php echo substr(strip_tags($mailandsms->message), 0, 36). ".."; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_sentby')?>">
                                        <?php echo $mailandsms->sendername; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('delivery_report')?>">
                                        <?php if ($mailandsms->type == "Sms" || $mailandsms->type == "Other SMS") { echo (strpos($mailandsms->delivery_report, "FAILED") !== false) ? "<i class='fa fa-exclamation-circle' title='Failed' style='color: red;'></i>" : ( (strpos($mailandsms->delivery_report, "DELIVRD") !== false || strpos($mailandsms->delivery_report, "DeliveredToTerminal") !== false) ? "<i class='fa fa-check-circle' title='Delivered' style='color: green;'></i>" : "<i class='fa fa-clock-o' title='Pending'></i>" ); }?>
                                    </td>
                                    <?php if(permissionChecker('mailandsms_view') || permissionChecker('mailandsms_resend')) { ?>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                        <form action="view" method="POST" style="display:inline">
                                          <input name="id" type="hidden" value="<?=$mailandsms->mailandsmsID?>">
                                          <button class="btn btn-success btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="delivery report" type="submit"><i class='fa fa-check-square-o'></i></button>
                                        </form>
                                        <!--<?php echo btn_view('mailandsms/view/'.$mailandsms->mailandsmsID, $this->lang->line('view')) ?>-->
                                        <?php echo btn_resend('mailandsms/resend/'.$mailandsms->mailandsmsID, $this->lang->line('resend')) ?>
                                    </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>


            </div> <!-- col-sm-12 -->

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->
