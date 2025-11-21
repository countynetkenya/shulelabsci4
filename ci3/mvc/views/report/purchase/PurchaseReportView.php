<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-productpurchasereport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_productpurchasereport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group col-sm-3" id="supplierDiv">
                    <label><?=$this->lang->line("productpurchasereport_supplier")?></label>
                    <?php
                    $supplierArray = array(
                        "0" => $this->lang->line("productpurchasereport_please_select"),
                    );
                    if(customCompute($productsuppliers)) {
                        foreach($productsuppliers as $productsupplier) {
                            $supplierArray[$productsupplier->productsupplierID] = $productsupplier->productsuppliercompanyname;
                        }
                    }
                    echo form_dropdown("productsupplierID", $supplierArray, set_value("productsupplierID"), "id='productsupplierID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="WarehouseDiv">
                    <label><?=$this->lang->line("productpurchasereport_warehouse")?></label>
                    <?php
                    $wareHouseArray = array(
                        "0" => $this->lang->line("productpurchasereport_please_select"),
                    );
                    if(customCompute($productwarehouses)) {
                        foreach($productwarehouses as $productwarehouse) {
                            $wareHouseArray[$productwarehouse->productwarehouseID] = $productwarehouse->productwarehousename;
                        }
                    }
                    echo form_dropdown("productwarehouseID", $wareHouseArray, set_value("productwarehouseID"), "id='productwarehouseID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="referenceNoDiv">
                    <label><?=$this->lang->line("productpurchasereport_referenceNo")?></label>
                    <input class="form-control" type="text" name="reference_no" id="reference_no">
                </div>

                <div class="form-group col-sm-3" id="fromdateDiv">
                    <label><?=$this->lang->line("productpurchasereport_fromdate")?></label>
                   <input class="form-control" type="text" name="fromdate" id="fromdate">
                </div>

                <div class="form-group col-sm-3" id="todateDiv">
                    <label><?=$this->lang->line("productpurchasereport_todate")?></label>
                    <input class="form-control" type="text" name="todate" id="todate">
                </div>

                <div class="form-group col-sm-3" id="productIDDiv">
                    <label><?=$this->lang->line("purchasereport_productID")?></label>
                    <input class="form-control" type="text" name="productID" id="productID">
                </div>

                <div class="form-group col-sm-3" id="productNameDiv">
                    <label><?=$this->lang->line("purchasereport_product")?></label>
                    <?php
                    $productArray = array(0 => $this->lang->line("productpurchasereport_please_select"));
                    if(customCompute($products)) {
                        foreach ($products as $product) {
                            $productArray[$product->productname] = $product->productname;
                        }
                    }
                    echo form_dropdown("productname", $productArray, set_value("productname"), "id='productname' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="productcategoryDiv">
                    <label for="productcategoryID"><?=$this->lang->line("productpurchasereport_category")?></label>
                    <?php
                    $productcategoryArray = array(0 => $this->lang->line("productpurchasereport_please_select"));
                    if(customCompute($productcategories)) {
                        foreach ($productcategories as $productcategory) {
                            $productcategoryArray[$productcategory->productcategoryID] = $productcategory->productcategoryname;
                        }
                    }
                    echo form_dropdown("productcategoryID", $productcategoryArray, set_value("productcategoryID"), "id='productcategoryID' class='form-control select2'");
                    ?>
                </div>

                <div class="col-sm-3 form-group" >
    							<label for="report details" class="control-label">
                    <?=$this->lang->line("purchasereport_reportdetails")?> <span class="text-red">*</span>
                  </label>

    							<?php
    								$reportDetailsArray = array("" => $this->lang->line('productpurchasereport_please_select'), "transaction" => $this->lang->line('purchasereport_transaction'), "summary" => $this->lang->line('purchasereport_summary'));
    								echo form_dropdown("reportdetails", $reportDetailsArray, set_value("reportdetails", ""), "id='reportdetails' class='form-control select2'");
    							?>
    						</div>

                <div class="col-sm-3 form-group" >
    							<label for="select items">
                    <?=$this->lang->line("purchasereport_select_columns")?>
                  </label>

    							<?php
                    $columnArray = array("month" => $this->lang->line("productpurchasereport_month"), "year" => $this->lang->line("productpurchasereport_year"), "term" => $this->lang->line("productpurchasereport_term"), "dayofweek" => $this->lang->line("productpurchasereport_dayofweek"), "warehouse" => $this->lang->line("productpurchasereport_warehouse"), "category" => $this->lang->line("productpurchasereport_category"));
                    echo form_dropdown("columns", $columnArray, set_value("columns"), "id='columns' class='form-control' multiple");
    							?>
    						</div>

                <div class="col-sm-3">
                    <button id="get_purchasereport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("productpurchasereport_submit")?></button>
                </div>

            </div>

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_productpurchasereport"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
<script type="text/javascript">

    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('productpurchasereport_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }
    $('.select2').select2();

    $('#fromdate').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        startDate:'<?=$schoolyearsessionobj->startingdate?>',
        endDate:'<?=$schoolyearsessionobj->endingdate?>',
    });

    $('#todate').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        startDate:'<?=$schoolyearsessionobj->startingdate?>',
        endDate:'<?=$schoolyearsessionobj->endingdate?>',
    });

    $('#columns').multiselect({
      enableFiltering: true,
      includeSelectAllOption: true
    });

    $(".multiselect-container").css("position", "relative");

    $(document).bind('click', '#fromdate, #todate', function() {
        $('#fromdate').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            startDate:'<?=$schoolyearsessionobj->startingdate?>',
            endDate:'<?=$schoolyearsessionobj->endingdate?>',
        });

        $('#todate').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            startDate:'<?=$schoolyearsessionobj->startingdate?>',
            endDate:'<?=$schoolyearsessionobj->endingdate?>',
        });
    });

    $('#reportdetails').change(function() {
        var type = $(this).val();
        var columns = <?php echo json_encode($columnArray); ?>;
        if(type == "transaction") {
          $("#columns").html('');
          $.each(columns, function(key, value) {
              if(key != "warehouse" && key != "category")
                  $('#columns').append($('<option>', { value : key }).text(value));
          });
        } else if(type == "summary") {
          $("#columns").html('');
          $.each(columns, function(key, value) {
              $('#columns').append($('<option>', { value : key }).text(value));
          });
        }
        $('#columns').multiselect('rebuild');
    });

    $('#get_purchasereport').click(function() {
        var productID = $('#productID').val();
        var productname = $('#productname').val();
        var productsupplierID = $('#productsupplierID').val();
        var productwarehouseID = $('#productwarehouseID').val();
        var productcategoryID = $('#productcategoryID').val();
        var reportdetails = $('#reportdetails').val();
        var reference_no = $('#reference_no').val();
        var fromdate = $('#fromdate').val();
        var todate   = $('#todate').val();
        var columns = $("#columns").val();

        var field = {
            'productID': productID,
            'productname': productname,
            'productsupplierID': productsupplierID,
            'productwarehouseID': productwarehouseID,
            'productcategoryID': productcategoryID,
            'reportdetails': reportdetails,
            'reference_no': reference_no,
            'fromdate': fromdate,
            'todate': todate,
            'columns': columns
        };

        makingPostDataPreviousofAjaxCall(field);
    });

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('purchasereport/getPurchaseReport')?>",
            data: passData,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        if(response.status) {
            $('#load_productpurchasereport').html(response.render);
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }
        } else {
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }

            for (var key in response) {
                if (response.hasOwnProperty(key)) {
                    $('#'+key).parent().addClass('has-error');
                }
            }
        }
    }

</script>
