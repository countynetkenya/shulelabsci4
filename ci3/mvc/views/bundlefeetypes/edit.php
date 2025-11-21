
<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-invoice"></i> <?=$this->lang->line('panel_title')?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form role="form" method="post" enctype="multipart/form-data" id="bundlefeetypeDataForm">

                  <?php
                        if(form_error('bundlefeetypes'))
                            echo "<div class='bundlefeetypesDiv form-group has-error' >";
                        else
                            echo "<div class='bundlefeetypesDiv form-group' >";
                    ?>
                        <label for="bundlefeetypes" class="control-label">
                            <?=$this->lang->line('feetypes_name')?> <span class="text-red">*</span>
                        </label>
                        <input type="text" class="form-control" id="bundlefeetypes" name="bundlefeetypes" value="<?=set_value('bundlefeetypes', $bundlefeetypes->bundlefeetypes)?>" >
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('bundlefeetypes'); ?>
                        </span>
                    </div>

          					<div class="form-group">
          					    <label for="note">
          							<?=$this->lang->line('feetypes_note')?>
          						</label>
          						<textarea id="note" name="note" class="form-control"><?=$bundlefeetypes->note?></textarea>
          					</div>

                    <input id="addBundleFeeTypeButton" type="button" class="btn btn-success" value="<?=$this->lang->line("update_feetype")?>" >
                </form>
            </div>
        </div>
    </div>


    <div class="col-sm-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-feetypes"></i> <?=$this->lang->line('invoice_feetype_list')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("bundlefeetypes/index")?>"><?=$this->lang->line('menu_bundlefeetypes')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_bundlefeetypes')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group <?=form_error('feetypeID') ? 'has-error' : '' ?>" >
                                <label for="feetypeID" class="control-label">
                                    <?=$this->lang->line("bundlefeetypes_feetype")?> <span class="text-red">*</span>
                                </label>
                                <?php
                                    $feetypeArray = array('0' => $this->lang->line("bundlefeetypes_select_feetype"));
                                    foreach ($feetypes as $feetype) {
                                        $feetypeArray[$feetype->feetypesID] = $feetype->feetypes;
                                    }
                                    echo form_dropdown("feetypeID", $feetypeArray, set_value("feetypeID"), "id='feetypeID' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('feetypeID'); ?>
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
                                <th class="col-sm-3"><?=$this->lang->line('bundlefeetypes_feetype')?></th>
                                <th class="col-sm-2" ><?=$this->lang->line('invoice_amount')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody id="feetypeList">
                          <?php
                              if(customCompute($bundlefeetype_feetypes)) {
                                  $i = 1;
                                  $totalAmount = 0;

                                  foreach ($bundlefeetype_feetypes as $feetype) {
                                      $randID = rand(0, 9999999999);

                                      $totalAmount += $feetype->amount;

                                      echo '<tr id="tr_'.$randID.'" invoicefeetypeID="'.$feetype->feetypesID.'" invoicebundlefeetypeID="'.$feetype->bundlefeetypesID.'">';
                                          echo '<td>';
                                              echo $i;
                                          echo '</td>';

                                          echo '<td>';
                                              echo isset($feetype->feetypes) ? $feetype->feetypes  : '';
                                          echo '</td>';

                                          echo '<td>';
                                              echo '<input type="text" class="form-control change-amount" id="td_amount_id_'.$randID.'" data-amount-id="'.$randID.'" value="'.$feetype->amount.'">';
                                          echo '</td>';

                                          /*echo '<td>';
                                              echo '<input type="text" class="form-control change-discount" id="td_discount_id_'.$randID.'" data-discount-id="'.$randID.'" value="'.$invoice->discount.'">';
                                          echo '</td>';

                                          echo '<td>';
                                              echo $subtotal;
                                          echo '</td>';

                                          echo '<td>';
                                              echo  '<input type="text" class="form-control change-paidamount" id="td_paidamount_id_'.$randID.'" data-paidamount-id="'.$randID.'" readonly="readonly">';
                                          echo '</td>';*/

                                          echo '<td>';
                                              echo '<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="feetype_'.$randID.'" data-feetype-id="'.$randID.'"><i class="fa fa-trash-o"></i></a>';
                                          echo '</td>';
                                      echo '</tr>';
                                      $i++;
                                  }
                              }
                          ?>
                        </tbody>

                        <tfoot id="feetypeListFooter">
                            <tr>
                                <td colspan="2" style="font-weight: bold"><?=$this->lang->line('invoice_total')?></td>
                                <td id="totalAmount" style="font-weight: bold"><?=$totalAmount?></td>
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
    function dd(data) {
        console.log(data);
    }

    $('.select2').select2();

    function getRandomInt() {
      return Math.floor(Math.random() * Math.floor(9999999999999999));
    }

    function productItemDesign(feetypeID, productText) {
        var randID = getRandomInt();
        if($('#feetypeList tr:last').text() == '') {
            var lastTdNumber = 0;
        } else {
            var lastTdNumber = $("#feetypeList tr:last td:eq(0)").text();
        }

        lastTdNumber = parseInt(lastTdNumber);
        lastTdNumber++;

        var text = '<tr id="tr_'+randID+'" invoicefeetypeID="'+feetypeID+'">';
            text += '<td>';
                text += lastTdNumber;
            text += '</td>';

            text += '<td>';
                text += productText;
            text += '</td>';

            text += '<td>';
                text += ('<input type="text" class="form-control change-amount" id="td_amount_id_'+randID+'" data-amount-id="'+randID+'">');
            text += '</td>';

            text += '<td>';
                text += ('<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="feetype_'+randID+'" data-feetype-id="'+randID+'"><i class="fa fa-trash-o"></i></a>');
            text += '</td>';
        text += '</tr>';

        return text;
    }

    $('#feetypeID').change(function(e) {
        var feetypeID   = $(this).val();
        if(feetypeID != 0) {
            var feetypeText = $(this).find(":selected").text();
            var appendData  = productItemDesign(feetypeID, feetypeText);
            $('#feetypeList').append(appendData);
        }
    });

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

    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
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

    function floatChecker(value) {
        var val = value;
        if(isNumeric(val)) {
            return true;
        } else {
            return false;
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

    function parseSentenceForNumber(sentence) {
        var matches = sentence.replace(/,/g, '').match(/(\+|-)?((\d+(\.\d+)?)|(\.\d+))/);
        return matches && matches[0] || null;
    }

    function currencyConvert(data) {
        return data.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }

    var globaltotalamount = 0;
    var globaltotalsubtotal = 0;
    var globaltotalpaidamount = 0;
    function totalInfo() {
        var i = 1;
        var j = 1;

        var totalAmount = 0;
        var totalSubtotal = 0;
        var totalPaidAmount = 0;

        $('#feetypeList tr').each(function(index, value) {
            if($(this).children().eq(2).children().val() != '' && $(this).children().eq(2).children().val() != null && $(this).children().eq(2).children().val() != '.') {
                var amount = parseFloat($(this).children().eq(2).children().val());
                totalAmount += amount;
            }
        });
        globaltotalamount = totalAmount;
        $('#totalAmount').text(currencyConvert(totalAmount));

        $('#feetypeList tr').each(function(index, value) {
            var amount = parseFloat($(this).children().eq(2).children().val());
            var subtotal = 0;
            if(amount > 0) {
                subtotal = amount;
            }

            $(this).children().eq(4).text(subtotal);
            totalSubtotal += subtotal;
        });
        globaltotalsubtotal = totalSubtotal;
        $('#totalSubtotal').text(currencyConvert(totalSubtotal));

        $('#feetypeList tr').each(function(index, value) {
            if($(this).children().eq(5).children().val() != '' && $(this).children().eq(5).children().val() != null && $(this).children().eq(5).children().val() != '.') {
                var paidamount = parseFloat($(this).children().eq(5).children().val());
                totalPaidAmount += paidamount;
            }
        });
        globaltotalpaidamount = totalPaidAmount;
        $('#totalPaidAmount').text(currencyConvert(totalPaidAmount));

    }

    $(document).on('keyup', '.change-amount', function() {
        var amount =  toFixedVal($(this).val());
        var amountID = $(this).attr('data-amount-id');

        if(dotAndNumber(amount)) {
            if(amount.length > 15) {
                amount = lenChecker(amount, 15);
                $(this).val(amount);
            }

            if(amount != '' && amount != null) {
                $(this).val(amount);
                totalInfo();
            } else {
                totalInfo();
            }
        } else {
            var amount = parseSentenceForNumber(toFixedVal($(this).val()));
            $(this).val(amount);
        }
    });

    $(document).on('click', '.deleteBtn', function(er) {
        er.preventDefault();
        var feetypeID = $(this).attr('data-feetype-id');
        $('#tr_'+feetypeID).remove();

        var i = 1;
        $('#feetypeList tr').each(function(index, value) {
            $(this).children().eq(0).text(i);
            i++;
        });
        totalInfo();
    });

</script>

<script type="text/javascript">

    $(document).on('click', '#addBundleFeeTypeButton', function() {
        var error=0;
        var field = {
            'bundlefeetype'                : $('#bundlefeetypes').val(),
        };

        if(field['bundlefeetype'] === '') {
            $('.bundlefeetypesDiv').addClass('has-error');
            error++;
        } else {
            $('.bundlefeetypesDiv').removeClass('has-error');
        }

        var totalsubtotal = 0;
        var feetypeitems = $('tr[id^=tr_]').map(function(){
            if($(this).children().eq(2).children().val() != '' && $(this).children().eq(2).children().val() != null) {
                totalsubtotal += parseFloat($(this).children().eq(2).children().val());
            }

            return { feetypeID : $(this).attr('invoicefeetypeid'), amount: $(this).children().eq(2).children().val()};
        }).get();

        if (typeof feetypeitems == 'undefined' || feetypeitems.length <= 0) {
            error++;
            toastr["error"]('The fee type item is required.')
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

        feetypeitems = JSON.stringify(feetypeitems);

        if(error === 0) {
            $(this).attr('disabled', 'disabled');
            var formData = new FormData($('#bundlefeetypeDataForm')[0]);
            formData.append("feetypeitems", feetypeitems);
            formData.append("totalsubtotal", totalsubtotal);
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
            url: "<?=base_url('bundlefeetypes/edit/') . $bundlefeetypeID?>",
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
            window.location = "<?=base_url("bundlefeetypes/index")?>";
        } else {
            $('#addBundleFeeTypeButton').removeAttr('disabled');
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
