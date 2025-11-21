<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-tabulationsheetreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_classmeritlistreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="examDiv">
                    <label><?=$this->lang->line("classmeritlistreport_exam")?><span class="text-red"> * </span></label>
                    <?php
                        $examArray = array(
                            "0" => $this->lang->line("classmeritlistreport_please_select"),
                        );
                        if(customCompute($exams)) {
                            foreach ($exams as $examKey => $exam) {
                                $examArray[$exam->examID] = $exam->exam;
                            }
                        }
                        echo form_dropdown("examID", $examArray, set_value("examID"), "id='examID' class='form-control select2'");
                     ?>
                </div>
                <!--<div class="form-group col-sm-4" id="subjectDiv">
                    <label><?=$this->lang->line("classmeritlistreport_subject")?></label>
                    <?php
                        $subjectArray = array(
                            "0" => $this->lang->line("classmeritlistreport_please_select"),
                        );
                        echo form_dropdown("subjectID", $subjectArray, set_value("subjectID"), "id='subjectID' class='form-control select2'");
                     ?>
                </div>-->
                <div class="col-sm-4">
                    <button id="get_tabulationsheetreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("classmeritlistreport_submit")?></button>
                </div>
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_tabulationsheetreport"></div>


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
        //$("#subjectID").val(0);
        $('#examDiv').show('slow');
        //$('#subjectDiv').hide('slow');
    });

    $(document).on('change',"#subjectID", function() {
        $('#load_tabulationsheetreport').html('');
    });

    $(document).on('click','#get_tabulationsheetreport', function() {
        $('#load_admitcardreport').html('');
        var passData;
        var error = 0;
        var field = {
            'examID'    : $("#examID").val(),
            //'subjectID' : $('#subjectID').val(),
        };

        if (field['examID'] == 0) {
            $('#examDiv').addClass('has-error');
            error++;
        } else {
            $('#examDiv').removeClass('has-error');
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
            url: "<?=base_url('classmeritlistreport/getClassmeritlistReport')?>",
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
