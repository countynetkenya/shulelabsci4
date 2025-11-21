<?php if(customCompute($product)) { ?>
    <div class="well">
        <div class="row">
            <div class="col-sm-6"></div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("product/index")?>"><?=$this->lang->line('panel_title')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_view')?></li>
                </ol>
            </div>
        </div>
    </div>

    <div id="printablediv">
        <section class="content invoice" >
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table class="table table-bordered product-style">
                            <thead>
                                <tr>
                                    <?php foreach($productwarehouses as $productwarehouse) {?>
                                      <th class="col-lg-1"><?=$productwarehouse->productwarehousecode?></th>
                                    <?php }?>
                                    <th class="col-lg-2"><?=$this->lang->line('product_lastbuyingprice')?></th>
                                    <th class="col-lg-2"><?=$this->lang->line('product_averagebuyingprice')?></th>
                                    <th class="col-lg-2"><?=$this->lang->line('product_sellingprice')?></th>
                                    <th class="col-lg-2"><?=$this->lang->line('product_lastsupplier')?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach($productwarehouses as $productwarehouse) {?>
                                      <td data-title="<?=$productwarehouse->productwarehousecode?>">
                                        <?=$productwarehousequantity[$productwarehouse->productwarehouseID]?>
                                      </td>
                                    <?php }?>

                                    <td data-title="<?=$this->lang->line('product_lastbuyingprice')?>">
                                        <?=$lastbuyingprice->productpurchaseunitprice?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_averagebuyingprice')?>">
                                        <?=$averageunitprice->averageunitprice?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_sellingprice')?>">
                                        <?=$product->productsellingprice?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_lastsuplier')?> ">
                                        <?=$lastsupplier->productsuppliername?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-sm-9 col-xs-12 pull-left">
                    <p><?=$product->productdesc?></p>
                </div>
                <div class="col-sm-3 col-xs-12 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?=$this->lang->line('product_create_by')?> : <?=$createuser?>
                            <br>
                            <?=$this->lang->line('product_date')?> : <?=date('d M Y', strtotime($product->create_date))?>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script language="javascript" type="text/javascript">
        function printDiv(divID) {
            var divElements = document.getElementById(divID).innerHTML;
            var oldPage = document.body.innerHTML;
            document.body.innerHTML =
              "<html><head><title></title></head><body>" +
              divElements + "</body>";
            window.print();
            document.body.innerHTML = oldPage;
            window.location.reload();
        }
    </script>
<?php } ?>
