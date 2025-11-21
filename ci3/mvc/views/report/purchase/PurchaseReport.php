<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            echo btn_printReport('productpurchasereport', $this->lang->line('report_print'), 'printablediv');

        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?=$this->lang->line('productpurchasereport_report_for')?> - <?=$this->lang->line('productpurchasereport_product_purchase')?>  </h3>
    </div><!-- /.box-header -->

    <div id="printablediv">
        <!-- form start -->
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>

                <div class="col-sm-12">
                    <?php if($fromdate != '' && $todate != '' ) { ?>
                        <h5 class="pull-left">
                            <?=$this->lang->line('productpurchasereport_fromdate')?> : <?=date('d M Y',strtotime($fromdate))?></p>
                        </h5>
                        <h5 class="pull-right">
                            <?=$this->lang->line('productpurchasereport_todate')?> : <?=date('d M Y',strtotime($todate))?></p>
                        </h5>
                    <?php } elseif($reference_no != '0') { ?>
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productpurchasereport_referenceNo')." : ";
                                echo $reference_no;
                            ?>
                        </h5>
                    <?php } elseif($productsupplierID != 0 && $productwarehouseID != 0 ) { ?>
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productpurchasereport_supplier')." : ";
                                echo isset($productsuppliers[$productsupplierID]) ? $productsuppliers[$productsupplierID] : '';
                            ?>
                        </h5>
                        <h5 class="pull-right">
                            <?php
                                echo $this->lang->line('productpurchasereport_warehouse')." : ";
                                echo isset($productwarehouses[$productwarehouseID]) ? $productwarehouses[$productwarehouseID] : '';
                            ?>
                        </h5>
                    <?php } elseif($productsupplierID != 0) { ?>
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productpurchasereport_supplier')." : ";
                                echo isset($productsuppliers[$productsupplierID]) ? $productsuppliers[$productsupplierID] : '';
                            ?>
                        </h5>
                    <?php } elseif($productwarehouseID != 0) { ?>
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productpurchasereport_warehouse')." : ";
                                echo isset($productwarehouses[$productwarehouseID]) ? $productwarehouses[$productwarehouseID] : '';
                            ?>
                        </h5>
                    <?php } ?>
                </div>

                <div class="col-sm-12" style="margin-top:5px">
                    <?php if (customCompute($productpurchaseitems)) {
                        if ($reportdetails == "transaction") {?>
                        <div id="hide-table">
                            <table id="example1" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('slno')?></th>
                                        <th><?=$this->lang->line('productpurchasereport_referenceNo')?></th>
                                        <?php foreach($columns AS $column) {$lang = 'productpurchasereport_'. $column;?>
                                        <th><?=$this->lang->line($lang)?></th>
                                        <?php }?>
                                        <th><?=$this->lang->line('purchasereport_quantity')?></th>
                                        <th><?=$this->lang->line('purchasereport_cost')?></th>
                                        <th><?=$this->lang->line('purchasereport_totalcost')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $i=1;
                                    foreach($productpurchaseitems as $productpurchaseitem) { ?>
                                        <tr>
                                            <td data-title="<?=$this->lang->line('slno')?>"><?=$i?></td>
                                            <td data-title="<?=$this->lang->line('productpurchasereport_referenceNo')?>">
                                                <?=$productpurchaseitem['reference_no'];?>
                                            </td>
                                            <?php foreach($columns AS $column) {?>
                                            <td><?=$productpurchaseitem[$column];?></td>
                                            <?php }?>
                                            <td data-title="<?=$this->lang->line('purchasereport_quantity')?>">
                                               <?=$productpurchaseitem['productpurchasequantity'];?>
                                            </td>
                                            <td data-title="<?=$this->lang->line('purchasereport_cost')?>">
                                               <?=number_format($productpurchaseitem['averageunitprice'], 2);?>
                                            </td>
                                            <td data-title="<?=$this->lang->line('purchasereport_totalcost')?>">
                                               <?=number_format($productpurchaseitem['totalcost'], 2);?>
                                            </td>
                                        </tr>
                                    <?php $i++; } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } elseif ($reportdetails == "summary") {?>
                          <div id="hide-table">
                              <table id="example1" class="table table-striped table-bordered table-hover">
                                  <thead>
                                      <tr>
                                          <th><?=$this->lang->line('slno')?></th>
                                          <th><?=$this->lang->line('purchasereport_productID')?></th>
                                          <th><?=$this->lang->line('purchasereport_productdesc')?></th>
                                          <?php foreach($columns AS $column) {$lang = 'productpurchasereport_'. $column;?>
                                          <th><?=$this->lang->line($lang)?></th>
                                          <?php }?>
                                          <th><?=$this->lang->line('purchasereport_quantity')?></th>
                                          <th><?=$this->lang->line('purchasereport_cost')?></th>
                                          <th><?=$this->lang->line('purchasereport_totalcost')?></th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                  <?php
                                      $i=1;
                                      foreach($productpurchaseitems as $productpurchaseitem) { ?>
                                          <tr>
                                              <td data-title="<?=$this->lang->line('slno')?>"><?=$i?></td>
                                              <td data-title="<?=$this->lang->line('purchasereport_productID')?>">
                                                  <?=$productpurchaseitem['productID'];?>
                                              </td>
                                              <td data-title="<?=$this->lang->line('purchasereport_productdesc')?>">
                                                 <?=$productpurchaseitem['productdesc'];?>
                                              </td>
                                              <?php foreach($columns AS $column) {?>
                                              <td><?=$productpurchaseitem[$column];?></td>
                                              <?php }?>
                                              <td data-title="<?=$this->lang->line('purchasereport_quantity')?>">
                                                 <?=$productpurchaseitem['productpurchasequantity'];?>
                                              </td>
                                              <td data-title="<?=$this->lang->line('purchasereport_cost')?>">
                                                 <?=number_format($productpurchaseitem['averageunitprice'], 2);?>
                                              </td>
                                              <td data-title="<?=$this->lang->line('purchasereport_totalcost')?>">
                                                 <?=number_format($productpurchaseitem['totalcost'], 2);?>
                                              </td>
                                          </tr>
                                      <?php $i++; } ?>
                                  </tbody>
                              </table>
                          </div>
                    <?php }
                    } else { ?>
                    <div class="callout callout-danger">
                        <p><b class="text-info"><?=$this->lang->line('productpurchasereport_data_not_found')?></b></p>
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
