
<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-invoice"></i> <?=$this->lang->line('panel_title')?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form role="form" method="post" enctype="multipart/form-data" id="invoiceDataForm">

                    <div class="classesDiv form-group <?=form_error('classesID') ? 'has-error' : '' ?>" >
                        <label for="classesID">
                            <?=$this->lang->line("invoice_classesID")?> <span class="text-red">*</span>
                        </label>
                            <?php
                                $classesArray = array('0' => $this->lang->line("invoice_select_classes"));
                                if(customCompute($classes)) {
                                    foreach ($classes as $classa) {
                                        $classesArray[$classa->classesID] = $classa->classes;
                                    }
                                }
                                echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                            ?>
                        <span class="text-red">
                            <?php echo form_error('classesID'); ?>
                        </span>
                    </div>

					<div class="form-group <?=form_error('studentGroupID') ? ' has-error' : ''  ?>">
                        <label for="studentGroupID">
                            <?=$this->lang->line("student_studentgroup")?>
                        </label>
                            <?php
                                $groupArray = array(0 => $this->lang->line("student_select_studentgroup"));
                                if(customCompute($studentgroups)) {
                                    foreach ($studentgroups as $studentgroup) {
                                        $groupArray[$studentgroup->studentgroupID] = $studentgroup->group;
                                    }
                                }
                                echo form_dropdown("studentGroupID", $groupArray, set_value("studentGroupID"), "id='studentGroupID' class='form-control select2'");
                            ?>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('studentGroupID'); ?>
                        </span>
                    </div>

                    <div class="studentDiv form-group <?=form_error('studentID') ? 'has-error' : '' ?>" >
                        <label for="studentID">
                            <?=$this->lang->line("invoice_studentID")?> <span class="text-red">*</span>
                        </label>
                            <?php
                                $studentArray = array('0' => $this->lang->line("invoice_all_student"));
                                if(customCompute($students)) {
                                    foreach ($students as $student) {
                                        $studentArray[$student->studentID] = $student->name;
                                    }
                                }
                                echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
                            ?>
                        <span class="text-red">
                            <?php echo form_error('studentID'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('student_status'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="invoice_status">
                            <?=$this->lang->line("invoice_status")?> <span class="text-red">*</span>
                        </label>
                        <?php
                            $array = array(
                                '' => $this->lang->line('invoice_all'),
                                '1' => $this->lang->line('invoice_active'),
                                '0' => $this->lang->line('invoice_inactive')
                            );

                            echo form_dropdown("invoice_active ", $array, set_value("invoice_active"), "id='invoice_active' class='form-control select2'");
                        ?>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('student_status'); ?>
                        </span>
                    </div>

					          <div class="form-group <?=form_error('schooltermID') ? 'has-error' : '' ?>" >
                        <label for="schooltermID">
                            <?=$this->lang->line("invoice_schooltermID")?> <span class="text-red">*</span>
                        </label>
                            <?php
                                $termsArray = array('0' => $this->lang->line("invoice_select_schoolterm"));
                                if(customCompute($terms)) {
                                    foreach ($terms as $term) {
                                        $termsArray[$term->schooltermID] = $term->schooltermtitle;
                                    }
                                }
                                echo form_dropdown("schooltermID", $termsArray, set_value("schooltermID"), "id='schooltermID' class='form-control select2'");
                            ?>
                        <span class="text-red">
                            <?php echo form_error('schooltermID'); ?>
                        </span>
                    </div>

                    <div class="dateDiv form-group <?=form_error('date') ? 'has-error' : '' ?>" >
                        <label for="date">
                            <?=$this->lang->line("invoice_date")?> <span class="text-red">*</span>
                        </label>
                        <input type="text" class="form-control" id="date" name="date" value="<?=set_value('date')?>" >
                        <span class="text-red">
                            <?php echo form_error('date'); ?>
                        </span>
                    </div>

					<div class="form-group">
					    <label for="memo">
							Memo
						</label>
						<textarea id="memo" name="memo" class="form-control"></textarea>
					</div>
                    <?php if ($config['active'] == "1" && empty($config['sessionAccessToken'])) {?>
                    <div class="form-group">
                      <input onclick="oauth.loginPopup()" id="connectQuickBooksButton" type="button" class="btn btn-warning" value="<?=$this->lang->line("connect_quickbooks")?>" >
                    </div>
                  <?php } elseif ($config['active'] == "1" && now() > $config['sessionAccessTokenExpiry']) {?>
                    <div class="form-group">
                      <a href="<?=base_url("quickbooks/refreshToken")?>" class="btn btn-warning"><?=$this->lang->line("reconnect_quickbooks")?></a>
                    </div>
                    <?php }?>
                    <div class="form-group">
                      <input id="addInvoiceButton" type="button" class="btn btn-success" value="<?=$this->lang->line("add_invoice")?>" >
                    </div>
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
                    <li><a href="<?=base_url("invoice/index")?>"><?=$this->lang->line('menu_invoice')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('menu_invoice')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group <?=form_error('feetypeID') ? 'has-error' : '' ?>" >
                                <label for="feetypeID" class="control-label">
                                    <?=$this->lang->line("invoice_feetype")?>
                                </label>
                                <?php
                                    $feetypeArray = array('' => $this->lang->line("invoice_select_feetype"));
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

                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group <?=form_error('bundlefeetypeID') ? 'has-error' : '' ?>" >
                                <label for="bundlefeetypeID" class="control-label">
                                    <?=$this->lang->line("invoice_bundlefeetype")?>
                                </label>
                                <select id='bundlefeetypeID' class='form-control select2'>
                                  <option value=""><?=$this->lang->line("invoice_select_bundlefeetype")?></option>
                                   <?php foreach ($bundlefeetypes as $bundlefeetype) {?>
                                      <option value="<?=$bundlefeetype->bundlefeetypesID?>" data-total="<?=$bundlefeetype->total?>"><?=$bundlefeetype->bundlefeetypes?></option>
                                   <?php }?>
                                </select>
                                <span class="control-label">
                                    <?php echo form_error('bundlefeetypeID'); ?>
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
                                <th class="col-sm-3"><?=$this->lang->line('invoice_feetype')?></th>
                                <th class="col-sm-2" ><?=$this->lang->line('invoice_amount')?></th>
                                <!--<th class="col-sm-1" ><?=$this->lang->line('invoice_discount')?>(%)</th>
                                <th class="col-sm-2" ><?=$this->lang->line('invoice_subtotal')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('invoice_paid_amount')?></th>-->
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody id="feetypeList">
                        </tbody>

                        <tfoot id="feetypeListFooter">
                            <tr>
                                <td colspan="2" style="font-weight: bold"><?=$this->lang->line('invoice_total')?></td>
                                <td id="totalAmount" style="font-weight: bold">0.00</td>
                                <!--<td id="totalDiscount" style="font-weight: bold">0.00</td>
                                <td id="totalSubtotal" style="font-weight: bold">0.00</td>
                                <td id="totalPaidAmount" style="font-weight: bold">0.00</td>-->
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
    $('#date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        startDate:'<?=$schoolyearsessionobj->startingdate?>',
        endDate:'<?=$schoolyearsessionobj->endingdate?>',
    });

    $('#classesID').change(function(event) {
        var classesID = $(this).val();
		    var studentGroupID = $('#studentGroupID').val();

        if(classesID === '0') {
            $('#studentID').html('<option value="0"><?=$this->lang->line('invoice_all_student')?></option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('invoice/getstudent')?>",
                data: {'classesID' : classesID, 'studentGroupID' : studentGroupID},
                dataType: "html",
                success: function(data) {
                    $('#studentID').html(data);
                }
            });
        }
    });

	$('#studentGroupID').change(function(event) {
        var studentGroupID = $(this).val();
		    var classesID = $('#classesID').val();

        if(classesID === '0') {
            $('#studentID').html('<option value="0"><?=$this->lang->line('invoice_all_student')?></option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('invoice/getstudent')?>",
                data: {'classesID' : classesID, 'studentGroupID' : studentGroupID},
                dataType: "html",
                success: function(data) {
                    $('#studentID').html(data);
                }
            });
        }
    });

    function getRandomInt() {
      return Math.floor(Math.random() * Math.floor(9999999999999999));
    }

    function productItemDesign(feetypeID, productText, bundlefeetypeID = "", amount = "") {
        var randID = getRandomInt();
        if($('#feetypeList tr:last').text() == '') {
            var lastTdNumber = 0;
        } else {
            var lastTdNumber = $("#feetypeList tr:last td:eq(0)").text();
        }

        lastTdNumber = parseInt(lastTdNumber);
        lastTdNumber++;

        var text = '<tr id="tr_'+randID+'" invoicefeetypeID="'+feetypeID+'" invoicebundlefeetypeID="'+bundlefeetypeID+'">';
            text += '<td>';
                text += lastTdNumber;
            text += '</td>';

            text += '<td>';
                text += productText;
            text += '</td>';

            text += '<td>';
                if (bundlefeetypeID != "")
                    text += ('<input type="text" class="form-control change-amount" readonly id="td_amount_id_'+randID+'" data-amount-id="'+randID+'" value="'+ amount +'">');
                else
                    text += ('<input type="text" class="form-control change-amount" id="td_amount_id_'+randID+'" data-amount-id="'+randID+'" value="'+ amount +'">');
            text += '</td>';

            text += '<td>';
                text += ('<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="feetype_'+randID+'" data-feetype-id="'+randID+'"><i class="fa fa-trash-o"></i></a>');
            text += '</td>';
        text += '</tr>';

        return text;
    }

    $('#bundlefeetypeID').change(function(event) {
          var bundlefeetypeID = $(this).val();
          var selected = $(this).find('option:selected');
          var total = selected.data("total");

          if(bundlefeetypeID != '') {
            var feetypeText = $(this).find(":selected").text();
            var appendData  = productItemDesign('', feetypeText, bundlefeetypeID, total);
            $('#feetypeList').append(appendData);
          }
      });

    $('#feetypeID').change(function(e) {
        var feetypeID   = $(this).val();
        if(feetypeID != '') {
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
    var globaltotaldiscount = 0;
    var globaltotalsubtotal = 0;
    var globaltotalpaidamount = 0;
    function totalInfo() {
        var i = 1;
        var j = 1;

        var totalAmount = 0;
        var totalDiscount = 0;
        var totalSubtotal = 0;
        var totalPaidAmount = 0;

        var discount = 0;

        $('#feetypeList tr').each(function(index, value) {
            if($(this).children().eq(2).children().val() != '' && $(this).children().eq(2).children().val() != null && $(this).children().eq(2).children().val() != '.') {
                var amount = parseFloat($(this).children().eq(2).children().val());
                totalAmount += amount;
            }
        });
        globaltotalamount = totalAmount;
        $('#totalAmount').text(currencyConvert(totalAmount));

        $('#feetypeList tr').each(function(index, value) {
            if($(this).children().eq(3).children().val() != '' && $(this).children().eq(3).children().val() != null && $(this).children().eq(3).children().val() != '.') {
                var discount = parseFloat($(this).children().eq(3).children().val());
                totalDiscount += discount;
            }
        });
        globaltotaldiscount = totalDiscount;
        $('#totalDiscount').text(currencyConvert(totalDiscount));


        $('#feetypeList tr').each(function(index, value) {
            var amount = parseFloat($(this).children().eq(2).children().val());
            var discount = parseFloat($(this).children().eq(3).children().val());
            var subtotal = 0;
            if(amount > 0) {
                if(discount > 0) {
                    if(discount == 100) {
                        subtotal = 0;
                    } else {
                        subtotal = (amount - ((amount/100) * discount));
                    }
                } else {
                    subtotal = amount;
                }
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

        removePaidAmount(amountID);
    });

    $(document).on('keyup', '.change-paidamount', function() {
        var trID = $(this).parent().parent().attr('id').replace('tr_','');
        var amount = $('#'+'td_amount_id_'+trID).val();
        var discount = $('#'+'td_discount_id_'+trID).val();

        if(discount != '' && discount != null) {
            amount = (amount - ((amount/100) * discount));
        }

        if(amount != '' && amount != null) {
            var paidamount =  toFixedVal($(this).val());
            var paidamountID = $(this).attr('data-paidamount-id');

            if(dotAndNumber(paidamount)) {
                if(paidamount.length > 15) {
                    paidamount = lenChecker(paidamount, 15);
                    if(parseFloat(paidamount) > parseFloat(amount)) {
                        $(this).val(amount);
                    } else {
                        $(this).val(paidamount);
                    }
                }

                if(paidamount != '' && paidamount != null) {
                    if(parseFloat(paidamount) > parseFloat(amount)) {
                        $(this).val(amount);
                    } else {
                        $(this).val(paidamount);
                    }
                    totalInfo();
                } else {
                    totalInfo();
                }
            } else {
                var paidamount = parseSentenceForNumber(toFixedVal($(this).val()));
                if(parseFloat(paidamount) > parseFloat(amount)) {
                    $(this).val(amount);
                } else {
                    $(this).val(paidamount);
                }
            }
        } else {
            $(this).val('');
        }
    });

    $(document).on('keyup', '.change-discount', function() {
        var trID = $(this).parent().parent().attr('id').replace('tr_','');
        var randID = $(this).attr('data-discount-id');
        var amount = $('#'+'td_amount_id_'+trID).val();

        if(amount != '' && amount != null) {
            var discount =  toFixedVal($(this).val());
            var discountID = $(this).attr('data-discount-id');

            if(dotAndNumber(discount)) {
                if(discount > 100) {
                    discount = 100;
                }
                $(this).val(discount);
                totalInfo();
            } else {
                var discount = parseSentenceForNumber(toFixedVal($(this).val()));
                $(this).val(discount);
            }
        } else {
            $(this).val('');
        }

        removePaidAmount(randID);
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

    function removePaidAmount(randID) {
        var ramount = $('#td_amount_id_'+randID).val();
        var rdiscount = $('#td_discount_id_'+randID).val();
        var rpaidamount = ($('#td_paidamount_id_'+randID).val());

        if(ramount == '' && ramount == null) {
            ramount = 0;
        }

        if(rdiscount == '' && rdiscount == null) {
            rdiscount = 0;
        }

        if(rpaidamount != '' && rpaidamount != null) {
            ramount = parseFloat((ramount - (ramount/100) * rdiscount));
            rpaidamount = parseFloat(rpaidamount);
            if(rpaidamount > ramount) {
                $('#td_paidamount_id_'+randID).val('');
            }
        }
    }

    $(document).on('click', '#addInvoiceButton', function() {
        var error=0;
        var field = {
            'classesID'           : $('#classesID').val(),
            'studentID'           : $('#studentID').val(),
			      'schooltermID'        : $('#schooltermID').val(),
            'date'                : $('#date').val(),
            'payment_method'      : $('#payment_method').val(),
        };

        if(field['classesID'] === '0') {
            $('.classesDiv').addClass('has-error');
            error++;
        } else {
            $('.classesDiv').removeClass('has-error');
        }

        if(field['date'] === '') {
            $('.dateDiv').addClass('has-error');
            error++;
        } else {
            $('.dateDiv').removeClass('has-error');
        }

        var totalsubtotal = 0;
        var totalpaidamount = 0;
        var feetypeitems = $('tr[id^=tr_]').map(function(){
            if($(this).children().eq(4).text() != '' && $(this).children().eq(4).text() != null) {
                totalsubtotal += parseFloat($(this).children().eq(4).text());
            }

            if($(this).children().eq(5).children().val() != '' && $(this).children().eq(5).children().val() != null) {
                totalpaidamount += parseFloat($(this).children().eq(5).children().val());
            }

            //return { feetypeID : $(this).attr('invoicefeetypeid'), bundlefeetypeID : $(this).attr('invoicebundlefeetypeid'), amount: $(this).children().eq(2).children().val(), discount : $(this).children().eq(3).children().val(), subtotal: $(this).children().eq(4).text() , paidamount: $(this).children().eq(5).children().val() };
            if ($(this).attr('invoicebundlefeetypeid') != "")
                return { bundlefeetypeID : $(this).attr('invoicebundlefeetypeid'), amount: $(this).children().eq(2).children().val(), discount : $(this).children().eq(3).children().val(), subtotal: $(this).children().eq(4).text() , paidamount: $(this).children().eq(5).children().val() };
            else if ($(this).attr('invoicefeetypeid') != "")
                return { feetypeID : $(this).attr('invoicefeetypeid'), amount: $(this).children().eq(2).children().val(), discount : $(this).children().eq(3).children().val(), subtotal: $(this).children().eq(4).text() , paidamount: $(this).children().eq(5).children().val() };
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

        feetypeitems        = JSON.stringify(feetypeitems);

        if(error === 0) {
            $(this).attr('disabled', 'disabled');
            var formData = new FormData($('#invoiceDataForm')[0]);
            formData.append("feetypeitems", feetypeitems);
            formData.append("totalsubtotal", totalsubtotal);
            formData.append("totalpaidamount", totalpaidamount);
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
            url: "<?=base_url('invoice/saveinvoice')?>",
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
            window.location = "<?=base_url("invoice/index")?>";
        } else {
            $('#addInvoiceButton').removeAttr('disabled');
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

    var url = '<?php echo $authUrl; ?>';

    var OAuthCode = function(url) {

        this.loginPopup = function (parameter) {
            this.loginPopupUri(parameter);
        }

        this.loginPopupUri = function (parameter) {

            // Launch Popup
            var parameters = "location=1,width=800,height=650";
            parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

            var win = window.open(url, 'connectPopup', parameters);
            var pollOAuth = window.setInterval(function () {
                try {

                    if (win.document.URL.indexOf("code") != -1) {
                        window.clearInterval(pollOAuth);
                        win.close();
                        location.reload();
                    }
                } catch (e) {
                    console.log(e)
                }
            }, 100);
        }
    }

    var oauth = new OAuthCode(url);

</script>
