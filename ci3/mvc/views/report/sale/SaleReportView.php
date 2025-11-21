<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-productsalereport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_salereport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group col-sm-3" id="fromdateDiv">
                    <label><?=$this->lang->line("productsaleitemreport_fromdate")?></label>
                   <input class="form-control" type="text" name="fromdate" id="fromdate">
                </div>

                <div class="form-group col-sm-3" id="todateDiv">
                    <label><?=$this->lang->line("productsaleitemreport_todate")?></label>
                    <input class="form-control" type="text" name="todate" id="todate">
                </div>

                <div class="form-group col-sm-3" id="productIDDiv">
                    <label><?=$this->lang->line("productsaleitemreport_productID")?></label>
                    <input class="form-control" type="text" name="productID" id="productID">
                </div>

                <div class="form-group col-sm-3" id="productDiv">
                    <label><?=$this->lang->line("productsaleitemreport_product")?></label>
                    <?php
                    $productArray = array(0 => $this->lang->line("productsaleitemreport_please_select"));
                    if(customCompute($products)) {
                        foreach ($products as $product) {
                            $productArray[$product->productname] = $product->productname;
                        }
                    }
                    echo form_dropdown("productname", $productArray, set_value("productname"), "id='productname' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="productwarehouseDiv">
                    <label for="productwarehouseID"><?=$this->lang->line("productsaleitemreport_productwarehousename")?></label>
                    <?php
                    $warehouseArray = array(0 => $this->lang->line("productsaleitemreport_please_select"));
                    if(customCompute($warehouses)) {
                        foreach ($warehouses as $warehouse) {
                            $warehouseArray[$warehouse->productwarehouseID] = $warehouse->productwarehousename;
                        }
                    }
                    echo form_dropdown("productwarehouseID", $warehouseArray, set_value("productwarehouseID"), "id='productwarehouseID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="productcategoryDiv">
                    <label for="productcategoryID"><?=$this->lang->line("productsaleitemreport_productcategoryname")?></label>
                    <?php
                    $productcategoryArray = array(0 => $this->lang->line("productsaleitemreport_please_select"));
                    if(customCompute($productcategories)) {
                        foreach ($productcategories as $productcategory) {
                            $productcategoryArray[$productcategory->productcategoryID] = $productcategory->productcategoryname;
                        }
                    }
                    echo form_dropdown("productcategoryID", $productcategoryArray, set_value("productcategoryID"), "id='productcategoryID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="productsalecustomertypeDiv">
                    <label><?=$this->lang->line("productsaleitemreport_productsalecustomertype")?></label>
                    <?php
                    $usertypeArray = array(0 => $this->lang->line("productsaleitemreport_please_select"));
                    if(customCompute($usertypes)) {
                        foreach ($usertypes as $usertype) {
                            $usertypeArray[$usertype->usertypeID] = $usertype->usertype;
                        }
                    }
                    echo form_dropdown("productsalecustomertypeID", $usertypeArray, set_value("productsalecustomertypeID"), "id='productsalecustomertypeID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="productsalecustomerDiv">
                    <label for="productsalecustomerID"><?=$this->lang->line("productsaleitemreport_productsalecustomerName")?></label>
                    <?php
                    $productsalecustomerArray = array(0 => $this->lang->line("productsaleitemreport_please_select"));
                    echo form_dropdown("productsalecustomerID", $productsalecustomerArray, set_value("productsalecustomerID"), "id='productsalecustomerID' class='form-control select2'");
                    ?>
                </div>

                <div class="col-sm-3 form-group" >
    							<label for="report details" class="control-label">
                    <?=$this->lang->line("salereport_reportdetails")?> <span class="text-red">*</span>
                  </label>

    							<?php
    								$reportDetailsArray = array("" => $this->lang->line('productsaleitemreport_please_select'), "transaction" => $this->lang->line('salereport_transaction'), "summary" => $this->lang->line('salereport_summary'));
    								echo form_dropdown("reportdetails", $reportDetailsArray, set_value("reportdetails", ""), "id='reportdetails' class='form-control select2'");
    							?>
    						</div>

                <div class="col-sm-3 form-group" >
    							<label for="select items">
                    <?=$this->lang->line("productsaleitemreport_select_columns")?>
                  </label>

    							<?php
                    $columnArray = array("productsalecustomertype" => $this->lang->line("productsaleitemreport_productsalecustomertype"), "productsalecustomerName" => $this->lang->line("productsaleitemreport_productsalecustomerName"), "month" => $this->lang->line("productsaleitemreport_month"), "year" => $this->lang->line("productsaleitemreport_year"), "term" => $this->lang->line("productsaleitemreport_term"), "dayofweek" => $this->lang->line("productsaleitemreport_dayofweek"), "productwarehousename" => $this->lang->line("productsaleitemreport_productwarehousename"), "productcategoryname" => $this->lang->line("productsaleitemreport_productcategoryname"));
                    echo form_dropdown("columns", $columnArray, set_value("columns"), "id='columns' class='form-control' multiple");
    							?>
    						</div>

                <div class="col-sm-3">
                    <button id="get_salereport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("productsaleitemreport_submit")?></button>
                </div>
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_productsalereport"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
<script type="text/javascript">

    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('productsaleitemreport_hotline')?> : <?=$siteinfos->phone?></center>";
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

    $('#productsalecustomertypeID, #productsaleclassesID').change(function(event) {
        var productsalecustomertypeID = $('#productsalecustomertypeID').val();
        var productsaleclassesID = $('#productsaleclassesID').val();

        if(productsalecustomertypeID === '3') {
            $('#productsaleclassesDiv').removeClass('hide');
        } else {
            $('#productsaleclassesDiv').addClass('hide');
        }

        if(productsalecustomertypeID === '0') {
            $('#productID').html('<option value="0"><?=$this->lang->line('productsaleitemreport_please_select')?></option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('salereport/getuser')?>",
                data: {'productsalecustomertypeID' : productsalecustomertypeID, 'productsaleclassesID' : productsaleclassesID},
                dataType: "html",
                success: function(data) {
                    $('#productsalecustomerID').html(data);
                }
            });
        }
    });

    $('#reportdetails').change(function() {
        var type = $(this).val();
        var columns = <?php echo json_encode($columnArray); ?>;
        if(type == "transaction") {
          $("#columns").html('');
          $.each(columns, function(key, value) {
              if(key != "productwarehousename" && key != "productcategoryname")
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

    $('#get_salereport').click(function() {
        var productID = $('#productID').val();
        var productname = $('#productname').val();
        var productwarehouseID = $('#productwarehouseID').val();
        var productcategoryID = $('#productcategoryID').val();
        var productsalecustomertypeID = $('#productsalecustomertypeID').val();
        var productsalecustomerID = $('#productsalecustomerID').val();
        var reportdetails = $('#reportdetails').val();
        var fromdate = $('#fromdate').val();
        var todate   = $('#todate').val();
        var columns = $("#columns").val();
        var error = 0;

        var field = {
            'productID': productID,
            'productname': productname,
            'productwarehouseID': productwarehouseID,
            'productcategoryID': productcategoryID,
            'productsalecustomertypeID': productsalecustomertypeID,
            'productsalecustomerID': productsalecustomerID,
            'reportdetails': reportdetails,
            'fromdate': fromdate,
            'todate': todate,
            'columns': columns
        };

        if(fromdate != '' && todate == '') {
            error++;
            $('#todateDiv').addClass('has-error');
        } else{
            $('#todateDiv').removeClass('has-error');
        }

        if(fromdate == '' && todate != '') {
            error++;
            $('#fromdateDiv').addClass('has-error');
        } else {
            $('#fromdateDiv').removeClass('has-error');
        }

        if(fromdate != '' && todate != '') {
            var fromdate = fromdate.split('-');
            var todate = todate.split('-');
            var newfromdate = new Date(fromdate[2], fromdate[1]-1, fromdate[0]);
            var newtodate   = new Date(todate[2], todate[1]-1, todate[0]);

            if(newfromdate.getTime() > newtodate.getTime()) {
                error++;
                $('#todateDiv').addClass('has-error');
            } else {
                $('#todateDiv').removeClass('has-error');
            }
        }

        if(error == 0 ) {
            makingPostDataPreviousofAjaxCall(field);
        }
    });

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('salereport/getSaleReport')?>",
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
            $('#load_productsalereport').html(response.render);
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
