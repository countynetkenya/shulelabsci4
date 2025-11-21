<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-balancefeesreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_balancefeesreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">

            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("balancefeesreport_class")?></label>
                    <?php
                        $classesArray = array(
                            "0" => $this->lang->line("balancefeesreport_please_select"),
                        );
                        foreach ($classes as $classaKey => $classa) {
                            $classesArray[$classa->classesID] = $classa->classes;
                        }
                        echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="groupDiv">
                    <label><?=$this->lang->line("balancefeesreport_group")?></label>
                    <?php
                        $groupArray = array(
                            "0" => $this->lang->line("balancefeesreport_please_select"),
                        );
						foreach ($groups as $group) {
							$groupArray[$group->studentgroupID] = $group->group;
						}
                        echo form_dropdown("studentgroupID", $groupArray, set_value("studentgroupID"), "id='studentgroupID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="studentDiv">
                    <label><?=$this->lang->line("balancefeesreport_student")?></label>
                    <?php
                        $studentArray = array(
                            "0" => $this->lang->line("balancefeesreport_please_select"),
                        );
						if(customCompute($students)) {
							foreach ($students as $student) {
								$studentArray[$student->srstudentID] = $student->srname.' - '.$this->lang->line('balancefeesreport_registerno').' - '.$student->srstudentID;
							}
						}
                        echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
                     ?>
                </div>

            </div>

			<div class="row">
				<div class="col-sm-12">
					<div class="col-md-3">
						<div class="form-group">
							<label for="date from" class="control-label">
								From
							</label>
							<input name="dateFrom" id="dateFrom" type="date" class="form-control" value="<?=$set_dateFrom?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label for="date to" class="control-label">
								To
							</label>
							<input name="dateTo" id="dateTo" type="date" class="form-control" value="<?=$set_dateTo?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group" >
							<label for="schoolYearID" class="control-label">School Year</label>

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
							<label for="schoolTermID" class="control-label">School Term</label>

							<?php
								$schoolTermArray = array(
                            "0" => $this->lang->line("balancefeesreport_please_select"),);
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
				<div class="col-md-2 pull-right">
					<button id="get_duefeesreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("balancefeesreport_submit")?></button>
				</div>
			</div>

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_balancefeesreport"></div>


<script type="text/javascript">

    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('balancefeesreport_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    $('.select2').select2();
    $(function(){
        //$('#sectionDiv').hide('slow');
        //$('#studentDiv').hide('slow');
    });

    $("#classesID").change(function() {
		var id = $(this).val();
		if(parseInt(id)) {
			if(id === '0') {
				$('#studentID').val(0);
			} else {
				$.ajax({
					type: 'POST',
					url: "<?=base_url('balancefeesreport/getStudent')?>",
					data: {"classesID" : id},
					dataType: "html",
					success: function(data) {
					   $('#studentID').html(data);
					}
				});
			}
		}
	});

    $(document).on('click','#get_duefeesreport', function() {
      $('#load_balancefeesreport').html("");
      var classesID = $('#classesID').val();
      var studentgroupID = $('#studentgroupID').val();
      var studentID = $('#studentID').val();
  		var dateFrom = $('#dateFrom').val();
  		var dateTo = $('#dateTo').val();
  		var schoolYearID = $('#schoolYearID').val();
  		var schoolTermID = $('#schoolTermID').val();
      var error = 0;

      var field = {
        "classesID"    : classesID,
        "studentgroupID"    : studentgroupID,
        "studentID"    : studentID,
    		"dateFrom"     : dateFrom,
    		"dateTo"       : dateTo,
    		"schoolyearID" : schoolYearID,
    		"schooltermID" : schoolTermID,
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
            url: "<?=base_url('balancefeesreport/getBalanceFeesReport')?>",
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
            $('#load_balancefeesreport').html(response.render);
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
