
<div class="row">
  <div class="col-sm-3">
      <div class="box">
          <div class="box-header">
              <h3 class="box-title"><i class="fa fa-calculator"></i> <?=$this->lang->line('panel_title')?></h3>
          </div><!-- /.box-header -->
          <div class="box-body">
              <form role="form" method="post" enctype="multipart/form-data" id="stockAdjustDataForm">

                <div class="productwarehouseDiv form-group <?=form_error('fromproductwarehouseID') ? 'has-error' : '' ?>" >
                    <label for="fromproductwarehouseID">
                        <?=$this->lang->line("stock_warehouse")?> <span class="text-red">*</span>
                    </label>
                    <?php
                        $productwarehouseArray = array(0 => $this->lang->line("stock_select_warehouse"));
                        if(customCompute($productwarehouses)) {
                            foreach ($productwarehouses as $productwarehouse) {
                                $productwarehouseArray[$productwarehouse->productwarehouseID] = $productwarehouse->productwarehousename;
                            }
                        }
                        echo form_dropdown("fromproductwarehouseID", $productwarehouseArray, set_value("fromproductwarehouseID"), "id='fromproductwarehouseID' class='form-control select2'");
                    ?>
                    <span class="text-red">
                        <?php echo form_error('fromproductwarehouseID'); ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="memo">
                        <?=$this->lang->line("stock_memo")?>
                    </label>
                    <textarea id="memo" name="memo" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <input id="adjustStockButton" type="button" class="btn btn-success" value="<?=$this->lang->line("adjust_title")?>" >
                </div>
              </form>
          </div>
      </div>
  </div>

    <div class="col-sm-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa iniicon-product"></i> <?=$this->lang->line('stock_product_list')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("stock/index")?>"><?=$this->lang->line('menu_stock')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_adjust')?> <?=$this->lang->line('menu_stock')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group <?=form_error('productID') ? 'has-error' : '' ?>" >
                                <label for="productID" class="control-label">
                                    <?=$this->lang->line("stock_product")?> <span class="text-red">*</span>
                                </label>
                                <?php
                                    $productArray = array('0' => $this->lang->line("stock_select_product"));
                                    foreach ($products as $product) {
                                        $productArray[$product->productID] = $product->productname;
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
                    <table class="table table-bordered feetype-style" style="font-size: 16px;">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-4"><?=$this->lang->line('stock_product')?></th>
                                <th class="col-sm-1" ><?=$this->lang->line('stock_available')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('stock_quantity')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('stock_cost')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody id="productList">
                        </tbody>

                        <tfoot id="productListFooter">
                            <tr>
                                <td colspan="3" style="font-weight: bold"><?=$this->lang->line('stock_total')?></td>
                                <td id="totalAmount" style="font-weight: bold">0.00</td>
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

    function getRandomInt() {
        return Math.floor(Math.random() * Math.floor(9999999999999999));
      }

  	function productItemDesign(productID, productText) {
      var productwarehouseID = $('#fromproductwarehouseID').val();
      var productpurchasequintity = <?=$productpurchasequintity?>;
      var productsalequintity = <?=$productsalequintity?>;
      var permission = <?= (permissionChecker('stock_adjust') != 1) ? 0 : 1; ?>;
      var randID = getRandomInt();
      if($('#productList tr:last').text() == '') {
          var lastTdNumber = 0;
      } else {
          var lastTdNumber = $("#productList tr:last td:eq(0)").text();
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

      lastTdNumber = parseInt(lastTdNumber);
      lastTdNumber++;
      var available = parseInt(productpurchasequintityinfo.quantity) - parseInt(productsalequintityinfo.quantity);
      if(available <= 0) {
        return "out of stock";
      }

      var text = '<tr id="tr_'+randID+'" adjustproductID="'+productID+'">';
          text += '<td>';
              text += lastTdNumber;
          text += '</td>';

          text += '<td>';
              text += productText;
          text += '</td>';

          text += '<td>';
                text += available;
          text += '</td>';

          text += '<td>';
              text += ('<input type="number" class="form-control change-amount" id="td_amount_id_'+randID+'" data-amount-id="'+randID+'">');
          text += '</td>';

          text += '<td class="cost"></td>';

          text += '<td>';
              text += ('<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="product_'+randID+'" data-product-id="'+randID+'"><i class="fa fa-trash-o"></i></a>');
          text += '</td>';
      text += '</tr>';

      return text;
    }

  	$('#productID').change(function(e) {
        var productID   = $(this).val();
        if(productID != 0) {
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
              $('select.productwarehouse').select2();
            }
        }
    });

    function isObjectEmpty(obj) {
        return Object.keys(obj).length === 0;
    }

    function currencyConvert(data) {
        return data.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }

    function totalInfo() {
        var totalAmount = 0;

        $('#productList tr').each(function(index, value) {
            if($(this).children().eq(3).html() != '' && $(this).children().eq(3).html() != null && $(this).children().eq(3).html() != '.') {
                var amount = parseFloat($(this).children().eq(3).html());
                totalAmount += amount;
            }
        });
        $('#totalAmount').text(currencyConvert(totalAmount));
    }

    $(document).on('keyup', '.change-amount', function() {
        var amount =  $(this).val();
        var amountID = $(this).attr('data-amount-id');
        var productbuyingprices = <?= json_encode($productbuyingprices) ?>;

        var tr = $(this).closest('tr');
        var productID = tr.attr('adjustproductID');
        var cost = amount * productbuyingprices[productID];
        tr.find('.cost').html(cost);
        totalInfo();
    });

    $(document).on('click', '.deleteBtn', function(er) {
          er.preventDefault();
          var productID = $(this).attr('data-product-id');
          $(this).closest('tr').remove();

          var i = 1;
          $('#productList tr').each(function(index, value) {
              $(this).children().eq(0).text(i);
              i++;
          });
      });

      $(document).on('click', '#adjustStockButton', function() {
            var error=0;
            var field = {
                'fromproductwarehouseID'                : $('#productwarehouseID').val(),
                'memo'                                  : $('#memo').val(),
            };

            if(field['productwarehouseID'] === '0') {
                $('.productwarehouseDiv').addClass('has-error');
                error++;
            } else {
                $('.productwarehouseDiv').removeClass('has-error');
            }

            var productitems = $('tr[id^=tr_]').map(function(){
                return { productID : $(this).attr('adjustproductID'), amount: $(this).children().eq(3).children().val()};
            }).get();

            if (typeof productitems == 'undefined' || productitems.length <= 0) {
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

            productitems = JSON.stringify(productitems);

            if(error === 0) {
                $(this).attr('disabled', 'disabled');
                var formData = new FormData($('#stockAdjustDataForm')[0]);
                formData.append("productitems", productitems);
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
                url: "<?=base_url('stock/saveadjustment')?>",
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
                window.location = "<?=base_url("stock/index")?>";
            } else {
                $('#adjustStockButton').removeAttr('disabled');
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
