<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            $pdf_preview_uri = base_url('terminalreport/pdf/'.$examID.'/'.$examcompilationID.'/'.$classesID.'/'.$sectionID.'/'.$studentIDD.'/'.$rankingType);
            echo btn_printReport('terminalreport', $this->lang->line('report_print'), 'printablediv');
            //echo btn_pdfPreviewReport('terminalreport',$pdf_preview_uri, $this->lang->line('report_pdf_preview'));
            //echo btn_sentToMailReport('terminalreport', $this->lang->line('report_send_pdf_to_mail'));
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
        <?=$this->lang->line('terminalreport_report_for')?> <?=$this->lang->line('terminalreport_terminal')?> - <?=$examName?> <?=isset($classes[$classesID]) ? "( ".$classes[$classesID]." ) " : ''?>
        </h3>
    </div><!-- /.box-header -->
    <div id="printablediv">
        <style type="text/css">
            .mainterminalreport{
                margin: 0px;
                overflow: hidden;
                border:1px solid #ddd;
                max-width:794px;
                margin: 0px auto;
                margin-bottom: 10px;
                padding:30px;
            }

            .terminal-headers{
                border-bottom: 1px solid #ddd;
                overflow: hidden;
                padding-bottom: 10px;
                vertical-align: middle;
                margin-bottom: 4px;
            }

            .terminal-logo {
                float: left;
            }

            .terminal-headers img{
                width: 60px;
                height: 60px;
            }

            .school-name h2{
                padding-left: 20px;
                padding-top: 7px;
                font-weight: bold;
                float: left;
            }

            .terminal-infos {
                width: 100%;
                overflow: hidden;
            }

            .terminal-infos h3{
                padding: 2px 0px;
                margin: 0px;
            }

            .terminal-infos p{
                margin-bottom: 3px;
                font-size: 15px;
            }

            .school-address{
                float: left;
                width: 40%;
            }

            .student-profile {
                float: left;
                width: 40%;

            }

            .student-profile-img {
                float: left;
                width: 20%;
                text-align: right;
            }

            .student-profile-img img {
                width: 120px;
                height: 120px;
                border: 1px solid #ddd;
                margin-top: 5px;
                margin-right: 2px;
            }

            @media screen and (max-width: 480px) {
                .school-name h2{
                    padding-left: 0px;
                    float: none;
                }

                .school-address {
                    width: 100%;
                }

                .student-profile {
                    width: 100%;
                }

                .student-profile-img  {
                    margin-top: 10px;
                    width: 100%;
                }

                .student-profile-img img {
                    width: 100%;
                    height: 100%;
                    margin: 10px 0px;
                }
            }

            .terminal-contents {
                width: 100%;
                overflow: hidden;
            }

            .terminal-contents table {
                width: 100%;
            }

            .terminal-contents table tr,.terminal-contents table td,.terminal-contents table th {
                border:1px solid #ddd;
                padding: 8px 2px;
                font-size: 14px;
                text-align: center;
            }

            @media print {
                .mainterminalreport{
                    border:0px solid #ddd;
                    padding: 0px 20px;
                }

                .student-profile-img img {
                    margin-right: 5px !important;
                }

                .terminal-contents table td,.terminal-contents table th {
                    font-size: 12px;
                }
            }

            .container, .container2, .container3 {
                min-width: 310px;
                max-width: 800px;
                height: 400px;
                margin: 0 auto;
            }
        </style>
        <div class="box-body" style="margin-bottom: 50px;">
            <div class="row">
                <div class="col-sm-12">
                <?php if(customCompute($studentLists)) { foreach($studentLists as $student) { ?>
                    <div class="mainterminalreport">
                        <div class="terminal-headers">
                            <div class="terminal-logo">
                                <img src="<?=base_url("uploads/images/$siteinfos->photo")?>" alt="">
                            </div>
                            <div class="school-name">
                                <h2><?=$siteinfos->sname?></h2>
                            </div>
                        </div>
                        <div class="terminal-infos">
                            <div class="school-address">
                                <h4><b><?=$siteinfos->sname?></b></h4>
                                <p><?=$siteinfos->address?></p>
                                <p><?=$this->lang->line('terminalreport_phone')?> : <?=$siteinfos->phone?></p>
                                <p><?=$this->lang->line('terminalreport_email')?> : <?=$siteinfos->email?></p>
                            </div>
                            <div class="student-profile">
                                <h4><b><?=$student->srname?></b></h4>
                                <p><?=$this->lang->line('terminalreport_academic_year')?> : <b><?=$schoolyearsessionobj->schoolyear;?></b>
                                <p><?=$this->lang->line('terminalreport_position_in_class')?> :  <b><?=customCompute($studentPosition['studentClassPositionArray'][$examID]) ? addOrdinalNumberSuffix((int)array_search($student->srstudentID, array_keys($studentPosition['studentClassPositionArray'][$examID])) + 1) : ''?></b></p>
                                <p><?=$this->lang->line('terminalreport_reg_no')?> : <b><?=$student->srstudentID?></b>, <?=$this->lang->line('terminalreport_class')?> : <b><?=isset($classes[$student->srclassesID]) ? $classes[$student->srclassesID] : ''?></b></p>
                                <p><?=$this->lang->line('terminalreport_section')?> : <b><?=isset($sections[$student->srsectionID]) ? $sections[$student->srsectionID] : ''?></b></p>
                                <p><?=$this->lang->line('terminalreport_group')?> : <b><?=isset($groups[$student->srstudentgroupID]) ? $groups[$student->srstudentgroupID] : ''?></b></p>
                                <p><?=$this->lang->line('terminalreport_exam')?> : <b><?=$examName?></b></p>
                            </div>
                            <div class="student-profile-img">
                                <img src="<?=imagelink($student->photo)?>" alt="">
                            </div>
                        </div>
                        <div class="terminal-contents terminalreporttable">
                            <h4><b><?=$this->lang->line('terminalreport_terminal_report')?></b></h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('terminalreport_subjects');?></th>
                                        <?php
                                            reset($markpercentagesArr);
                                            $firstindex          = key($markpercentagesArr);
                                            $uniquepercentageArr = isset($markpercentagesArr[$firstindex]) ? $markpercentagesArr[$firstindex] : [];
                                            $markpercentages     = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
                                            if(customCompute($markpercentages)) {
                                                foreach($markpercentages as $markpercentageID) {
                                                    $markpercentage = isset($percentageArr[$markpercentageID]) ? $percentageArr[$markpercentageID]->markpercentagetype : '';
                                                    echo "<th>".$markpercentage."</th>";
                                            } }
                                        ?>
                                        <th><?=$this->lang->line('terminalreport_total')?></th>
                                        <th><?=$this->lang->line('terminalreport_target')?></th>
                                        <th><?=$this->lang->line('terminalreport_position_subject');?></th>
                                        <th><?=$this->lang->line('terminalreport_grade');?></th>
                                        <th><?=$this->lang->line('terminalreport_rubric');?></th>
                                        <th><?=$this->lang->line('terminalreport_ranked');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(customCompute($subjects)) { foreach($subjects as $subject) {
                                            if(isset($studentPosition[$examID][$student->srstudentID]['subjectMark'][$subject->subjectID])) {
                                                $uniquepercentageArr =  isset($markpercentagesArr[$subject->subjectID]) ? $markpercentagesArr[$subject->subjectID] : [];
                                            ?>
                                            <tr>
                                                <td><?=$subject->subject?></td>
                                                <?php
                                                $percentageMark  = 0;
                                                if(customCompute($markpercentages)) {
                                                    foreach($markpercentages as $markpercentageID) {
                                                        $f = false;
                                                        if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
                                                            $f = true;
                                                            $percentageMark   += isset($percentageArr[$markpercentageID]) ? $percentageArr[$markpercentageID]->percentage : 0;
                                                        }
                                                        ?>
                                                        <td>
                                                            <?php
                                                                if(isset($studentPosition[$examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID]) && $f) {
                                                                    echo $studentPosition[$examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
                                                                } else {
                                                                    if($f) {
                                                                        echo 'N/A';
                                                                    }
                                                                }
                                                            ?>
                                                        </td>
                                                <?php } } ?>
                                                <td><?=isset($studentPosition[$examID][$student->srstudentID]['subjectMark'][$subject->subjectID]) ? $studentPosition[$examID][$student->srstudentID]['subjectMark'][$subject->subjectID] : '0'?></td>
                                                <td><?=$subject->targetmark?></td>
                                                <td>
                                                    <?=isset($studentPosition['studentSubjectPositionMark'][$subject->subjectID][$examID]) ? addOrdinalNumberSuffix((int)array_search($student->srstudentID, array_keys($studentPosition['studentSubjectPositionMark'][$subject->subjectID][$examID])) + 1) : '';?> / <?=$outof?>
                                                </td>
                                                <?php
                                                $subjectMark = isset($studentPosition[$examID][$student->srstudentID]['subjectMark'][$subject->subjectID]) ? $studentPosition[$examID][$student->srstudentID]['subjectMark'][$subject->subjectID] : '0';
                                                $subjectMark = markCalculationView($subjectMark, $subject->finalmark, $percentageMark);
                                                if(customCompute($grades)) { foreach($grades as $grade) {
                                                    if(($grade->gradefrom <= $subjectMark) && ($grade->gradeupto >= $subjectMark)) { ?>
                                                        <td><?=$grade->grade?></td>
                                                        <td><?=$grade->note?></td>
                                                <?php } } } ?>
                                                <td><?=!empty($rankingSubjects) && !in_array($subject->subjectID, $rankingSubjects) ? 'No' : 'Yes'?></td>
                                            </tr>
                                            <tr>
                                              <td colspan="7" style="text-align:left"><?=$subject->name?> : <?=$marks[$examID][$student->srstudentID][$subject->subjectID]['teacher_comment']?></td>
                                            </tr>
                                        <?php }
                                        } }  ?>
                                    <tr>
                                        <td><b><?=$this->lang->line('terminalreport_total')?></b></td>
                                        <?php if(customCompute($markpercentages)) {
                                            foreach($markpercentages as $markpercentageID) { ?>
                                                <td><b><?=isset($studentPosition[$examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID]) ? $studentPosition[$examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] : '0'?></b></td>
                                        <?php } } ?>
                                        <td><b><?=isset($studentPosition[$examID][$student->srstudentID]['totalSubjectMark']) ? $studentPosition[$examID][$student->srstudentID]['totalSubjectMark'] : '0'?></b></td>
                                        <td></td>
                                        <td><b><?=customCompute($studentPosition['studentClassPositionArray'][$examID]) ? addOrdinalNumberSuffix((int)array_search($student->srstudentID, array_keys($studentPosition['studentClassPositionArray'][$examID])) + 1) : ''?></b></p></td>
                                        <?php
                                          if(isset($studentPosition[$examID][$student->srstudentID]['classPositionMark']))
                                            $averageMark = ini_round($studentPosition[$examID][$student->srstudentID]['classPositionMark']);
                                          else
                                            $averageMark = 0;
                                          if(customCompute($grades)) { foreach($grades as $grade) {
                                            if(($grade->gradefrom <= $averageMark) && ($grade->gradeupto >= $averageMark)) { ?>
                                                <td><?=$grade->grade?></td>
                                        <?php } } } ?>
                                    </tr>

                                    <tr>
                                        <td colspan="<?=($col-3)?>"><b><?=$this->lang->line('terminalreport_mark_average')?> : <?=isset($studentPosition[$examID][$student->srstudentID]['classPositionMark']) ? ini_round($studentPosition[$examID][$student->srstudentID]['classPositionMark']) : '0.00'?></b></td>
                                        <td colspan="3"><b><?=$this->lang->line('terminalreport_class_average')?> :
                                            <?php
                                                if(isset($studentPosition[$examID][$student->srstudentID]['classPositionMark']) && $studentPosition[$examID][$student->srstudentID]['classPositionMark'] > 0 && isset($studentPosition['totalStudentMarkAverage'][$examID])) {
                                                    echo ini_round($studentPosition['totalStudentMarkAverage'][$examID] / customCompute($studentPosition[$examID]));
                                                } else {
                                                    echo "0.00";
                                                }
                                            ?></b>
                                        </td>
                                        <td colspan="<?=($col-3)?>"><b><?=$this->lang->line('terminalreport_improvement')?> : <?=isset($studentPosition[$examID][$student->srstudentID]['classPositionMark']) && isset($compareStudentPosition[$student->srstudentID]['classPositionMark']) && $studentPosition[$examID][$student->srstudentID]['classPositionMark'] > 0 ? ini_round(($studentPosition[$examID][$student->srstudentID]['classPositionMark']-$compareStudentPosition[$student->srstudentID]['classPositionMark'])/$studentPosition[$examID][$student->srstudentID]['classPositionMark']*100) : '0.00'?></b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b><?=$this->lang->line('terminalreport_promoted_to');?></b></td>
                                        <td colspan="<?=($col-2)?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><?=$this->lang->line('terminalreport_attendance');?></td>
                                        <td colspan="<?=($col-2)?>"><?=isset($attendance[$student->srstudentID]) ? $attendance[$student->srstudentID] : '0'?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><?=$this->lang->line('terminalreport_from_teacher_remarks')?></td>
                                        <td colspan="<?=($col-2)?>"><?=$remarks[$examID][$student->srstudentID]->form_teacher?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><?=$this->lang->line('terminalreport_house_teacher_remarks')?></td>
                                        <td colspan="<?=($col-2)?>"><?=$remarks[$examID][$student->srstudentID]->house_teacher?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><?=$this->lang->line('terminalreport_principal_remarks')?></td>
                                        <td colspan="<?=($col-2)?>"><?=$remarks[$examID][$student->srstudentID]->principal_teacher?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="<?=$col?>"><?=$this->lang->line('terminalreport_interpretation')?> :
                                            <b>
                                                <?php if(customCompute($grades)) { $i = 1; foreach($grades as $grade) {
                                                    if(customCompute($grades) == $i) {
                                                        echo $grade->gradefrom.'-'.$grade->gradeupto." = ".$grade->point." [".$grade->grade."]";
                                                    } else {
                                                        echo $grade->gradefrom.'-'.$grade->gradeupto." = ".$grade->point." [".$grade->grade."], ";
                                                    }
                                                    $i++;
                                                }}?>
                                            </b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="<?=$student->srstudentID?>" class="charts">
                          <div style="break-after:page"></div>
                          <div class="container"></div>
                          <div style="break-after:page"></div>
                          <div class="container2"></div>
                          <div style="break-after:page"></div>
                          <div class="container3"></div>
                        </div>
                    </div>
                    <p style="page-break-after: always;">&nbsp;</p>
                    <?php
                        $this->load->view("report/terminal/SubjectPerformanceChartJavascript.php", array('studentID' => $student->srstudentID));
                        $this->load->view("report/terminal/ExamPerformanceChartJavascript.php", array('studentID' => $student->srstudentID));
                        $this->load->view("report/terminal/PositionPerformanceChartJavascript.php", array('studentID' => $student->srstudentID));
                        } } else { ?>
                        <div class="callout callout-danger">
                            <p><b class="text-info"><?=$this->lang->line('terminalreport_data_not_found')?></b></p>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- row -->
        </div>
    </div>
</div>


<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?=base_url('terminalreport/send_pdf_to_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=$this->lang->line('terminalreport_close')?></span></button>
                <h4 class="modal-title"><?=$this->lang->line('terminalreport_mail')?></h4>
            </div>
            <div class="modal-body">

                <?php
                    if(form_error('to'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="to" class="col-sm-2 control-label">
                        <?=$this->lang->line("terminalreport_to")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("terminalreport_subject")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("terminalreport_message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("terminalreport_send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>
<!-- email end here -->

<script type="text/javascript">

    $('.terminalreporttable').mCustomScrollbar({
        axis:"x"
    });

    function check_email(email) {
        var status = false;
        var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (email.search(emailRegEx) == -1) {
            $("#to_error").html('');
            $("#to_error").html("<?=$this->lang->line('terminalreport_mail_valid')?>").css("text-align", "left").css("color", 'red');
        } else {
            status = true;
        }
        return status;
    }


    $('#send_pdf').click(function() {
        var field = {
            'to'         : $('#to').val(),
            'subject'    : $('#subject').val(),
            'message'    : $('#message').val(),
            'examID'     : '<?=$examID?>',
            'classesID'  : '<?=$classesID?>',
            'sectionID'  : '<?=$sectionID?>',
            'studentID'  : '<?=$studentIDD?>',
        };

        var to = $('#to').val();
        var subject = $('#subject').val();
        var error = 0;

        $("#to_error").html("");
        $("#subject_error").html("");

        if(to == "" || to == null) {
            error++;
            $("#to_error").html("<?=$this->lang->line('terminalreport_mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        }

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("<?=$this->lang->line('terminalreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('terminalreport/send_pdf_to_mail')?>",
                data: field,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.status == false) {
                        $('#send_pdf').removeAttr('disabled');
                        if( response.to) {
                            $("#to_error").html("<?=$this->lang->line('terminalreport_mail_to')?>").css("text-align", "left").css("color", 'red');
                        }

                        if( response.subject) {
                            $("#subject_error").html("<?=$this->lang->line('terminalreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
                        }

                        if(response.message) {
                            toastr["error"](response.message)
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
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });
</script>
