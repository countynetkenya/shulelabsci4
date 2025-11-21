<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-calculator"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('panel_title')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
              <h5 class="page-header">
                <?php if(permissionChecker('inventory_transfer_memo_add')) { ?>
                    <a href="<?php echo base_url('inventory_transfer_memo/add') ?>">
                        <i class="fa fa-arrow-right"></i>
                        <?=$this->lang->line('move_title')?>
                    </a>
                <?php }?>
              </h5>

                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                        <label for="date from" class="control-label">
                            <?=$this->lang->line('stock_from')?>
                        </label>
                      <input name="dateFrom" id="dateFrom" type="date" class="form-control" value="<?=$set_dateFrom?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                        <label for="date to" class="control-label">
                            <?=$this->lang->line('stock_to')?>
                        </label>
                      <input name="dateTo" id="dateTo" type="date" class="form-control" value="<?=$set_dateTo?>">
                    </div>
                  </div>
                </div>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('stock_from')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('stock_to')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('stock_quantity')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('stock_date')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($mainstocks)) {$i = 1; foreach($mainstocks as $mainstock) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_from')?>">
                                        <?=$productwarehouses[$mainstock->stockfromwarehouseID];?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_to')?>">
                                        <?=$productwarehouses[$mainstock->stocktowarehouseID];?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_quantity')?>">
                                        <?php if(isset($totalquantities['totalquantity'][$mainstock->mainstockID])) { echo $totalquantities['totalquantity'][$mainstock->mainstockID]; } else { echo 0; } ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_date')?>">
                                        <?php echo date("d M Y", strtotime($mainstock->mainstockcreate_date)) ; ?>
                                    </td>

                                    <td>
                                      <?php if(permissionChecker('inventory_transfer_memo_view')) {
                                        echo btn_view('inventory_transfer_memo/view/'.$mainstock->mainstockID, $this->lang->line('view'));
                                      } ?>
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
