<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-transactionreport"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_transactionsummary')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <!--<div class="row">

            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="fromdateDiv">
                    <label><?=$this->lang->line("transactionsummary_fromdate")?><span class="text-red"> * </span></label>
                   <input class="form-control" type="text" name="fromdate" id="fromdate">
                </div>

                <div class="form-group col-sm-4" id="todateDiv">
                    <label><?=$this->lang->line("transactionsummary_todate")?><span class="text-red"> * </span></label>
                    <input class="form-control" type="text" name="todate" id="todate">
                </div>

                <div class="col-sm-4">
                    <button id="get_classreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("transactionsummary_submit")?></button>
                </div>

            </div>

        </div><!-- row -->
		<div class="row">

            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("transactionsummary_class")?></label>
                    <?php
                        $classesArray = array(
                            "0" => $this->lang->line("transactionsummary_please_select"),
                        );
                        foreach ($classes as $classaKey => $classa) {
                            $classesArray[$classa->classesID] = $classa->classes;
                        }
                        echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="groupDiv">
                    <label><?=$this->lang->line("transactionsummary_group")?></label>
                    <?php
                        $groupArray = array(
                            "0" => $this->lang->line("transactionsummary_please_select"),
                        );
						foreach ($groups as $group) {
							$groupArray[$group->studentgroupID] = $group->group;
						}
                        echo form_dropdown("studentgroupID", $groupArray, set_value("studentgroupID"), "id='studentgroupID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="studentDiv">
                    <label><?=$this->lang->line("transactionsummary_student")?></label>
                    <?php
                        $studentArray = array(
                            "0" => $this->lang->line("transactionsummary_please_select"),
                        );
                        echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
                     ?>
                </div>

            </div>

			<div class="row">
				<div class="col-sm-12">
					<div class="col-md-3">
						<div class="form-group">
							<label for="from date" class="control-label">
								From
							</label>
							<input name="fromdate" id="fromdate" type="date" class="form-control" value="<?=$set_dateFrom?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label for="to date" class="control-label">
								To
							</label>
							<input name="todate" id="todate" type="date" class="form-control" value="<?=$set_dateTo?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group" >
							<label for="schoolYearID" class="control-label"><?=$this->lang->line("transactionsummary_schoolyear")?></label>

							<?php
								$schoolYearArray = array();
								foreach ($schoolyears as $schoolYear) {
									$schoolYearArray[$schoolYear->schoolyearID] = $schoolYear->schoolyear;
								}
								echo form_dropdown("schoolYearID", $schoolYearArray, set_value("schoolYearID", $set_schoolYearID), "id='schoolYearID' class='form-control select2'");
							?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group" >
							<label for="schoolTermID" class="control-label"><?=$this->lang->line("transactionsummary_schoolterm")?></label>

							<?php
								$schoolTermArray = array(
                            "0" => $this->lang->line("transactionsummary_please_select"));
								foreach ($schoolterms as $schoolTerm) {
									$schoolTermArray[$schoolTerm->schooltermID] = $schoolTerm->schooltermtitle;
								}
								echo form_dropdown("schoolTermID", $schoolTermArray, set_value("schoolTermID", 0), "id='schoolTermID' class='form-control select2'");
							?>
						</div>
					</div>
				</div>
			</div>
      <div class="row">
				<div class="col-sm-12">
          <div class="col-md-3">
						<div class="form-group" >
							<label for="report type" class="control-label">
                <?=$this->lang->line("transactionsummary_report_type")?> <span class="text-red">*</span>
              </label>

							<?php
								$reportTypeArray = array("invoice_report" => $this->lang->line('transactionsummary_invoice_report'), "creditmemo_report" => $this->lang->line('transactionsummary_creditmemo_report'), "payment_report" => $this->lang->line('transactionsummary_payment_report'));
								echo form_dropdown("report-type", $reportTypeArray, set_value("report-type", "invoice_report"), "id='report-type' class='form-control select2'");
							?>
						</div>
					</div>
          <div class="col-md-3">
						<div class="form-group" >
							<label for="report details" class="control-label">
                <?=$this->lang->line("transactionsummary_report_details")?> <span class="text-red">*</span>
              </label>

							<?php
								$reportDetailsArray = array("student_detail" => $this->lang->line('transactionsummary_student_detail'), "class_summary" => $this->lang->line('transactionsummary_class_summary'), "division_summary" => $this->lang->line('transactionsummary_division_summary'), "date_detail" => $this->lang->line('transactionsummary_date_detail'), "date_summary" => $this->lang->line('transactionsummary_date_summary'), "month_summary" => $this->lang->line('transactionsummary_month_summary'), "term_summary" => $this->lang->line('transactionsummary_term_summary'), "year_summary" => $this->lang->line('transactionsummary_year_summary'));
								echo form_dropdown("report-details", $reportDetailsArray, set_value("report-details", ""), "id='report-details' class='form-control select2'");
							?>
						</div>
					</div>
          <div class="col-md-3" id="selectItemsDiv">
						<div class="form-group" >
							<label for="select items" class="control-label">
                <?=$this->lang->line("transactionsummary_select_items")?>
              </label>

							<?php
                $itemsArray = array($this->lang->line('transactionsummary_term_fee') => $this->lang->line('transactionsummary_term_fee'));
                foreach ($feetypes as $feetype) {
                  $itemsArray[$feetype->feetypesID] = $feetype->feetypes;
                }
                $itemsArray[$this->lang->line('transactionsummary_total_amount')] = $this->lang->line('transactionsummary_total_amount');
								echo form_dropdown("items", $itemsArray, set_value("items"), "id='items' class='form-control' multiple");
							?>
						</div>
					</div>
          <div class="col-md-3" id="selectedTotalDiv">
						<div class="form-group" >
							<label for="selected total" class="control-label">
                <?=$this->lang->line("transactionsummary_selected_total")?>
              </label>

							<?php
                $selectedTotalArray = array("0" => $this->lang->line("transactionsummary_all_selected_total"), "1" => $this->lang->line("transactionsummary_greater_than_0"));
								echo form_dropdown("selected-total", $selectedTotalArray, set_value("selected-total", 0), "id='selected-total' class='form-control select2'");
							?>
						</div>
					</div>
        </div>
      </div>

			<div class="row">
				<div class="col-md-2 pull-right">
					<button id="get_classreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("transactionsummary_submit")?></button>
				</div>
			</div>

        </div><!-- row -->

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_transactionreport"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
<script type="text/javascript">
	$('.select2').select2();

    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('transactionreport_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    /*$('#fromdate').datepicker({
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
    });*/

	$("#classesID").change(function() {
		var id = $(this).val();
		if(parseInt(id)) {
			if(id === '0') {
				$('#studentID').val(0);
			} else {
				$.ajax({
					type: 'POST',
					url: "<?=base_url('student_statement/studentcall')?>",
					data: {"classesID" : id},
					dataType: "html",
					success: function(data) {
					   $('#studentID').html(data);
					}
				});
			}
		}
	});

  $("#report-type").change(function() {
		var id = $(this).val();
    if (id != "invoice_report") {
      $("#selectItemsDiv").hide('slow');
      $("#selectedTotalDiv").hide('slow');
    }
    else {
      $("#selectItemsDiv").show('slow');
      $("#selectedTotalDiv").show('slow');
    }
	});

  $("#schoolYearID").change(function() {
		var id = $(this).val();
    if(parseInt(id)) {
			if(id === '0') {
				$('#schoolTermID').val(0);
			} else {
				$.ajax({
					type: 'POST',
					url: "<?=base_url('student_statement/termcall')?>",
					data: {"schoolYearID" : id},
					dataType: "html",
					success: function(data) {
					   $('#schoolTermID').html(data);
					}
				});
			}
		}
	});

  $('#items').multiselect({
    enableFiltering: true,
    includeSelectAllOption: true
  });

  $(".multiselect-container").css("position", "relative");

    $('#get_classreport').click(function() {
		    var classesID = $('#classesID').val();
        var studentgroupID = $('#studentgroupID').val();
        var studentID = $('#studentID').val();
    		var dateFrom = $('#dateFrom').val();
    		var dateTo = $('#dateTo').val();
    		var schoolYearID = $('#schoolYearID').val();
    		var schoolTermID = $('#schoolTermID').val();
        var reportType = $('#report-type').val();
        var reportDetails = $('#report-details').val();
        var selected = $('#reportDetails').val();
        var items = $('#items').val();
        var selectedTotal = $('#selected-total').val();
        var error = 0;

        var field = {
            "classesID"      : classesID,
            "studentgroupID" : studentgroupID,
            "studentID"      : studentID,
      			"dateFrom"       : dateFrom,
      			"dateTo"         : dateTo,
      			"schoolyearID"   : schoolYearID,
      			"schooltermID"   : schoolTermID,
            "reportType"     : reportType,
            "reportDetails"  : reportDetails,
            "selectedTotal"  : selectedTotal,
            "items"          : items
        };

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
            url: "<?=base_url('transactionsummary/getTransactionSummary')?>",
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
            $('#load_transactionreport').html(response.render);
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
