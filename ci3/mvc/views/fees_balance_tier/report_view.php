<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-balancefeesreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_fees_balance_tier')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">

            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("fees_balance_tier_class")?></label>
                    <?php
                        $classesArray = array(
                            "0" => $this->lang->line("fees_balance_tier_please_select"),
                        );
                        foreach ($classes as $classaKey => $classa) {
                            $classesArray[$classa->classesID] = $classa->classes;
                        }
                        echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="groupDiv">
                    <label><?=$this->lang->line("fees_balance_tier_group")?></label>
                    <?php
                        $groupArray = array(
                            "0" => $this->lang->line("fees_balance_tier_please_select"),
                        );
            						foreach ($groups as $group) {
            							$groupArray[$group->studentgroupID] = $group->group;
            						}
                        echo form_dropdown("studentgroupID", $groupArray, set_value("studentgroupID"), "id='studentgroupID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="studentDiv">
                    <label><?=$this->lang->line("fees_balance_tier_student")?></label>
                    <?php
                        $studentArray = array(
                            "0" => $this->lang->line("fees_balance_tier_please_select"),
                        );
            						if(customCompute($students)) {
            							foreach ($students as $student) {
            								$studentArray[$student->srstudentID] = $student->srname.' - '.$this->lang->line('fees_balance_tier_registerno').' - '.$student->srstudentID;
            							}
            						}
                        echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
                     ?>
                </div>

                <div class="col-sm-4 form-group" >
      							<label for="feesBalanceTier" class="control-label"><?=$this->lang->line("fees_balance_tier")?></label>

    							<?php
    								$feesBalanceTierArray = array("" => $this->lang->line("fees_balance_tier_please_select"));
    								foreach ($fees_balance_tiers as $fees_balance_tier) {
    									$feesBalanceTierArray[$fees_balance_tier->name] = $fees_balance_tier->name;
    								}
    								echo form_dropdown("feesBalanceTier", $feesBalanceTierArray, set_value("feesBalanceTier", $set_feesBalanceTier), "id='feesBalanceTier' class='form-control select2'");
    							?>
    						</div>

            </div>

			<div class="row">
				<div class="col-sm-12">

				</div>
			</div>
			<div class="row">
				<div class="col-md-2 pull-right">
					<button id="get_fees_balance_tier_report" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("submit")?></button>
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
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('fees_balance_tier_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    $('.select2').select2();

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

    $(document).on('click','#get_fees_balance_tier_report', function() {
      $('#load_balancefeesreport').html("");
      var classesID = $('#classesID').val();
      var studentID = $('#studentID').val();
      var studentgroupID = $('#studentgroupID').val();
      var feesBalanceTier = $('#feesBalanceTier').val();

      var error = 0;

      var field = {
        "classesID"       : classesID,
        "studentgroupID"  : studentgroupID,
        "studentID"       : studentID,
        "feesBalanceTier" : feesBalanceTier,
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
            url: "<?=base_url('fees_balance_tier/getFeesBalanceTierReport')?>",
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
