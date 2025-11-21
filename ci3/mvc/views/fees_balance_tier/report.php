<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php

            $pdf_preview_uri = base_url('balancefeesreport/pdf/'.$classesID.'/'.$sectionID.'/'.$studentID);
            $xml_preview_uri = base_url('balancefeesreport/xlsx/'.$classesID.'/'.$sectionID.'/'.$studentID);

            echo btn_printReport('balancefeesreport', $this->lang->line('report_print'), 'printablediv');
            //echo btn_pdfPreviewReport('balancefeesreport',$pdf_preview_uri, $this->lang->line('report_pdf_preview'));
            //echo btn_xmlReport('balancefeesreport',$xml_preview_uri, $this->lang->line('report_xlsx'));
            //echo btn_sentToMailReport('balancefeesreport', $this->lang->line('report_send_pdf_to_mail'));
        ?>
		<button class="btn btn-default" onclick="javascript:csvDiv('printablediv')"> Download CSV </button>
    </div>
</div>

<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
            <?=$this->lang->line('fees_balance_tier_report_for')?> -
            <?=$this->lang->line('fees_balance_tier_balancefees');?>
        </h3>
    </div><!-- /.box-header -->
    <div id="printablediv">
    <!-- form start -->
        <div class="box-body" style="margin-bottom: 50px;">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>
                <?php if($classesID >= 0 || $sectionID >= 0 ) { ?>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="pull-left">
                                    <?php
                                        echo $this->lang->line('fees_balance_tier_class')." : ";
                                        echo isset($classes[$classesID]) ? $classes[$classesID] : $this->lang->line('fees_balance_tier_all_class');
                                    ?>
                                </h5>
                                <h5 class="pull-right">
                                    <?php
                                       echo $this->lang->line('fees_balance_tier_section')." : ";
                                       echo isset($sections[$sectionID]) ? $sections[$sectionID] : $this->lang->line('fees_balance_tier_all_section');
                                    ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                <?php }  else { ?>
                    <div class="col-sm-12" style="margin-top: 15px;"></div>
                <?php }
                if(customCompute($students)) { ?>
                    <div class="col-sm-12">
                        <div id="hide-table">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('fees_balance_tier_student_detail')?></th>
                    										<th><?=$this->lang->line('fees_balance_tier_class')?></th>
                    										<th><?=$this->lang->line('fees_balance_tier_group')?></th>
                    										<th><?=$this->lang->line('fees_balance_tier_balance_bf')?></th>
                                        <th><?=$this->lang->line('fees_balance_tier_invoiced_amount')?></th>
                                        <th><?=$this->lang->line('fees_balance_tier_less_credits')?> </th>
										                    <th><?=$this->lang->line('fees_balance_tier_total_payable')?></th>
                                        <th><?=$this->lang->line('fees_balance_tier_amount_paid')?></th>
                                        <th><?=$this->lang->line('fees_balance_tier_balance_cf')?> </th>
                    										<th><?=$this->lang->line('fees_balance_tier_%_unpaid')?> </th>
                    										<th><?=$this->lang->line('fees_balance_tier_%_paid')?> </th>
                                        <th><?=$this->lang->line('fees_balance_tier_%_credit') ?></th>
                                        <th><?=$this->lang->line('fees_balance_tier') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                    										$totalBalanceBf = 0;
                    										$totalBalanceCf = 0;
                                        $totalAmount = 0;
                    										$totalCredits = 0;
                    										$totalPayable = 0;
                                        $totalPayments = 0;
                                        $totalBalance = 0;
                    										$totalUnpaidPercent = 0;
                    										$totalPaidPercent = 0;
                    										$totalCreditPercent = 0;
                                        foreach($students as $student) { ?>
                                            <tr>
                                                <td data-title="<?=$this->lang->line('fees_balance_tier_student_detail')?>">
                        													<?=$student->srname?> - <?=$student->srstudentID?> - <?=$student->srclasses?> - <?=$student->group?>
                                                                        </td>
                        												<td data-title="<?=$this->lang->line('fees_balance_tier_class')?>">
                        													<?=$student->srclasses?>
                                                                        </td>
                        												<td data-title="<?=$this->lang->line('fees_balance_tier_group')?>">
                        													<?=$student->group?>
                                                </td>
                                                <td data-title="<?=$this->lang->line('fees_balance_tier_balance_bf')?>">
                                                    <?php
                                                        $AmountBf = 0;
                                                        $CreditBf = 0;
                                                        $PaymentBf = 0;
                            														$BalanceBf = 0;
                            														$BalanceCf = 0;

                                                        if(isset($totalAmountAndDiscountBf[$student->srstudentID]['amount'])) {
                                                            $AmountBf = $totalAmountAndDiscountBf[$student->srstudentID]['amount'];
                                                        }

                                                        if(isset($totalCreditBf[$student->srstudentID]['amount'])) {
                                                            $CreditBf = $totalCreditBf[$student->srstudentID]['amount'];
                                                        }

                                                        if(isset($totalPaymentBf[$student->srstudentID]['payment'])) {
                                                            $PaymentBf = $totalPaymentBf[$student->srstudentID]['payment'];
                                                        }

                                                        $BalanceBf = ($AmountBf - $CreditBf) - ($PaymentBf);
														                            $totalBalanceBf += $BalanceBf;

                                                        echo number_format($BalanceBf,2);
                                                    ?>
                                                </td>
												                        <td data-title="<?=$this->lang->line('fees_balance_tier_invoiced_amount')?>">
                                                    <?=isset($totalAmountAndDiscount[$student->srstudentID]['amount']) ? number_format($totalAmountAndDiscount[$student->srstudentID]['amount'],2) : number_format(0, 2)?>
                                                </td>
                        												<td data-title="<?=$this->lang->line('fees_balance_tier_less_credits')?>">
                        													<?=isset($totalCredit[$student->srstudentID]['amount']) ? number_format($totalCredit[$student->srstudentID]['amount'],2) : number_format(0, 2)?>
                                                </td>
                        												<td data-title="<?=$this->lang->line('fees_balance_tier_total_payable')?>">
													                             <?php
                                                        $Amount = 0;
                                                        $Credit = 0;
                                                        $Payment = 0;

                                                        if(isset($totalAmountAndDiscount[$student->srstudentID]['amount'])) {
                                                            $Amount = $totalAmountAndDiscount[$student->srstudentID]['amount'];
                                                            $totalAmount += $Amount;
                                                        }

                                                        if(isset($totalCredit[$student->srstudentID]['amount'])) {
                                                            $Credit = $totalCredit[$student->srstudentID]['amount'];
                                                            $totalCredits += $Credit;
                                                        }

                                                        if(isset($totalPayment[$student->srstudentID]['payment'])) {
                                                            $Payment = $totalPayment[$student->srstudentID]['payment'];
                                                            $totalPayments += $Payment;
                                                        }

                            														$Payable = $BalanceBf + $Amount - $Credit;
                            														$totalPayable += $Payable;
                                                        $BalanceCf = $Payable - $Payment;
                            														$totalBalanceCf += $BalanceCf;
                            														if ($BalanceCf > 0) {
                            															$unpaidPercent = round($BalanceCf/$Payable*100);
                            															$paidPercent = 100-$unpaidPercent;
                            														} else {
                            															$unpaidPercent = 0;
                            															$paidPercent = 100;
                            														}

                            														if ($Credit > 0 && $Amount > 0)
                            															$creditPercent = round($Credit/$Amount*100);
                            														else
                            															$creditPercent = 0;

                                                        $totalBalance += $Balance;

                                                        echo number_format($Payable,2);
                                                    ?>
                                                </td>
                                                <td data-title="<?=$this->lang->line('fees_balance_tier_total_paid')?>">
                                                    <?=isset($totalPayment[$student->srstudentID]['payment']) ? number_format($totalPayment[$student->srstudentID]['payment'],2) : number_format(0, 2)?>
                                                </td>
                                                <td data-title="<?=$this->lang->line('fees_balance_tier_balance_cf')?>">
													                          <?=number_format($BalanceCf, 2)?>
                                                </td>
                        												<td><?=$unpaidPercent?></td>
                        												<td><?=$paidPercent?></td>
                        												<td><?=$creditPercent?></td>
                                                <td><?=$tiers[$student->srstudentID]?></td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                </tbody>
								                <tfoot>
                                        <td data-title="<?=$this->lang->line('fees_balance_tier_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('fees_balance_tier_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                                        <td data-title="<?=$this->lang->line('fees_balance_tier_total_balance_bf')?>" class="text-bold"><?=number_format($totalBalanceBf,2)?></td>
                                        <td data-title="<?=$this->lang->line('fees_balance_tier_total_amount')?>" class="text-bold"><?=number_format($totalAmount,2)?></td>
										                    <td data-title="<?=$this->lang->line('fees_balance_tier_total_credit')?>" class="text-bold"><?=number_format($totalCredits,2)?></td>
										                    <td data-title="<?=$this->lang->line('fees_balance_tier_total_payable')?>" class="text-bold"><?=number_format($totalPayable,2)?></td>
                                        <td data-title="<?=$this->lang->line('fees_balance_tier_total_paid')?>" class="text-bold"><?=number_format($totalPayments,2)?></td>
                                        <td data-title="<?=$this->lang->line('fees_balance_tier_total_balance_cf')?>" class="text-bold"><?=number_format($totalBalanceCf,2)?></td>
                                        <td>
                                            <?php if($totalPayable > 0) {
                                                $totalUnpaidPercent = round($totalBalanceCf/$totalPayable*100);
                                              } else {
                                                $totalUnpaidPercent = 0;
                                              }
                                              echo $totalUnpaidPercent;
                                            ?>
                                        </td>
                    										<td>
                                            <?=100-$totalUnpaidPercent;?>
                                        </td>
                    										<td>
                                            <?php if ($totalCredits > 0 && $totalAmount > 0)
                                              $totalCreditPercent = round($totalCredits/$totalAmount*100);
                                            else
                                              $totalCreditPercent = 0;

                                            echo $totalCreditPercent; ?>
                                        </td>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php } else { ?>
                    <br/>
                    <div class="col-sm-12">
                        <div class="callout callout-danger">
                            <p><b class="text-info"><?=$this->lang->line('report_data_not_found')?></b></p>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-12 text-center footerAll">
                    <?=reportfooter($siteinfos, $schoolyearsessionobj)?>
                </div>
            </div><!-- row -->
        </div><!-- Body -->
    </div>
</div>


<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?=base_url('balancefeesreport/send_pdf_to_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=$this->lang->line('fees_balance_tier_close')?></span></button>
                <h4 class="modal-title"><?=$this->lang->line('fees_balance_tier_mail')?></h4>
            </div>
            <div class="modal-body">

                <?php
                    if(form_error('to'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="to" class="col-sm-2 control-label">
                        <?=$this->lang->line("fees_balance_tier_to")?> <span class="text-red">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="email" class="form-control" id="to" name="to" value="<?=set_value('to')?>" >
                    </div>
                    <span class="col-sm-4 control-label" id="to_error">
                    </span>
                </div>

                <?php
                    if(form_error('subject'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="subject" class="col-sm-2 control-label">
                        <?=$this->lang->line("fees_balance_tier_subject")?> <span class="text-red">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="subject" name="subject" value="<?=set_value('subject')?>" >
                    </div>
                    <span class="col-sm-4 control-label" id="subject_error">
                    </span>

                </div>

                <?php
                    if(form_error('message'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="message" class="col-sm-2 control-label">
                        <?=$this->lang->line("fees_balance_tier_message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("fees_balance_tier_send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>

<script type="text/javascript">

	$('.table').DataTable({
        columnDefs: [
            {
                targets: [0],
                orderData: [0, 1],
            },
            {
                targets: [1],
                orderData: [1, 0],
            },
            {
                targets: [4],
                orderData: [4, 0],
            },
        ],
    });

	function csvDiv(divID) {
	//Get the HTML of div
	var divElements = document.getElementById(divID).innerHTML;

	//Reset the page's HTML with div's HTML only
	var html =
	  "<html><head><title></title></head><body>" +
	  divElements + "</body>";
	htmlToCSV(html, "statement.csv");
}

function htmlToCSV(html, filename) {
	var data = [];
	var rows = document.querySelectorAll('table tr');

	for (var i = 0; i < rows.length; i++) {
		var row = [], cols = rows[i].querySelectorAll("td, th");

		for (var j = 0; j < cols.length; j++) {
				row.push("\""+cols[j].innerText+"\"");
        }

		data.push(row.join(","));
	}

	downloadCSVFile(data.join("\n"), filename);
}

function downloadCSVFile(csv, filename) {
	var csv_file, download_link;

	csv_file = new Blob([csv], {type: "text/csv"});

	download_link = document.createElement("a");

	download_link.download = filename;

	download_link.href = window.URL.createObjectURL(csv_file);

	download_link.style.display = "none";

	document.body.appendChild(download_link);

	download_link.click();
}

    function check_email(email) {
        var status = false;
        var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (email.search(emailRegEx) == -1) {
            $("#to_error").html('');
            $("#to_error").html("<?=$this->lang->line('fees_balance_tier_mail_valid')?>").css("text-align", "left").css("color", 'red');
        } else {
            status = true;
        }
        return status;
    }

    $("#send_pdf").click(function() {
        var field = {
            'to'         : $('#to').val(),
            'subject'    : $('#subject').val(),
            'message'    : $('#message').val(),
            'classesID'  : '<?=$classesID?>',
            'sectionID'  : '<?=$sectionID?>',
            'studentID'  : '<?=$studentID?>',
        };

        var to = $('#to').val();
        var subject = $('#subject').val();
        var error = 0;

        $("#to_error").html("");
        $("#subject_error").html("");

        if(to == "" || to == null) {
            error++;
            $("#to_error").html("<?=$this->lang->line('fees_balance_tier_mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        }

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("<?=$this->lang->line('fees_balance_tier_mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('balancefeesreport/send_pdf_to_mail')?>",
                data: field,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if(response.status == false) {
                        $('#send_pdf').removeAttr('disabled');
                        $.each(response, function(index, value) {
                            if(index != 'status') {
                                toastr["error"](value)
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
                        });
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });
</script>
