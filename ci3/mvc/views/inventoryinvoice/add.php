<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa iniicon-productsale"></i> <?=$this->lang->line('panel_title')?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form role="form" method="post" enctype="multipart/form-data" id="productPurchaseDataForm">
                    <div class="productwarehouseDiv form-group <?=form_error('productwarehouseID') ? 'has-error' : '' ?>" >
                        <label for="productwarehouseID">
                            <?=$this->lang->line("inventoryinvoice_warehouse")?> <span class="text-red">*</span>
                        </label>
                        <?php
                            $productwarehouseArray = array(0 => $this->lang->line("inventoryinvoice_select_warehouse"));
                            if(customCompute($productwarehouses)) {
                                foreach ($productwarehouses as $productwarehouse) {
                                    $productwarehouseArray[$productwarehouse->productwarehouseID] = $productwarehouse->productwarehousename;
                                }
                            }
                            echo form_dropdown("productwarehouseID", $productwarehouseArray, set_value("productwarehouseID"), "id='productwarehouseID' class='form-control select2'");
                        ?>
                        <span class="text-red">
                            <?php echo form_error('productwarehouseID'); ?>
                        </span>
                    </div>


                    <div class="productsaleclassesDiv form-group <?=form_error('productsaleclassesID') ? 'has-error' : '' ?>" >
                        <label for="productsaleclassesID">
                            <?=$this->lang->line("inventoryinvoice_classes")?> <span class="text-red">*</span>
                        </label>
                        <?php
                            $classesArray = array(0 => $this->lang->line("inventoryinvoice_select_classes"));
                            if(customCompute($classes)) {
                                foreach ($classes as $classa) {
                                    $classesArray[$classa->classesID] = $classa->classes;
                                }
                            }
                            echo form_dropdown("productsaleclassesID", $classesArray, set_value("productsaleclassesID"), "id='productsaleclassesID' class='form-control select2'");
                        ?>
                        <span class="text-red">
                            <?php echo form_error('productsaleclassesID'); ?>
                        </span>
                    </div>

                    <div class="productsalecustomerDiv form-group <?=form_error('productsalecustomerID') ? 'has-error' : '' ?>" >
                        <label for="productsalecustomerID">
                            <?=$this->lang->line("inventoryinvoice_user")?> <span class="text-red">*</span>
                        </label>
                        <?php
                            $productwarehouseArray = array(0 => $this->lang->line("inventoryinvoice_select_user"));
                            echo form_dropdown("productsalecustomerID", $productwarehouseArray, set_value("productsalecustomerID"), "id='productsalecustomerID' class='form-control select2'");
                        ?>
                        <span class="text-red">
                            <?php echo form_error('productsalecustomerID'); ?>
                        </span>
                    </div>

                    <div class="productsalereferencenoDiv form-group <?=form_error('productsalereferenceno') ? 'has-error' : '' ?>" >
                        <label for="productsalereferenceno">
                            <?=$this->lang->line("inventoryinvoice_referenceno")?> <span class="text-red">*</span>
                        </label>
                            <input type="text" class="form-control" id="productsalereferenceno" name="productsalereferenceno" value="<?=set_value('productsalereferenceno')?>" >
                        <span class="text-red">
                            <?php echo form_error('productsalereferenceno'); ?>
                        </span>
                    </div>


                    <div class="productsaledateDiv form-group <?=form_error('productsaledate') ? 'has-error' : '' ?>" >
                        <label for="productsaledate">
                            <?=$this->lang->line("inventoryinvoice_date")?> <span class="text-red">*</span>
                        </label>
                            <input type="text" class="form-control" id="productsaledate" name="productsaledate" value="<?=set_value('productsaledate')?>" >
                        <span class="text-red">
                            <?php echo form_error('productsaledate'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('productsalefile') ? 'has-error' : '' ?>" >
                        <label for="productsalefile">
                            <?=$this->lang->line("inventoryinvoice_file")?>
                        </label>
                        <div class="input-group image-preview">
                            <input type="text" class="form-control image-preview-filename" disabled="disabled">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                    <span class="fa fa-remove"></span>
                                    <?=$this->lang->line('productsale_clear')?>
                                </button>
                                <div class="btn btn-success image-preview-input">
                                    <span class="fa fa-repeat"></span>
                                    <span class="image-preview-input-title">
                                    <?=$this->lang->line('productsale_browse')?></span>
                                    <input type="file" name="productsalefile"/>
                                </div>
                            </span>
                        </div>
                        <span class="text-red">
                            <?php echo form_error('productsale_file'); ?>
                        </span>
                    </div>

                    <div class="productsaledescriptionDiv form-group <?=form_error('productsaledescription') ? 'has-error' : '' ?>" >
                        <label for="productsaledescription">
                            <?=$this->lang->line("inventoryinvoice_description")?>
                        </label>
                        <textarea class="form-control" style="resize:none;" id="productsaledescription" name="productsaledescription"><?=set_value('productsaledescription')?></textarea>
                        <span class="text-red">
                            <?php echo form_error('productsaledescription'); ?>
                        </span>
                    </div>

                    <input id="addPurchaseButton" type="button" class="btn btn-success" value="<?=$this->lang->line("add_inventoryinvoice")?>" >
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa iniicon-productpurchaseitem"></i> <?=$this->lang->line('productsale_saleitem')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("inventoryinvoice/index")?>"><?=$this->lang->line('menu_productsale')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('menu_productsale')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group <?=form_error('productcategoryID') ? 'has-error' : '' ?>" >
                                <label for="productcategoryID" class="control-label">
                                    <?=$this->lang->line("inventoryinvoice_category")?>
                                </label>
                                <?php
                                    $productcategoryArray = array(0 => $this->lang->line("inventoryinvoice_select_category"));
                                    if(customCompute($productcategorys)) {
                                        foreach ($productcategorys as $productcategory) {
                                            $productcategoryArray[$productcategory->productcategoryID] = $productcategory->productcategoryname;
                                        }
                                    }
                                    echo form_dropdown("productcategoryID", $productcategoryArray, set_value("productcategoryID"), "id='productcategoryID' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('productcategoryID'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group <?=form_error('productID') ? 'has-error' : '' ?>" >
                                <label for="productID" class="control-label">
                                    <?=$this->lang->line("inventoryinvoice_product")?> <span class="text-red">*</span>
                                </label>
                                <?php
                                    $productArray = array(0 => $this->lang->line("inventoryinvoice_select_product"));
                                    if(customCompute($products)) {
                                        foreach ($products as $product) {
                                            $productArray[$product->productID] = $product->productname;
                                        }
                                    }
                                    echo form_dropdown("productID", $productArray, set_value("productID"), "id='productID' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('productID'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered product-style" style="font-size: 16px;">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('inventoryinvoice_product')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('inventoryinvoice_billing_type')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('inventoryinvoice_nonbillable_reason')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('inventoryinvoice_tax_code_override')?></th>
                                <th class="col-sm-1" ><?=$this->lang->line('inventoryinvoice_unit_price')?></th>
                                <th class="col-sm-1" ><?=$this->lang->line('inventoryinvoice_available')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('inventoryinvoice_quantity')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('inventoryinvoice_subtotal')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody id="productList">
                        </tbody>

                        <tfoot id="productListFooter">
                            <tr>
                                <td colspan="6" style="font-weight: bold"><?=$this->lang->line('productsale_total')?></td>
                                <td id="totalQuantity" style="font-weight: bold">0.00</td>
                                <td id="totalSubtotal" style="font-weight: bold">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('.select2').select2();
    $('#productsaledate').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        startDate:'<?=$schoolyearsessionobj->startingdate?>',
        endDate:'<?=$schoolyearsessionobj->endingdate?>',
    });

    var BILLABLE_LABEL = <?=json_encode($this->lang->line('inventoryinvoice_billable'))?>;
    var NON_BILLABLE_LABEL = <?=json_encode($this->lang->line('inventoryinvoice_non_billable'))?>;
    var NON_BILLABLE_REASON_LABEL = <?=json_encode($this->lang->line('inventoryinvoice_nonbillable_reason'))?>;
    var TAX_CODE_LABEL = <?=json_encode($this->lang->line('inventoryinvoice_tax_code_override'))?>;
    var NON_BILLABLE_REASON_ERROR = <?=json_encode($this->lang->line('inventoryinvoice_nonbillable_reason_required'))?>;

    $(function() {
        // Create the close button
        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");
        // Set the popover default content
        $('.image-preview').popover({
            trigger:'manual',
            html:true,
            title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
            content: "There's no image",
            placement:'bottom'
        });
        // Clear event
        $('.image-preview-clear').click(function(){
            $('.image-preview').attr("data-content","").popover('hide');
            $('.image-preview-filename').val("");
            $('.image-preview-clear').hide();
            $('.image-preview-input input:file').val("");
            $(".image-preview-input-title").text("<?=$this->lang->line('productsale_browse')?>");
        });
        // Create the preview image
        $(".image-preview-input input:file").change(function (){
            var file = this.files[0];
            var reader = new FileReader();
            // Set preview image into the popover data-content
            reader.onload = function (e) {
                $(".image-preview-input-title").text("<?=$this->lang->line('productsale_browse')?>");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);
            }
            reader.readAsDataURL(file);
        });
    });

    function getRandomInt() {
      return Math.floor(Math.random() * Math.floor(9999999999999999));
    }

    function productItemDesign(productID, productText) {
        var productwarehouseID = $('#productwarehouseID').val();
        var productpurchasequintity = <?=$productpurchasequintity?>;
        var productsalequintity = <?=$productsalequintity?>;

        var productobj = <?=$productobj?>;
        var randID = getRandomInt();
        if($('#productList tr:last').text() == '') {
            var lastTdNumber = 0;
        } else {
            var lastTdNumber = $("#productList tr:last td:eq(0)").text();
        }

        if(productwarehouseID === '0') {
            $('.productwarehouseDiv').addClass('has-error');
        } else {
            $('.productwarehouseDiv').removeClass('has-error');
        }

        if(typeof(productpurchasequintity) == 'object') {
            if(!isObjectEmpty(productpurchasequintity) && typeof(productpurchasequintity[productwarehouseID]) == 'object' && typeof(productpurchasequintity[productwarehouseID][productID]) == 'object') {
                var productpurchasequintityinfo = productpurchasequintity[productwarehouseID][productID];
            } else {
                productpurchasequintityinfo = {'quantity' : '0', 'productID' : productID, 'productwarehouseID' : productwarehouseID};
            }
        }

        if(typeof(productsalequintity) == 'object') {
            if(!isObjectEmpty(productsalequintity) && typeof(productsalequintity[productwarehouseID]) == 'object' && typeof(productsalequintity[productwarehouseID][productID]) == 'object') {
                var productsalequintityinfo = productsalequintity[productwarehouseID][productID];
            } else {
                productsalequintityinfo = {'quantity' : '0', 'productID' : productID, 'productwarehouseID' : productwarehouseID};
            }
        }

        if(typeof(productobj) == 'object') {
            if(typeof(productobj[productID]) == 'object') {
                var productobjinfo = productobj[productID];
            } else {
                productobjinfo = {'productID' : productID, 'productbuyingprice' : '0', 'productsellingprice' : '0'};
            }
        }

        lastTdNumber = parseInt(lastTdNumber);
        lastTdNumber++;
        var available = parseInt(productpurchasequintityinfo.quantity) - parseInt(productsalequintityinfo.quantity);

        if(available <= 0) {
          return "out of stock";
        }

        var basePrice = parseFloat(productobjinfo.productsellingprice) || 0;
        var defaultBilling = parseInt(productobjinfo.is_billable_default, 10) === 0 ? 'NON_BILLABLE' : 'BILLABLE';
        var text = '<tr id="tr_'+randID+'" saleproductid="'+productID+'" data-default-price="'+basePrice.toFixed(2)+'" data-last-billable-price="'+basePrice.toFixed(2)+'">';
            text += '<td class="line-index">'+ lastTdNumber +'</td>';
            text += '<td>'+ productText +'</td>';
            text += '<td>' +
                '<select class="form-control billing-type" data-productprice-id="'+randID+'">' +
                    '<option value="BILLABLE"'+(defaultBilling==='BILLABLE' ? ' selected' : '')+'>'+BILLABLE_LABEL+'</option>' +
                    '<option value="NON_BILLABLE"'+(defaultBilling==='NON_BILLABLE' ? ' selected' : '')+'>'+NON_BILLABLE_LABEL+'</option>' +
                '</select>' +
            '</td>';
            text += '<td>' +
                '<input type="text" class="form-control nonbillable-reason" placeholder="'+NON_BILLABLE_REASON_LABEL+'" disabled>' +
            '</td>';
            text += '<td>' +
                '<input type="text" class="form-control tax-code-override" placeholder="'+TAX_CODE_LABEL+'">' +
            '</td>';
            text += '<td>' +
                '<input type="text" class="form-control change-productprice unit-price" id="productunitprice_'+randID+'" value="'+basePrice.toFixed(2)+'" data-productprice-id="'+randID+'">' +
            '</td>';
            text += '<td class="available-qty">'+ available +'</td>';
            text += '<td>' +
                '<input type="text" class="form-control change-productquantity quantity-input" id="productquantity_'+randID+'" max="'+ (available) +'"  data-productquantity-id="'+randID+'">' +
            '</td>';
            text += '<td class="line-subtotal" id="producttotal_'+randID+'">0.00</td>';
            text += '<td>' +
                '<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="productaction_'+randID+'" data-productaction-id="'+randID+'"><i class="fa fa-trash-o"></i></a>' +
            '</td>';
        text += '</tr>';

        return text;
    }

    $('#productsaleclassesID').change(function(event) {
        var productsaleclassesID = $('#productsaleclassesID').val();

        $.ajax({
            type: 'POST',
            url: "<?=base_url('inventoryinvoice/getuser')?>",
            data: {'productsalecustomertypeID' : 3, 'productsaleclassesID' : productsaleclassesID},
            dataType: "html",
            success: function(data) {
                $('#productsalecustomerID').html(data);
            }
        });
    });

    $('#productcategoryID').change(function(event) {
        var productcategoryID = $(this).val();
        if(productcategoryID === '0') {
            $('#productID').html('<option value="0"><?=$this->lang->line('productsale_select_product')?></option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('inventoryinvoice/getproductsale')?>",
                data: "productcategoryID=" + productcategoryID,
                dataType: "html",
                success: function(data) {
                    $('#productID').html(data);
                }
            });
        }
    });

    $('#productID').change(function(e) {
        var productID = $(this).val();
        if(productID!=0) {
            var productText = $(this).find(":selected").text();
            var appendData  = productItemDesign(productID, productText);
            if(appendData == "out of stock") {
              toastr["error"](appendData)
              toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": false,
                  "positionClass": "toast-top-right",
                  "preventDuplicates": false,
                  "onclick": null,
                  "showDuration": "500",
                  "hideDuration": "500",
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              }
            } else {
              $('#productList').append(appendData);
              var $newRow = $('#productList tr:last');
              initializeRow($newRow);
              updateRowIndexes();
            }
        }
    });

    function isObjectEmpty(obj) {
        return Object.keys(obj).length === 0;
    }

    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function floatChecker(value) {
        var val = value;
        if(isNumeric(val)) {
            return true;
        } else {
            return false;
        }
    }

    function parseSentenceForNumber(sentence) {
        var matches = sentence.replace(/,/g, '').match(/(\+|-)?((\d+(\.\d+)?)|(\.\d+))/);
        return matches && matches[0] || null;
    }

    function getRandomInt() {
      return Math.floor(Math.random() * Math.floor(9999999999999999));
    }


    function currencyConvert(data) {
        var num = parseFloat(data);
        if(!isFinite(num)) {
            num = 0;
        }
        return num.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }

    function dotAndNumber(data) {
        var retArray = [];
        var fltFlag = true;
        if(data.length > 0) {
            for(var i = 0; i <= (data.length-1); i++) {
                if(i == 0 && data.charAt(i) == '.') {
                    fltFlag = false;
                    retArray.push(true);
                } else {
                    if(data.charAt(i) == '.' && fltFlag == true) {
                        retArray.push(true);
                        fltFlag = false;
                    } else {
                        if(isNumeric(data.charAt(i))) {
                            retArray.push(true);
                        } else {
                            retArray.push(false);
                        }
                    }

                }
            }
        }

        if(jQuery.inArray(false, retArray) ==  -1) {
            return true;
        }
        return false;
    }

    function sentanceLengthRemove(sentence) {
        sentence = sentence.toString();
        sentence = sentence.slice(0, -1);
        sentence = parseFloat(sentence);
        return sentence;
    }

    function toFixedVal(x) {
      if (Math.abs(x) < 1.0) {
        var e = parseFloat(x.toString().split('e-')[1]);
        if (e) {
            x *= Math.pow(10,e-1);
            x = '0.' + (new Array(e)).join('0') + x.toString().substring(2);
        }
      } else {
        var e = parseFloat(x.toString().split('+')[1]);
        if (e > 20) {
            e -= 20;
            x /= Math.pow(10,e);
            x += (new Array(e+1)).join('0');
        }
      }
      return x;
    }

    function lenCheckerWithoutParseFloat(data, len) {
        var retdata = 0;
        var lencount = 0;
        if(data.length > len) {
            lencount = (data.length - len);
            data = data.toString();
            data = data.slice(0, -lencount);
            retdata = data;
        } else {
            retdata = data;
        }

        return retdata;
    }

    $(document).on('keyup', '#productsalereferenceno', function() {
        var productsalereferenceno =  $(this).val();
        if(productsalereferenceno.length > 99) {
            productsalereferenceno = lenCheckerWithoutParseFloat(productsalereferenceno, 99);
            $(this).val(productsalereferenceno);
        }
    });

    $(document).on('keyup', '#productsalepaidreferenceno', function() {
        var productsalepaidreferenceno =  $(this).val();
        if(productsalepaidreferenceno.length > 99) {
            productsalepaidreferenceno = lenCheckerWithoutParseFloat(productsalepaidreferenceno, 99);
            $(this).val(productsalepaidreferenceno);
        }
    });

    var globalsubtotal = 0;
    function getRowQuantity($row) {
        var val = parseFloat($row.find('.change-productquantity').val());
        return isNaN(val) ? 0 : val;
    }

    function getRowPrice($row) {
        var val = parseFloat($row.find('.unit-price').val());
        return isNaN(val) ? 0 : val;
    }

    function getRowBilling($row) {
        return $row.find('.billing-type').val() || 'BILLABLE';
    }

    function setRowSubtotal($row, amount) {
        $row.data('subtotal', amount);
        $row.find('.line-subtotal').text(currencyConvert(amount));
    }

    function recalcRow($row) {
        var quantity = getRowQuantity($row);
        var price = getRowPrice($row);
        var billing = getRowBilling($row);
        var subtotal = billing === 'NON_BILLABLE' ? 0 : (quantity * price);
        setRowSubtotal($row, subtotal);
    }

    function updateRowIndexes() {
        var i = 1;
        $('#productList tr').each(function(){
            $(this).find('.line-index').text(i++);
        });
    }

    function updateTotals() {
        var totalQuantity = 0;
        var totalSubtotal = 0;
        $('#productList tr').each(function(){
            var $row = $(this);
            totalQuantity += getRowQuantity($row);
            totalSubtotal += parseFloat($row.data('subtotal')) || 0;
        });
        globalsubtotal = totalSubtotal;
        $('#totalQuantity').text(currencyConvert(totalQuantity));
        $('#totalSubtotal').text(currencyConvert(totalSubtotal));
    }

    function totalInfo() {
        updateTotals();
    }

    function totalUnitQuantity(gettrID, getproductID, getamount) {
        var totalQuantity = 0;
        var maxValue = 0;
        var quantity = 0;
        $('#productList tr').each(function() {
            var $row = $(this);
            var trID = $row.attr('id');
            var productID = $row.attr('saleproductid');
            if($row.find('.change-productquantity').val() !== '' && $row.find('.change-productquantity').val() !== null) {
                if((trID != gettrID) && (parseInt(productID) == parseInt(getproductID))) {
                    totalQuantity += getRowQuantity($row);
                }
            }
        });

        maxValue = parseFloat($('#'+gettrID).find('.change-productquantity').attr('max'));

        quantity = (maxValue - totalQuantity);
        if(getamount > quantity) {
            $('#'+gettrID).find('.change-productquantity').val(quantity);
        }
    }

    function totalUnitQuantityAmount(gettrID, getproductID, getamount) {
        var totalQuantity = 0;
        var maxValue = 0;
        var quantity = 0;
        $('#productList tr').each(function() {
            var $row = $(this);
            var trID = $row.attr('id');
            var productID = $row.attr('saleproductid');
            if($row.find('.change-productquantity').val() !== '' && $row.find('.change-productquantity').val() !== null) {
                if((trID != gettrID) && (parseInt(productID) == parseInt(getproductID))) {
                    totalQuantity += getRowQuantity($row);
                }
            }
        });

        maxValue = parseFloat($('#'+gettrID).find('.change-productquantity').attr('max'));
        quantity = (maxValue - totalQuantity);
        if(getamount > quantity) {
            return quantity;
        } else {
            return getamount;
        }
    }

    function lenChecker(data, len) {
        var retdata = 0;
        var lencount = 0;
        data = toFixedVal(data);
        if(data.length > len) {
            lencount = (data.length - len);
            data = data.toString();
            data = data.slice(0, -lencount);
            retdata = parseFloat(data);
        } else {
            retdata = parseFloat(data);
        }

        return toFixedVal(retdata);
    }

    function initializeRow($row) {
        var billing = getRowBilling($row);
        var priceInput = $row.find('.unit-price');
        var reasonInput = $row.find('.nonbillable-reason');
        if(billing === 'NON_BILLABLE') {
            reasonInput.prop('disabled', false);
            var lastPrice = $row.attr('data-last-billable-price');
            if(typeof lastPrice === 'undefined' || lastPrice === '') {
                $row.attr('data-last-billable-price', $row.attr('data-default-price'));
            }
            priceInput.val('0.00');
        } else {
            reasonInput.prop('disabled', true);
            var basePrice = $row.attr('data-default-price');
            if(typeof basePrice !== 'undefined') {
                priceInput.val(toFixedVal(basePrice));
            }
        }
        recalcRow($row);
        updateTotals();
    }

    $(document).on('change', '.billing-type', function() {
        var $row = $(this).closest('tr');
        var billing = getRowBilling($row);
        var reasonInput = $row.find('.nonbillable-reason');
        var priceInput = $row.find('.unit-price');
        if(billing === 'NON_BILLABLE') {
            reasonInput.prop('disabled', false);
            $row.attr('data-last-billable-price', priceInput.val());
            priceInput.val('0.00');
        } else {
            reasonInput.prop('disabled', true).css('border-color', '');
            var restore = $row.attr('data-last-billable-price');
            if(typeof restore !== 'undefined' && restore !== '') {
                priceInput.val(toFixedVal(restore));
            } else {
                var basePrice = $row.attr('data-default-price');
                if(typeof basePrice !== 'undefined') {
                    priceInput.val(toFixedVal(basePrice));
                }
            }
        }
        recalcRow($row);
        updateTotals();
    });

    $(document).on('input', '.nonbillable-reason', function() {
        var $input = $(this);
        if($.trim($input.val()) !== '') {
            $input.css('border-color', '');
        }
    });

    $(document).on('keyup', '.change-productprice', function() {
        var $input = $(this);
        var productPrice = toFixedVal($input.val());
        if(dotAndNumber(productPrice)) {
            if(productPrice.length > 15) {
                productPrice = lenChecker(productPrice, 15);
            }
            $input.val(productPrice);
            var $row = $input.closest('tr');
            if(getRowBilling($row) === 'BILLABLE') {
                $row.attr('data-last-billable-price', productPrice);
            }
            recalcRow($row);
            updateTotals();
        } else {
            var parsed = parseSentenceForNumber(productPrice);
            $input.val(parsed);
        }
    });

    $(document).on('keyup', '.change-productquantity', function() {
        var $input = $(this);
        var $row = $input.closest('tr');
        var gettrID = $row.attr('id');
        var getproductID = $row.attr('saleproductid');
        var productQuantity = toFixedVal($input.val());

        if(dotAndNumber(productQuantity)) {
            if(productQuantity !== '' && productQuantity !== null) {
                if(floatChecker(productQuantity)) {
                    totalUnitQuantity(gettrID, getproductID, productQuantity);
                    productQuantity = totalUnitQuantityAmount(gettrID, getproductID, productQuantity);
                    $input.val(productQuantity);
                    recalcRow($row);
                    updateTotals();
                }
            } else {
                totalUnitQuantity(gettrID, getproductID, productQuantity);
                recalcRow($row);
                updateTotals();
            }
        } else {
            var parsed = parseSentenceForNumber(toFixedVal($input.val()));
            $input.val(parsed);
            totalUnitQuantity(gettrID, getproductID, parsed);
        }
    });

    $(document).on('click', '.deleteBtn', function(e) {
        e.preventDefault();
        var productItemID = $(this).attr('data-productaction-id');
        $('#tr_'+productItemID).remove();
        updateRowIndexes();
        totalInfo();
        $('#productsalepaidamount').val('');
    });

    $(document).on('click', '#addPurchaseButton', function() {
        var error=0;
        var field = {
            'productsalecustomerID'                 : $('#productsalecustomerID').val(),
            'productwarehouseID'                    : $('#productwarehouseID').val(),
            'productsalereferenceno'                : $('#productsalereferenceno').val(),
            'productsaledate'                       : $('#productsaledate').val(),
            'productpurchasedescription'            : $('#productpurchasedescription').val(),
        };

        if (field['productsalecustomerID'] === '0') {
            $('.productsalecustomerDiv').addClass('has-error');
            error++;
        } else {
            $('.productsalecustomerDiv').removeClass('has-error');
        }

        if (field['productwarehouseID'] === '0') {
            $('.productwarehouseDiv').addClass('has-error');
            error++;
        } else {
            $('.productwarehouseDiv').removeClass('has-error');
        }

        if (field['productsalereferenceno'] == '') {
            $('.productsalereferencenoDiv').addClass('has-error');
            error++;
        } else {
            $('.productsalereferencenoDiv').removeClass('has-error');
        }

        if (field['productsaledate'] == '') {
            $('.productsaledateDiv').addClass('has-error');
            error++;
        } else {
            $('.productsaledateDiv').removeClass('has-error');
        }

        var productitem = $('tr[id^=tr_]').map(function(){
            var $row = $(this);
            return {
                productID : $row.attr('saleproductid'),
                unitprice: $row.find('.unit-price').val(),
                quantity : $row.find('.change-productquantity').val(),
                billingType: $row.find('.billing-type').val(),
                nonbillableReason: $row.find('.nonbillable-reason').val(),
                taxCode: $row.find('.tax-code-override').val(),
                defaultPrice: $row.attr('data-default-price')
            };
        }).get();

        if (typeof productitem == 'undefined' || productitem.length <= 0) {
            error++;
            toastr["error"]('The product item is required.')
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "500",
                "hideDuration": "500",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        }

        var nonbillableError = false;
        $('#productList tr').each(function(){
            var $row = $(this);
            if($row.find('.billing-type').val() === 'NON_BILLABLE') {
                var reason = $.trim($row.find('.nonbillable-reason').val());
                if(reason === '') {
                    nonbillableError = true;
                    $row.find('.nonbillable-reason').css('border-color', '#dd4b39');
                } else {
                    $row.find('.nonbillable-reason').css('border-color', '');
                }
            } else {
                $row.find('.nonbillable-reason').css('border-color', '');
            }
        });

        if(nonbillableError) {
            error++;
            toastr["error"](NON_BILLABLE_REASON_ERROR);
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "500",
                "hideDuration": "500",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        }

        productitem = JSON.stringify(productitem);

        if(error === 0) {
            $(this).attr('disabled', 'disabled');
            var formData = new FormData($('#productPurchaseDataForm')[0]);
            formData.append("productitem", productitem);
            formData.append("productsalepaidreferenceno", $('#productsalepaidreferenceno').val());
            formData.append("productsalepaidamount", $('#productsalepaidamount').val());
            formData.append("productsalepaidpaymentmethod", $('#productsalepaidpaymentmethod').val());
            formData.append("productsalepaidphone", $('#productsalepaidphone').val());
            formData.append("editID", 0);
            makingPostDataPreviousofAjaxCall(formData);
        }
    });


    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('inventoryinvoice/saveproductsale')?>",
            data: passData,
            async: true,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                errrorLoader(response);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    function errrorLoader(response) {
        if(response.status) {
            window.location = "<?=base_url("inventoryinvoice/index")?>";
        } else {
            $('#addPurchaseButton').removeAttr('disabled');
            $.each(response.error, function(index, val) {
                toastr["error"](val)
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
            });
        }
    }
</script>
