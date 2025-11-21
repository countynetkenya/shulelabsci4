<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
        echo btn_printReport('productsalereport', $this->lang->line('report_print'), 'printablediv');

        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?=$this->lang->line('productsaleitemreport_report_for')?> - <?=$this->lang->line('productsaleitemreport_sale')?>  </h3>
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
                <?php } elseif($reference_no != '0') { ?>
                    <div class="col-sm-12">
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productsaleitemreport_referenceNo')." : ";
                                echo $reference_no;
                            ?>
                        </h5>
                    </div>
                <?php } elseif($productsalecustomertypeID != 0 && $productsalecustomerID != 0 ) { ?>
                    <div class="col-sm-12">
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productsaleitemreport_role')." : ";
                                echo isset($usertypes[$productsalecustomertypeID]) ? $usertypes[$productsalecustomertypeID] : '';
                            ?>
                        </h5>
                        <h5 class="pull-right">
                            <?php
                                echo $this->lang->line('productsaleitemreport_productsalecustomerName')." : ";
                                if(isset($users[3][$productsalecustomerID])) {
                                    $userName = isset($users[3][$productsalecustomerID]->name) ? $users[3][$productsalecustomerID]->name : $users[3][$productsalecustomerID]->srname;
                                    echo $userName;
                                }
                            ?>
                        </h5>
                    </div>
                <?php } else { ?>
                    <div class="col-sm-12">
                        <h5 class="pull-left">
                            <?php
                                echo $this->lang->line('productsaleitemreport_role')." : ";
                                 echo isset($usertypes[$productsalecustomertypeID]) ? $usertypes[$productsalecustomertypeID] : $this->lang->line('productsaleitemreport_all');
                            ?>
                        </h5>
                    </div>
                <?php } ?>

                <div class="col-sm-12" style="margin-top:5px">
                    <?php if (customCompute($productsaleitems)) {
                              if ($reportdetails == "transaction") {?>
                                <div id="fees_collection_details" class="tab-pane active">
                                    <div id="hide-table">
                                        <table id="example1" class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?=$this->lang->line('slno')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_referenceNo')?></th>
                                                    <?php foreach($columns AS $column) {$lang = 'productsaleitemreport_'. $column;?>
                                                    <th><?=$this->lang->line($lang)?></th>
                                                    <?php }?>
                                                    <th><?=$this->lang->line('productsaleitemreport_quantity')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_price')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_cost')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_margin')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_totalprice')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_totalcost')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_totalmargin')?></th>
                                                    <th><?=$this->lang->line('productsaleitemreport_marginpercentage')?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $i=1;
                                                    foreach($productsaleitems as $productsaleitem) { ?>
                                                        <tr>
                                                            <td data-title="<?=$this->lang->line('slno')?>"><?=$i?></td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_referenceNo')?>">
                                                                <?=$productsaleitem['productsalereferenceno'];?>
                                                            </td>

                                                            <?php foreach($columns AS $column) {?>
                                                            <td><?=$productsaleitem[$column];?></td>
                                                            <?php }?>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_quantity')?>">
                                                               <?=$productsaleitem['productsalequantity'];?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_price')?>">
                                                               <?=number_format($productsaleitem['productsaleunitprice'], 2);?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_cost')?>">
                                                               <?=number_format($productsaleitem['averageunitprice'], 2);?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_margin')?>">
                                                               <?=$productsaleitem['margin'];?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_totalprice')?>">
                                                               <?=number_format($productsaleitem['totalprice'], 2);?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_totalcost')?>">
                                                               <?=number_format($productsaleitem['totalcost'], 2);?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_totalmargin')?>">
                                                               <?=number_format($productsaleitem['totalmargin'], 2);?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('productsaleitemreport_marginpercentage')?>">
                                                               <?=$productsaleitem['marginpercentage'];?>
                                                            </td>
                                                        </tr>
                                                    <?php $i++; }  ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                              <?php } elseif ($reportdetails == "summary") {?>
                                  <div id="fees_collection_details" class="tab-pane active">
                                      <div id="hide-table">
                                          <table id="example1" class="table table-striped table-bordered table-hover">
                                              <thead>
                                                  <tr>
                                                      <th><?=$this->lang->line('slno')?></th>
                                                      <?php foreach($columns AS $column) {$lang = 'productsaleitemreport_'. $column;?>
                                                      <th><?=$this->lang->line($lang)?></th>
                                                      <?php }?>
                                                      <th><?=$this->lang->line('productsaleitemreport_productID')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_productdesc')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_quantity')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_price')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_cost')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_margin')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_totalprice')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_totalcost')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_totalmargin')?></th>
                                                      <th><?=$this->lang->line('productsaleitemreport_marginpercentage')?></th>
                                                  </tr>
                                              </thead>
                                              <tbody>
                                                  <?php
                                                      $i=1;
                                                      foreach($productsaleitems as $productsaleitem) { ?>
                                                          <tr>
                                                              <td data-title="<?=$this->lang->line('slno')?>"><?=$i?></td>

                                                              <?php foreach($columns AS $column) {?>
                                                              <td><?=$productsaleitem[$column];?></td>
                                                              <?php }?>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_productID')?>">
                                                                 <?=$productsaleitem['productID'];?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_productdesc')?>">
                                                                 <?=$productsaleitem['productdesc'];?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_quantity')?>">
                                                                 <?=$productsaleitem['productsalequantity'];?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_price')?>">
                                                                 <?=number_format($productsaleitem['productsaleunitprice'], 2);?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_cost')?>">
                                                                 <?=number_format($productsaleitem['averageunitprice'], 2);?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_margin')?>">
                                                                 <?=$productsaleitem['margin'];?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_totalprice')?>">
                                                                 <?=number_format($productsaleitem['totalprice'], 2);?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_totalcost')?>">
                                                                 <?=number_format($productsaleitem['totalcost'], 2);?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_totalmargin')?>">
                                                                 <?=$productsaleitem['totalmargin'];?>
                                                              </td>

                                                              <td data-title="<?=$this->lang->line('productsaleitemreport_marginpercentage')?>">
                                                                 <?=$productsaleitem['marginpercentage'];?>
                                                              </td>
                                                          </tr>
                                                      <?php $i++; }  ?>
                                              </tbody>
                                          </table>
                                      </div>
                                  </div>
                        <?php }
                    } else { ?>
                    <div class="callout callout-danger">
                        <p><b class="text-info"><?=$this->lang->line('productsaleitemreport_data_not_found')?></b></p>
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
