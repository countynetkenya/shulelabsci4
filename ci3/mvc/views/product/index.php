<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-product"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('panel_title')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <style type="text/css">
        .setting-fieldset {
            border: 1px solid #DBDEE0 !important;
            padding: 15px !important;
            margin: 0 20px 25px 20px !important;
            box-shadow: 0px 0px 0px 0px #000;
        }

        .setting-legend {
            font-size: 1.1em !important;
            font-weight: bold !important;
            text-align: left !important;
            width: auto;
            color: #428BCA;
            padding: 5px 15px;
            border: 1px solid #DBDEE0 !important;
            margin: 0px;
        }
    </style>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <?php if(permissionChecker('product_add')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('product/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?=$this->lang->line('add_title')?>
                        </a>
                    </h5>
                <?php } ?>

                <div class="row">
                  <div class="productwarehouseDiv form-group <?=form_error('productwarehouseID') ? 'has-error' : '' ?> col-sm-2 pull-right">
                    <label for="productwarehouse" class="control-label">
                        <?=$this->lang->line('product_warehouse')?>
                    </label>
                      <?php
                          $array = array("0" => $this->lang->line("product_select_warehouse"));
                          if(customCompute($productwarehouses)) {
                              foreach ($productwarehouses as $productwarehouse) {
                                  $array[$productwarehouse->productwarehouseID] = $productwarehouse->productwarehousename;
                              }
                          }
                          echo form_dropdown("productwarehouseID", $array, set_value("productwarehouseID", $_GET['warehouse']), "id='productwarehouseID' class='form-control select2'");
                      ?>
                  </div>
                </div>

                <div id="hide-table">
                    <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('product_product')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('product_category')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('product_quantity')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('product_desc')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('product_is_billable_default')?></th>
                                <?php if(permissionChecker('product_edit') || permissionChecker('product_delete')) { ?>
                                    <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($products)) {$i = 1; foreach($products as $product) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_product')?>">
                                        <?=$product->productname;?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_category')?>">
                                        <?=isset($productcategorys[$product->productcategoryID]) ? $productcategorys[$product->productcategoryID] : ''?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_quantity')?>">
                                        <?=$productpurchasequintity[$product->productID]->quantity - $productsalequintity[$product->productID]->quantity?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_desc')?>">
                                        <?=$product->productdesc?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('product_is_billable_default')?>">
                                        <?=$product->is_billable_default ? $this->lang->line('product_billable') : $this->lang->line('product_non_billable')?>
                                    </td>

                                    <?php if(permissionChecker('product_view') || permissionChecker('product_edit') || permissionChecker('product_delete')) { ?>
                                        <td data-title="<?=$this->lang->line('action')?>">
                                            <a href="<?=base_url('product/view/'.$product->productID.'/'.$set)?>" class="btn btn-success btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="View"><i class="fa fa-check-square-o"></i></a>
                                            <?php echo btn_edit('product/edit/'.$product->productID, $this->lang->line('edit')) ?>
                                            <?php echo btn_delete('product/delete/'.$product->productID, $this->lang->line('delete')) ?>
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

<script type="text/javascript">
    $(".select2").select2();

    $('#productwarehouseID').change(function() {
        var productwarehouseID = $(this).val();
        var url = "<?=base_url('product/index')?>";
        if(productwarehouseID != 0) {
            url = "<?=base_url('product/index')?>/" + productwarehouseID;
        }
        window.location.href = url;
    });

</script>
