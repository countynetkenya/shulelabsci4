
<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-payment"></i> <?=$this->lang->line('panel_title')?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form role="form" method="post" enctype="multipart/form-data" id="invoiceDataForm"> 

                    <div class="classesDiv form-group <?=form_error('classesID') ? 'has-error' : '' ?>" >
                        <label for="classesID">
                            <?=$this->lang->line("payment_classesID")?> <span class="text-red">*</span>
                        </label>
                            <?php
                                $classesArray = array('0' => $this->lang->line("payment_select_classes"));
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
					
					<div class="form-group <?=form_error('schooltermID') ? 'has-error' : '' ?>" >
                        <label for="schooltermID">
                            <?=$this->lang->line("payment_schooltermID")?> <span class="text-red">*</span>
                        </label>
                            <?php
                                $termsArray = array('0' => $this->lang->line("payment_select_schoolterm"));
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
                            <?=$this->lang->line("payment_date")?> <span class="text-red">*</span>
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

                    <input id="addPaymentButton" type="button" class="btn btn-success" value="<?=$this->lang->line("global_submit")?>" >
                </form>
            </div>
        </div>
    </div>


    <div class="col-sm-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-feetypes"></i> <?=$this->lang->line('payment_student')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("paymenthistory/index")?>"><?=$this->lang->line('menu_paymenthistory')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_payment')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">							
							<div class="form-group <?=form_error('studentID') ? 'has-error' : '' ?>" >
								<label for="studentID">
									<?=$this->lang->line("payment_studentID")?> <span class="text-red">*</span>
								</label>
									<?php
										$studentArray = array('0' => $this->lang->line("payment_select_student"));
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
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered feetype-style" style="font-size: 16px;">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('payment_student')?></th>
                                <th class="col-sm-2" ><?=$this->lang->line('payment_amount')?></th>
								<th class="col-sm-2" ><?=$this->lang->line('payment_method')?></th>
								<th class="col-sm-2" ><?=$this->lang->line('payment_transactionid')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody id="studentList">
                        </tbody>

                        <tfoot id="studentListFooter">
                            <tr>
                                <td colspan="2" style="font-weight: bold"><?=$this->lang->line('payment_total')?></td>
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

<style>
	.errorClass { line-height:1.4; }
</style>

