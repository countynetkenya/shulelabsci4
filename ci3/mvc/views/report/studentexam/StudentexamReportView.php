<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-tabulationsheetreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_studentexamreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <!--<div class="col-sm-12">-->
            <div class="form-group col-sm-4" id="schoolyearDiv">
                <label><?=$this->lang->line("studentexamreport_schoolyear")?><span class="text-red"> * </span></label>
                <?php
                    $schoolyearArray = array(
                        "0" => $this->lang->line("studentexamreport_please_select"),
                    );
                    if(customCompute($schoolyears)) {
                        foreach ($schoolyears as $schoolyearKey => $schoolyear) {
                            $schoolyearArray[$schoolyear->schoolyearID] = $schoolyear->schoolyear;
                        }
                    }
                    echo form_dropdown("schoolyearID", $schoolyearArray, set_value("schoolyearID"), "id='schoolyearID' class='form-control select2'");
                 ?>
            </div>
                <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("studentexamreport_class")?><span class="text-red"> * </span></label>
                    <?php
                        $classesArray = array(
                            "0" => $this->lang->line("studentexamreport_please_select"),
                        );
                        if(customCompute($classes)) {
                            foreach ($classes as $classaKey => $classa) {
                                $classesArray[$classa->classesID] = $classa->classes;
                            }
                        }
                        echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>
                <div class="form-group col-sm-4" id="examDiv">
                    <label><?=$this->lang->line("studentexamreport_exam")?><span class="text-red"> * </span></label>
                    <?php
                        $examArray = array(
                            "0" => $this->lang->line("studentexamreport_please_select"),
                        );
                        echo form_dropdown("examID", $examArray, set_value("examID"), "id='examID' class='form-control select2' multiple");
                     ?>
                </div>

            <!--</div>-->
        </div><!-- row -->
        <div class="row">
          <div class="form-group col-sm-4" id="sectionDiv">
              <label><?=$this->lang->line("studentexamreport_section")?></label>
              <?php
                  $sectionArray = array(
                      "0" => $this->lang->line("studentexamreport_please_select"),
                  );
                  echo form_dropdown("sectionID", $sectionArray, set_value("sectionID"), "id='sectionID' class='form-control select2'");
               ?>
          </div>

          <div class="form-group col-sm-4" id="studentDiv">
              <label><?=$this->lang->line("studentexamreport_student")?></label>
              <?php
                  $studentArray = array(
                      "0" => $this->lang->line("studentexamreport_please_select"),
                  );
                  echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
               ?>
          </div>
          <div class="col-sm-4">
              <button id="get_tabulationsheetreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("studentexamreport_submit")?></button>
          </div>
        </div>
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_tabulationsheetreport"></div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script type="text/javascript">

    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        var divElements = document.getElementById(divID).innerHTML;
        document.body.innerHTML = "<html><head><title></title></head><body>" + divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    $('.select2').select2();

    $(function(){
        $("#examID").val(0);
        $("#classesID").val(0);
        $("#sectionID").val(0);
        $("#studentID").val(0);
        $('#classesDiv').show('slow');
        $('#examDiv').hide('slow');
        $('#sectionDiv').hide('slow');
        $('#studentDiv').hide('slow');
    });

    $(document).on('change', "#classesID", function() {
        $('#load_tabulationsheetreport').html("");
        var classesID = $(this).val();
        if(classesID == '0'){
            $('#examDiv').hide('slow');
            $('#sectionDiv').hide('slow');
            $('#studentDiv').hide('slow');
            $('#examID').html('<option value="0">'+"<?=$this->lang->line("studentexamreport_please_select")?>"+'</option>');
            $('#sectionID').html('<option value="0">'+"<?=$this->lang->line("studentexamreport_please_select")?>"+'</option>');
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("studentexamreport_please_select")?>"+'</option>');
        } else {
            $('#examDiv').show('slow');
            $('#sectionDiv').show('slow');
            $('#studentDiv').show('slow');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('studentexamreport/getExam')?>",
                data: {"classesID" : classesID},
                dataType: "html",
                success: function(data) {
                   $('#examID').html(data);
                   if($("#examID option").length >= 3) {
                     var options = [$("#examID option:nth-last-child(1)").val(), $("#examID option:nth-last-child(2)").val(), $("#examID option:nth-last-child(3)").val()];
                     $("#examID").val(options).change();
                   }
                }
            });
            $.ajax({
                type: 'POST',
                url: "<?=base_url('studentexamreport/getSection')?>",
                data: {"classesID" : classesID},
                dataType: "html",
                success: function(data) {
                   $('#sectionID').html(data);
                }
            });
            $.ajax({
                type: 'POST',
                url: "<?=base_url('studentexamreport/getStudent')?>",
                data: {"classesID": classesID},
                dataType: "html",
                success: function(data) {
                   $('#studentID').html(data);
                }
            });
        }
    });

    $(document).on('change',"#sectionID", function() {
        $('#load_tabulationsheetreport').html("");
        var sectionID = $(this).val();
        var classesID = $("#classesID").val();
        if(sectionID == '0') {
            $('#studentDiv').hide('slow');
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("studentexamreport_please_select")?>"+'</option>');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('studentexamreport/getStudent')?>",
                data: {"classesID": classesID, "sectionID": sectionID},
                dataType: "html",
                success: function(data) {
                   $('#studentID').html(data);
                }
            });
        }
    });

    $(document).on('change',"#studentID", function() {
        $('#load_tabulationsheetreport').html('');
    });

    $(document).on('click','#get_tabulationsheetreport', function() {
        $('#load_admitcardreport').html('');
        var passData;
        var error = 0;
        var field = {
            'examID'       : $("#examID").val(),
            'schoolyearID' : $('#schoolyearID').val(),
            'classesID'    : $('#classesID').val(),
            'sectionID'    : $('#sectionID').val(),
            'studentID'    : $('#studentID').val(),
        };

        if (field['examID'] == 0) {
            $('#examDiv').addClass('has-error');
            error++;
        } else {
            $('#examDiv').removeClass('has-error');
        }

        if (field['classesID'] == 0) {
            $('#classesDiv').addClass('has-error');
            error++;
        } else {
            $('#classesDiv').removeClass('has-error');
        }

        if (error == 0) {
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
            url: "<?=base_url('studentexamreport/getStudentexamReport')?>",
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
            $('#load_tabulationsheetreport').html(response.render);
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
