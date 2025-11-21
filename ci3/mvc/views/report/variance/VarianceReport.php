<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
        echo btn_printReport('variancereport', $this->lang->line('report_print'), 'printablediv');

        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?=$this->lang->line('variancereport_report_for')?> - <?=$this->lang->line('variancereport_variance')?>  </h3>
    </div><!-- /.box-header -->

    <div id="printablediv">
        <!-- form start -->
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>

                <div class="col-sm-12" style="margin-top:5px">
                    <?php if (customCompute($products)) {?>
                          <div id="fees_collection_details" class="tab-pane active">
                              <div id="hide-table">
                                  <table id="example1" class="table table-striped table-bordered table-hover">
                                      <thead>
                                          <tr>
                                              <th><?=$this->lang->line('variancereport_productID')?></th>
                                              <th><?=$this->lang->line('variancereport_product')?></th>
                                              <th><?=$this->lang->line('variancereport_cost')?></th>
                                              <th><?=$this->lang->line('variancereport_expectedquantity')?></th>
                                              <th><?=$this->lang->line('variancereport_countedquantity')?></th>
                                              <th><?=$this->lang->line('variancereport_differencequantity')?></th>
                                              <th><?=$this->lang->line('variancereport_expectedcost')?></th>
                                              <th><?=$this->lang->line('variancereport_countedcost')?></th>
                                              <th><?=$this->lang->line('variancereport_differencecost')?></th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php foreach($products as $product) {
                                              $diffQuantity = (int)$productpurchasequintity[$product->productID]-(int)$productsalequintity[$product->productID]-(int)$product->quantity;
                                              $diffCost = ((int)$productpurchasequintity[$product->productID]-(int)$productsalequintity[$product->productID]-(int)$product->quantity)*$product->averageunitprice; ?>
                                              <tr>
                                                <td data-title="<?=$this->lang->line('variancereport_productID')?>">
                                                    <?=$product->productID;?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_product')?>">
                                                    <?=$product->productname;?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_cost')?>">
                                                    <?=number_format($product->productbuyingprice, 2);?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_expectedquantity')?>">
                                                   <?=(int)$productpurchasequintity[$product->productID]-(int)$productsalequintity[$product->productID];?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_countedquantity')?>">
                                                   <?=$product->quantity; ?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_differencequantity')?>">
                                                   <span style='color:<?=($diffQuantity < 0) ? "red" : (($diffQuantity > 0) ? "green" : "");?>'><?=$diffQuantity?></span>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_expectedcost')?>">
                                                   <?=number_format(((int)$productpurchasequintity[$product->productID]-(int)$productsalequintity[$product->productID])*$product->averageunitprice, 2);?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_countedcost')?>">
                                                   <?=number_format($product->quantity*$product->averageunitprice, 2);?>
                                                </td>

                                                <td data-title="<?=$this->lang->line('variancereport_differencecost')?>">
                                                   <span style='color:<?=($diffCost < 0) ? "red" : (($diffCost > 0) ? "green" : "");?>'><?=number_format($diffCost, 2);?></span>
                                                </td>
                                            </tr>
                                          <?php }  ?>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                    <?php } else { ?>
                    <div class="callout callout-danger">
                        <p><b class="text-info"><?=$this->lang->line('report_data_not_found')?></b></p>
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
