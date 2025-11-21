<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
        echo btn_printReport('stockreport', $this->lang->line('report_print'), 'printablediv');

        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?=$this->lang->line('stockreport_report_for')?> - <?=$this->lang->line('stockreport_sale')?>  </h3>
    </div><!-- /.box-header -->

    <div id="printablediv">
        <!-- form start -->
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>

                <?php if($fromdate != '' && $todate != '' ) { ?>
                    <div class="col-sm-12">
                        <h5 class="pull-left">
                            <?=$this->lang->line('productsaleitemreport_fromdate')?> : <?=date('d M Y',strtotime($fromdate))?></p>
                        </h5>
                        <h5 class="pull-right">
                            <?=$this->lang->line('productsaleitemreport_todate')?> : <?=date('d M Y',strtotime($todate))?></p>
                        </h5>
                    </div>
                <?php } ?>

                <div class="col-sm-12" style="margin-top:5px">
                    <?php if (customCompute($stocks)) {?>
                          <div id="fees_collection_details" class="tab-pane active">
                              <div id="hide-table">
                                  <table id="example1" class="table table-striped table-bordered table-hover">
                                      <thead>
                                          <tr>
                                              <th><?=$this->lang->line('slno')?></th>
                                              <th><?=$this->lang->line('stockreport_product')?></th>
                                              <th><?=$this->lang->line('stockreport_quantity')?></th>
                                              <th><?=$this->lang->line('stockreport_date')?></th>
                                              <th><?=$this->lang->line('stockreport_from')?></th>
                                              <th><?=$this->lang->line('stockreport_to')?></th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php
                                              $i=1;
                                              foreach($stocks as $stock) { ?>
                                                  <tr>
                                                      <td data-title="<?=$this->lang->line('slno')?>"><?=$i?></td>

                                                      <td data-title="<?=$this->lang->line('stockreport_product')?>">
                                                          <?=$products[$stock->productID];?>
                                                      </td>

                                                      <td data-title="<?=$this->lang->line('stockreport_quantity')?>">
                                                         <?=$stock->quantity;?>
                                                      </td>

                                                      <td data-title="<?=$this->lang->line('stockreport_date')?>">
                                                         <?php echo date("d M Y", strtotime($stock->create_date)) ; ?>
                                                      </td>

                                                      <td data-title="<?=$this->lang->line('stockreport_from')?>">
                                                         <?=$productwarehouses[$stock->stockfromwarehouseID];?>
                                                      </td>

                                                      <td data-title="<?=$this->lang->line('stockreport_to')?>">
                                                         <?=$productwarehouses[$stock->stocktowarehouseID];?>
                                                      </td>
                                                  </tr>
                                              <?php $i++; }  ?>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                    <?php } else { ?>
                    <div class="callout callout-danger">
                        <p><b class="text-info"><?=$this->lang->line('stockreport_data_not_found')?></b></p>
                    </div>
                    <?php } ?>
                </div>
                <div class="col-sm-12 text-center footerAll">
                    <?=reportfooter($siteinfos, $schoolyearsessionobj)?>
                </div>
            </div><!-- row -->
        </div><!-- Body -->
    </div>
</div>
