<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_reportform')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="reporttypediv">
                    <label><?=$this->lang->line('reportform_report_type')?></label>
                    <?php
                        $report_types = array(
                            0 => $this->lang->line('reportform_please_select'),
                            'terminal' => $this->lang->line('reportform_terminal'),
                            'tabulationsheet' => $this->lang->line('reportform_tabulationsheet'),
                            'progresscard' => $this->lang->line('reportform_progresscard'),
                            'studentsession' => $this->lang->line('reportform_studentsession'),
                        );
                        echo form_dropdown("report_type", $report_types, set_value("report_type"), "id='report_type' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-4" id="classesdiv">
                    <label><?=$this->lang->line('reportform_class')?></label>
                    <?php
                        $classes_array = array(0 => $this->lang->line("reportform_please_select"));
                        if(customCompute($classes)) {
                            foreach ($classes as $class) {
                                $classes_array[$class->classesID] = $class->classes;
                            }
                        }
                        echo form_dropdown("classesID", $classes_array, set_value("classesID"), "id='classesID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-4" id="sectiondiv">
                    <label><?=$this->lang->line('reportform_section')?></label>
                    <select id="sectionID" name="sectionID" class="form-control select2">
                        <option value="0"><?=$this->lang->line('reportform_please_select')?></option>
                    </select>
                </div>

                <div class="form-group col-sm-4" id="studentdiv">
                    <label><?=$this->lang->line('reportform_student')?></label>
                    <select id="studentID" name="studentID" class="form-control select2">
                        <option value="0"><?=$this->lang->line('reportform_please_select')?></option>
                    </select>
                </div>

                <div class="form-group col-sm-4" id="examdiv">
                    <label><?=$this->lang->line('reportform_exam')?></label>
                    <?php
                        $exams_array = array(0 => $this->lang->line("reportform_please_select"));
                        if(customCompute($exams)) {
                            foreach ($exams as $exam) {
                                $exams_array[$exam->examID] = $exam->exam;
                            }
                        }
                        echo form_dropdown("examID", $exams_array, set_value("examID"), "id='examID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-4" id="examcompilationdiv">
                    <label><?=$this->lang->line('reportform_exam_compilation')?></label>
                    <?php
                        $examcompilations_array = array(0 => $this->lang->line("reportform_please_select"));
                        if(customCompute($examcompilations)) {
                            foreach ($examcompilations as $examcompilation) {
                                $examcompilations_array[$examcompilation->examcompilationID] = $examcompilation->examcompilation;
                            }
                        }
                        echo form_dropdown("examcompilationID", $examcompilations_array, set_value("examcompilationID"), "id='examcompilationID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-4" id="examrankingdiv">
                    <label><?=$this->lang->line('reportform_exam_ranking')?></label>
                    <select id="examrankingID" name="examrankingID" class="form-control select2">
                        <option value="0"><?=$this->lang->line('reportform_please_select')?></option>
                    </select>
                </div>

                <div class="col-sm-4">
                    <button id="get_report" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("reportform_get_report")?></button>
                </div>

            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_report"></div>

<script type="text/javascript">
    $('.select2').select2();

    function hideAll() {
        $('#classesdiv').hide();
        $('#sectiondiv').hide();
        $('#studentdiv').hide();
        $('#examdiv').hide();
        $('#examcompilationdiv').hide();
        $('#examrankingdiv').hide();
    }

    $(document).ready(function() {
        hideAll();

        $('#report_type').on('change', function() {
            var report_type = $(this).val();
            hideAll();
            if(report_type == 'terminal') {
                $('#classesdiv').show();
                $('#sectiondiv').show();
                $('#studentdiv').show();
                $('#examdiv').show();
                $('#examcompilationdiv').show();
                $('#examrankingdiv').show();
            } else if (report_type == 'tabulationsheet' || report_type == 'progresscard') {
                $('#classesdiv').show();
                $('#sectiondiv').show();
                $('#studentdiv').show();
                $('#examdiv').show();
            } else if (report_type == 'studentsession') {
                $('#studentdiv').show();
            }
        });

        $('#classesID').on('change', function() {
            var classesID = $(this).val();
            if(classesID == 0) {
                $('#sectionID').html('<option value="0"><?=$this->lang->line('reportform_please_select')?></option>');
                $('#studentID').html('<option value="0"><?=$this->lang->line('reportform_please_select')?></option>');
                $('#examrankingID').html('<option value="0"><?=$this->lang->line('reportform_please_select')?></option>');
                $('#sectionID').val(0);
                $('#studentID').val(0);
                $('#examrankingID').val(0);
            } else {
                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('reportform/getSection')?>",
                    data: {"classesID" : classesID},
                    dataType: "html",
                    success: function(data) {
                       $('#sectionID').html(data);
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('reportform/getStudent')?>",
                    data: {"classesID" : classesID},
                    dataType: "html",
                    success: function(data) {
                       $('#studentID').html(data);
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('reportform/getExamranking')?>",
                    data: {"classesID" : classesID},
                    dataType: "html",
                    success: function(data) {
                       $('#examrankingID').html(data);
                    }
                });
            }
        });

        $('#sectionID').on('change', function() {
            var sectionID = $(this).val();
            var classesID = $('#classesID').val();
            if(sectionID == 0) {
                $('#studentID').html('<option value="0"><?=$this->lang->line('reportform_please_select')?></option>');
                $('#studentID').val(0);
            } else {
                 $.ajax({
                    type: 'POST',
                    url: "<?=base_url('reportform/getStudent')?>",
                    data: {"classesID" : classesID, "sectionID" : sectionID},
                    dataType: "html",
                    success: function(data) {
                       $('#studentID').html(data);
                    }
                });
            }
        });

        $('#get_report').on('click', function() {
            var report_type = $('#report_type').val();
            var classesID = $('#classesID').val();
            var sectionID = $('#sectionID').val();
            var studentID = $('#studentID').val();
            var examID = $('#examID').val();
            var examcompilationID = $('#examcompilationID').val();
            var examrankingID = $('#examrankingID').val();

            if(report_type != 0) {
                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('reportform/get_report')?>",
                    data: {
                        'report_type': report_type,
                        'classesID': classesID,
                        'sectionID': sectionID,
                        'studentID': studentID,
                        'examID': examID,
                        'examcompilationID': examcompilationID,
                        'examrankingID': examrankingID
                    },
                    dataType: "json",
                    success: function(data) {
                        if(data.url) {
                            var embed = '<embed src="' + data.url + '" type="application/pdf" width="100%" height="600px">';
                            $('#load_report').html(embed);
                        } else {
                            $('#load_report').html('<div class="alert alert-danger">Could not generate report URL.</div>');
                        }
                    }
                });
            }
        });
    });
</script>