<script type="text/javascript">

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
            $('#studentID').html('<option value="0"><?=$this->lang->line('payment_all_student')?></option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('payment/getstudent')?>",
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
            $('#studentID').html('<option value="0"><?=$this->lang->line('payment_all_student')?></option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('payment/getstudent')?>",
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
	
	function productItemDesign(studentID, productText) {
		var paymentmethods = <?= json_encode($paymentmethods) ?>;
        var randID = getRandomInt();
        if($('#studentList tr:last').text() == '') {
            var lastTdNumber = 0;
        } else {
            var lastTdNumber = $("#studentList tr:last td:eq(0)").text();
        }

        lastTdNumber = parseInt(lastTdNumber);
        lastTdNumber++;

        var text = '<tr id="tr_'+randID+'" paymentstudentID="'+studentID+'">';
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
                text += ('<select class="form-control payment-method select2" id="td_payment_method_id_'+randID+'" data-payment-method-id="'+randID+'">');
				for (var i = 0; i < paymentmethods.length; i++) {
					text += ('<option value="">'+ paymentmethods[i] +'</option>');
				}
				for (let key in paymentmethods) {
					text += ('<option value="'+ key +'">'+ paymentmethods[key] +'</option>');
				}
				text += '</select>';
            text += '</td>';
			
			text += '<td>';
                text += ('<input type="text" class="form-control transaction-id" id="td_transaction_id_'+randID+'" data-transaction-id="'+randID+'">');
            text += '</td>';
			
            text += '<td>';
                text += ('<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="student_'+randID+'" data-student-id="'+randID+'"><i class="fa fa-trash-o"></i></a>');
            text += '</td>';
        text += '</tr>';

        return text; 
    }
	
	$('#studentID').change(function(e) {
        var studentID   = $(this).val();
        if(studentID != 0) {
            var studentText = $(this).find(":selected").text();
            var appendData  = productItemDesign(studentID, studentText);
            $('#studentList').append(appendData);
			$('select.payment-method').select2();
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
	
	function currencyConvert(data) {
        return data.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }
	
	var globaltotalamount = 0;
    function totalInfo() {
        var i = 1;
        var j = 1;

        var totalAmount = 0;
        var totalPaidAmount = 0
		
        $('#studentList tr').each(function(index, value) {
            if($(this).children().eq(2).children().val() != '' && $(this).children().eq(2).children().val() != null && $(this).children().eq(2).children().val() != '.') {
                var amount = parseFloat($(this).children().eq(2).children().val());
                totalAmount += amount;
            } 
        });
        globaltotalamount = totalAmount;
        $('#totalAmount').text(currencyConvert(totalAmount));

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
	
	$(document).on('click', '.deleteBtn', function(er) {
        er.preventDefault();
        var studentID = $(this).attr('data-student-id');
        $('#tr_'+studentID).remove();
        
        var i = 1;
        $('#studentList tr').each(function(index, value) {
            $(this).children().eq(0).text(i);
            i++;
        });
        totalInfo();
    });
	
	function removePaidAmount(randID) {
        var ramount = $('#td_amount_id_'+randID).val();
        
        if(ramount == '' && ramount == null) {
            ramount = 0;
        }
    }

	/*function addStudent() {
		var students = <?= json_encode($students); ?>;
		
		//$studentArray[$student->srstudentID] = $student->srname.' - '.$this->lang->line('global_roll').' - '.$student->srstudentID;
		
		var text = '<tr>';
			text += '<td>';
				text += ('<?=$this->lang->line("global_student")?> <span class="text-red">*</span>');
			text += '</td>';
			
			text += '<td>';
				text += ('<select name="studentID[]" class="form-control select2">');
				text += ('<option value=""><?=$this->lang->line("global_select_student")?></option>');
				for (let key in students) {
					text += ('<option value="' + students[key]['srstudentID'] + '">' + students[key]['srname'] + ' - <?=$this->lang->line("global_roll")?>' + ' - ' + students[key]['srstudentID'] + '</option>');
				}
				
				text += ('</select>');
			text += '</td>';
			
			text += '<td>';
				text += ('<?=$this->lang->line("global_amount")?> <span class="text-red">*</span>');
			text += '</td>';
			
			text += '<td>';
				text += ('<input name="paid[]" class="form-control" type="number">');
			text += '</td>';
		text += '</tr>';

		return text;
	}

	$('#add_payment').on('click',function(e){
		var paymentdate            = $('#date'); 
		var payment_type           = $('#payment_type'); 
		var phonenumber        = $('#phonenumber');
		var referencenumber        = $('#referencenumber');
		var memo        		   = $('#memo');

		paymentdate             = paymentdate.val();
		payment_type            = payment_type.val();
		memo                    = memo.val();
		phonenumber         = phonenumber.val();
		referencenumber         = referencenumber.val();
		
		var paid = $('input[name^=paid]').map(function(){
			return $(this).val();
		}).get();
		var students = $('select[name^=studentID]').map(function(){
			return $(this).val();
		}).get();
		
		$(this).attr("disabled", "disabled");
		$.ajax({
			type: 'POST',
			url: "<?=base_url('global_payment/paymentSend')?>",
			data: {
				"studentID" : students,
				"paymentdate" : paymentdate,
				"payment_type" : payment_type,
				"paid" : paid,
				"memo" : memo,
				"phonenumber" : phonenumber,
				"referencenumber" : referencenumber
			},
			dataType: "html",
			success: function(data) {
				var response = JSON.parse(data);
				errorLoader(response);
			}
		});
	});

	function errorLoader(response) {
			if (response.status && "id" in response) {
				window.location.replace("paymenthistory/view/"+ response.id);
			} else if (response.status) {
				var val = "Please enter M-PESA PIN on your phone";
				toastr["success"](val)
					toastr.options = {
						"closeButton": true,
						"debug": false,
						"newestOnTop": false,
						"progressBar": false,
						"positionClass": "toast-top-right",
						"preventDuplicates": false,
						"onclick": null,
						"timeOut": "0",
						"extendedTimeOut": "0",
						"showEasing": "swing",
						"hideEasing": "linear",
						"showMethod": "fadeIn",
						"hideMethod": "fadeOut"
					}
			} else {
				$('#add_payment').removeAttr('disabled');
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
		}*/
	$(document).on('click', '#addPaymentButton', function() {
        var error=0;
        var field = {
            'date'                : $('#date').val(),
            'schooltermID'     : $('#schooltermID').val(), 
			'memo'                : $('#memo').val(),
        };
        
        if(field['date'] === '') {
            $('.dateDiv').addClass('has-error');
            error++;
        } else {
            $('.dateDiv').removeClass('has-error');
        }

        var studentitems = $('tr[id^=tr_]').map(function(){
            return { studentID : $(this).attr('paymentstudentID'), amount: $(this).children().eq(2).children().val(), paymentmethod: $(this).children().eq(3).find('select').val(), transactionID: $(this).children().eq(4).children().val()};
        }).get();

        if (typeof studentitems == 'undefined' || studentitems.length <= 0) {
            error++;
            toastr["error"]('The student item is required.')
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

        studentitems = JSON.stringify(studentitems);

        if(error === 0) {
            $(this).attr('disabled', 'disabled');
            var formData = new FormData($('#invoiceDataForm')[0]);
            formData.append("studentitems", studentitems);
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
            url: "<?=base_url('global_payment/paymentSend')?>",
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
            window.location = "<?=base_url("payment/index")?>";
        } else {
            $('#addPaymentButton').removeAttr('disabled');
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