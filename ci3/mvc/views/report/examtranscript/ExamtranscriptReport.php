<?php
    $examIDs               = isset($examIDs) ? (array) $examIDs : array();
    $examCompilationIDs    = isset($examCompilationIDs) ? (array) $examCompilationIDs : array();
    $encodedExamIDs        = base64_encode(json_encode($examIDs));
    $encodedCompilationIDs = base64_encode(json_encode($examCompilationIDs));
    $includeGuardian       = isset($include_guardian) ? $include_guardian : TRUE;
    $includeAddress        = isset($include_address) ? $include_address : TRUE;
    $includeStudentAddress = isset($include_student_address) ? $include_student_address : FALSE;
    $selectedExamCompilations = isset($selectedExamCompilations) ? $selectedExamCompilations : array();
    $selectedExamRanking   = isset($selectedExamRanking) ? $selectedExamRanking : NULL;
    $datasets              = isset($datasets) ? $datasets : array();
    $datasetMarks          = isset($datasetMarks) ? $datasetMarks : array();
    $datasetMode           = isset($datasetMode) ? $datasetMode : 'exam';
    $enableExamComparison  = isset($enableExamComparison) ? (bool) $enableExamComparison : FALSE;
    $enableExamCompilationComparison = isset($enableExamCompilationComparison) ? (bool) $enableExamCompilationComparison : FALSE;
    $showSubjectPosition   = isset($showSubjectPosition) ? (bool) $showSubjectPosition : TRUE;
    $showClassPosition     = isset($showClassPosition) ? (bool) $showClassPosition : TRUE;
    $studentSubjectsData   = isset($studentSubjects) ? $studentSubjects : array();
    $datasetSubjectGrades  = isset($datasetSubjectGrades) ? $datasetSubjectGrades : array();
    $datasetSummaryGrades  = isset($datasetSummaryGrades) ? $datasetSummaryGrades : array();
    $remarksData           = isset($remarks) ? $remarks : array();
    $attendanceData        = isset($attendance) ? $attendance : array();
    $pdf_preview_uri       = base_url('examtranscriptreport/pdf/'.$encodedExamIDs.'/'.$encodedCompilationIDs.'/'.$classesID.'/'.$sectionID.'/'.$studentIDD.'/'.$examrankingID.'/'.$schoolyearID.'/'.($includeGuardian ? 1 : 0).'/'.($includeAddress ? 1 : 0).'/'.($includeStudentAddress ? 1 : 0).'/'.$datasetMode.'/'.($enableExamComparison ? 1 : 0).'/'.($enableExamCompilationComparison ? 1 : 0));
    $pdf_preview_uri      .= '/'.($showSubjectPosition ? 1 : 0).'/'.($showClassPosition ? 1 : 0);
?>
<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            echo btn_printReport('examtranscriptreport', $this->lang->line('report_print'), 'printablediv');
            echo btn_pdfPreviewReport('examtranscriptreport', $pdf_preview_uri, $this->lang->line('report_pdf_preview'));
            echo btn_sentToMailReport('examtranscriptreport', $this->lang->line('report_send_pdf_to_mail'));
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
        <?=$this->lang->line('examtranscriptreport_report_for')?> - <?=isset($classes[$classesID]) ? $classes[$classesID] : ''?>
        </h3>
    </div><!-- /.box-header -->
    <div id="printablediv">
        <style type="text/css">
            .examtranscript-report {
                margin: 0 auto 20px auto;
                border:1px solid #ddd;
                max-width: 900px;
                padding:25px;
                background-color: #fff;
            }
            .examtranscript-header {
                border-bottom: 1px solid #ddd;
                overflow: hidden;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .examtranscript-logo {
                float: left;
                width: 80px;
                text-align: center;
            }
            .examtranscript-logo img {
                max-width: 80px;
                max-height: 80px;
            }
            .examtranscript-title {
                float: left;
                width: calc(100% - 80px);
                padding-left: 20px;
            }
            .examtranscript-title h2 {
                margin:0;
                font-weight: bold;
            }
            .examtranscript-title h4 {
                margin-top:5px;
                color:#555;
            }
            .examtranscript-info {
                overflow: hidden;
            }
            .examtranscript-info .info-block {
                float:left;
                width:50%;
            }
            .examtranscript-info .info-block ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .examtranscript-info .info-block ul li {
                margin-bottom:5px;
                font-size:14px;
            }
            .examtranscript-info .student-photo {
                float:right;
                width:120px;
                text-align:right;
            }
            .examtranscript-info .student-photo img {
                width:120px;
                height:120px;
                border:1px solid #ddd;
            }
            .examtranscript-guardian {
                margin-top:20px;
            }
            .examtranscript-guardian table,
            .examtranscript-table table,
            .examtranscript-summary table,
            .examtranscript-table table th,
            .examtranscript-table table td,
            .examtranscript-summary table th,
            .examtranscript-summary table td,
            .examtranscript-guardian table th,
            .examtranscript-guardian table td {
                border:1px solid #ddd;
                border-collapse: collapse;
            }
            .examtranscript-guardian table,
            .examtranscript-table table,
            .examtranscript-summary table {
                width:100%;
                margin-bottom:15px;
            }
            .examtranscript-guardian table th,
            .examtranscript-guardian table td,
            .examtranscript-table table th,
            .examtranscript-table table td,
            .examtranscript-summary table th,
            .examtranscript-summary table td {
                padding:8px;
                font-size:13px;
                text-align:left;
            }
            .examtranscript-table table th,
            .examtranscript-summary table th,
            .examtranscript-guardian table th {
                background-color:#f5f5f5;
            }
            .examtranscript-table .exam-title {
                font-weight:bold;
                margin:20px 0 10px 0;
            }
            .examtranscript-summary h4,
            .examtranscript-guardian h4,
            .examtranscript-table h4,
            .examtranscript-chart h4 {
                font-weight:bold;
                margin-bottom:10px;
            }
            .examtranscript-chart {
                margin-top:25px;
            }
            .exam-progress-chart {
                width:100%;
                min-height:320px;
            }
            .text-muted {
                color:#777;
            }
            @media print {
                .examtranscript-report {
                    border:0;
                    padding:0 10px;
                }
                .examtranscript-info .info-block {
                    width:45%;
                }
                .examtranscript-info .student-photo {
                    width:100px;
                }
                .exam-progress-chart {
                    min-height:260px;
                }
            }
        </style>
        <div class="box-body" style="margin-bottom: 50px;">
            <div class="row">
                <div class="col-sm-12">
                <?php if(customCompute($studentLists)) { foreach($studentLists as $student) { if($studentIDD == $student->srstudentID || $studentIDD == 0) { ?>
                    <div class="examtranscript-report">
                        <div class="examtranscript-header">
                            <div class="examtranscript-logo">
                                <img src="<?=base_url("uploads/images/$siteinfos->photo")?>" alt="">
                            </div>
                            <div class="examtranscript-title">
                                <h2><?=$siteinfos->sname?></h2>
                                <h4><?=$this->lang->line('examtranscriptreport_student_transcript')?></h4>
                                <p><?=$siteinfos->address?></p>
                                <p><?=$this->lang->line('examtranscriptreport_phone')?> : <?=$siteinfos->phone?> | <?=$this->lang->line('examtranscriptreport_email')?> : <?=$siteinfos->email?></p>
                            </div>
                        </div>
                        <div class="examtranscript-info">
                            <div class="info-block">
                                <?php
                                    $studentRegNo = (isset($student->srregisterNO) && trim((string) $student->srregisterNO) !== '') ? trim((string) $student->srregisterNO) : $this->lang->line('examtranscriptreport_not_available');
                                    $studentAddressLine = NULL;
                                    if($includeStudentAddress) {
                                        $studentAddressLine = (isset($student->address) && trim((string) $student->address) !== '') ? trim((string) $student->address) : $this->lang->line('examtranscriptreport_not_available');
                                    }
                                    $compilationNames = $this->lang->line('examtranscriptreport_not_available');
                                    if(customCompute($selectedExamCompilations)) {
                                        $names = array();
                                        foreach($selectedExamCompilations as $compilation) {
                                            $names[] = $compilation->name;
                                        }
                                        if(customCompute($names)) {
                                            $compilationNames = implode(', ', $names);
                                        }
                                    }
                                ?>
                                <ul>
                                    <li><strong><?=$student->srname?></strong></li>
                                    <li><?=$this->lang->line('examtranscriptreport_academic_year')?> : <strong><?=$schoolyearsessionobj->schoolyear;?></strong></li>
                                    <li><?=$this->lang->line('examtranscriptreport_reg_no')?> : <strong><?=$studentRegNo?></strong></li>
                                    <li><?=$this->lang->line('examtranscriptreport_class')?> : <strong><?=isset($classes[$student->srclassesID]) ? $classes[$student->srclassesID] : ''?></strong></li>
                                    <li><?=$this->lang->line('examtranscriptreport_section')?> : <strong><?=isset($sections[$student->srsectionID]) ? $sections[$student->srsectionID] : ''?></strong></li>
                                    <li><?=$this->lang->line('examtranscriptreport_group')?> : <strong><?=isset($groups[$student->srstudentgroupID]) ? $groups[$student->srstudentgroupID] : ''?></strong></li>
                                    <?php if($studentAddressLine !== NULL) { ?><li><?=$this->lang->line('examtranscriptreport_student_address')?> : <strong><?=$studentAddressLine?></strong></li><?php } ?>
                                </ul>
                            </div>
                            <div class="info-block">
                                <ul>
                                    <li><?=$this->lang->line('examtranscriptreport_examcompilation')?> : <strong><?=$compilationNames?></strong></li>
                                    <li><?=$this->lang->line('examtranscriptreport_examranking')?> : <strong><?=isset($selectedExamRanking->name) ? $selectedExamRanking->name : $this->lang->line('examtranscriptreport_not_available')?></strong></li>
                                </ul>
                            </div>
                            <div class="student-photo">
                                <img src="<?=imagelink($student->photo)?>" alt="">
                            </div>
                        </div>
                        <?php $studentGuardianContacts = isset($guardianContacts[$student->srstudentID]) ? $guardianContacts[$student->srstudentID] : array(); ?>
                        <?php if($includeGuardian && customCompute($studentGuardianContacts)) { ?>
                        <div class="examtranscript-guardian">
                            <h4><?=$this->lang->line('examtranscriptreport_guardian_details')?></h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('examtranscriptreport_guardian_name')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_guardian_relation')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_guardian_phone')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_guardian_email')?></th>
                                        <?php if($includeAddress) { ?><th><?=$this->lang->line('examtranscriptreport_guardian_address')?></th><?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($studentGuardianContacts as $contact) { ?>
                                    <tr>
                                        <td><?=($contact['name'] !== '') ? $contact['name'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                                        <td><?=($contact['relation'] !== '') ? $contact['relation'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                                        <td><?=($contact['phone'] !== '') ? $contact['phone'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                                        <td><?=($contact['email'] !== '') ? $contact['email'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                                        <?php if($includeAddress) { ?><td><?=($contact['address'] !== '') ? $contact['address'] : $this->lang->line('examtranscriptreport_not_available')?></td><?php } ?>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                        <div class="examtranscript-table">
                            <?php if(customCompute($datasets)) { foreach($datasets as $dataset) { $datasetKey = $dataset->key; ?>
                                <h4 class="exam-title"><?=$dataset->name?></h4>
                                <?php if($dataset->type === 'compilation' && customCompute($dataset->components)) { ?>
                                    <p class="text-muted">
                                        <?=$this->lang->line('examtranscriptreport_compilation_breakdown')?>:
                                        <?php
                                            $componentStrings = array();
                                            foreach($dataset->components as $component) {
                                                $componentName = isset($component['name']) && $component['name'] !== '' ? $component['name'] : $this->lang->line('examtranscriptreport_not_available');
                                                $weight = isset($component['weight']) ? ini_round($component['weight']) : 0;
                                                $componentStrings[] = $componentName.' ('.$weight.'%)';
                                            }
                                            echo customCompute($componentStrings) ? implode(', ', $componentStrings) : $this->lang->line('examtranscriptreport_not_available');
                                        ?>
                                    </p>
                                <?php } ?>
                                <?php $subjectColumnCount = $showSubjectPosition ? 6 : 5; ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?=$this->lang->line('examtranscriptreport_subjects')?></th>
                                            <th><?=$this->lang->line('examtranscriptreport_mark')?></th>
                                            <th><?=$this->lang->line('examtranscriptreport_grade')?></th>
                                            <th><?=$this->lang->line('examtranscriptreport_class_average')?></th>
                                            <?php if($showSubjectPosition) { ?><th><?=$this->lang->line('examtranscriptreport_subject_position')?></th><?php } ?>
                                            <th><?=$this->lang->line('examtranscriptreport_teacher_comment')?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $subjectsForStudent = isset($studentSubjectsData[$student->srstudentID]) ? $studentSubjectsData[$student->srstudentID] : array(); ?>
                                        <?php if(customCompute($subjectsForStudent)) { foreach($subjectsForStudent as $subjectID => $subject) { ?>
                                            <tr>
                                                <td><?=$subject->subject?></td>
                                                <td>
                                                    <?php
                                                        $subjectMark = isset($studentPosition[$datasetKey][$student->srstudentID]['subjectMark'][$subjectID]) ? $studentPosition[$datasetKey][$student->srstudentID]['subjectMark'][$subjectID] : NULL;
                                                        echo ($subjectMark !== NULL) ? ini_round($subjectMark) : $this->lang->line('examtranscriptreport_not_available');
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $subjectGrade = isset($datasetSubjectGrades[$datasetKey][$student->srstudentID][$subjectID]) ? $datasetSubjectGrades[$datasetKey][$student->srstudentID][$subjectID] : NULL;
                                                        echo ($subjectGrade !== NULL && $subjectGrade !== '') ? $subjectGrade : $this->lang->line('examtranscriptreport_not_available');
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $classAverage = isset($classSubjectAverages[$datasetKey][$subjectID]) ? $classSubjectAverages[$datasetKey][$subjectID] : NULL;
                                                        echo ($classAverage !== NULL) ? ini_round($classAverage) : $this->lang->line('examtranscriptreport_not_available');
                                                    ?>
                                                </td>
                                                <?php if($showSubjectPosition) { ?>
                                                <td>
                                                    <?php
                                                        $position = isset($subjectPositions[$datasetKey][$subjectID][$student->srstudentID]) ? $subjectPositions[$datasetKey][$subjectID][$student->srstudentID] : NULL;
                                                        echo ($position !== NULL) ? addOrdinalNumberSuffix($position) : $this->lang->line('examtranscriptreport_not_available');
                                                    ?>
                                                </td>
                                                <?php } ?>
                                                <td>
                                                    <?php
                                                        $teacherComment = isset($datasetMarks[$datasetKey][$student->srstudentID][$subjectID]['teacher_comment']) ? $datasetMarks[$datasetKey][$student->srstudentID][$subjectID]['teacher_comment'] : '';
                                                        echo ($teacherComment !== '') ? $teacherComment : $this->lang->line('examtranscriptreport_not_available');
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } } else { ?>
                                            <tr>
                                                <td colspan="<?=$subjectColumnCount?>" class="text-muted"><?=$this->lang->line('examtranscriptreport_data_not_found')?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } } else { ?>
                                <p class="text-muted"><?=$this->lang->line('examtranscriptreport_data_not_found')?></p>
                            <?php } ?>
                        </div>
                        <?php $studentExamSummary = isset($examSummaries[$student->srstudentID]) ? $examSummaries[$student->srstudentID] : array(); ?>
                        <?php if(customCompute($datasets)) { ?>
                        <div class="examtranscript-summary">
                            <h4><?=$this->lang->line('examtranscriptreport_overall_summary')?></h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('examtranscriptreport_exam')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_total_mark')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_average_mark')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_grade')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_class_average')?></th>
                                        <th><?=$this->lang->line('examtranscriptreport_difference')?></th>
                                        <?php if($showClassPosition) { ?><th><?=$this->lang->line('examtranscriptreport_overall_position')?></th><?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($datasets as $dataset) { $datasetKey = $dataset->key; $summary = isset($studentExamSummary[$datasetKey]) ? $studentExamSummary[$datasetKey] : NULL; ?>
                                        <tr>
                                            <td><?=$dataset->name?></td>
                                            <td><?=($summary && $summary['totalMark'] !== NULL) ? ini_round($summary['totalMark']) : $this->lang->line('examtranscriptreport_not_available')?></td>
                                            <td><?=($summary && $summary['averageMark'] !== NULL) ? ini_round($summary['averageMark']) : $this->lang->line('examtranscriptreport_not_available')?></td>
                                            <?php $summaryGrade = isset($datasetSummaryGrades[$student->srstudentID][$datasetKey]) ? $datasetSummaryGrades[$student->srstudentID][$datasetKey] : NULL; ?>
                                            <td><?=($summaryGrade !== NULL && $summaryGrade !== '') ? $summaryGrade : $this->lang->line('examtranscriptreport_not_available')?></td>
                                            <td><?=($summary && $summary['classAverage'] !== NULL) ? ini_round($summary['classAverage']) : $this->lang->line('examtranscriptreport_not_available')?></td>
                                            <td>
                                                <?php
                                                    if($summary && $summary['difference'] !== NULL) {
                                                        $sign = ($summary['difference'] > 0) ? '+' : '';
                                                        echo $sign.ini_round($summary['difference']);
                                                    } else {
                                                        echo $this->lang->line('examtranscriptreport_not_available');
                                                    }
                                                ?>
                                            </td>
                                            <?php if($showClassPosition) { ?>
                                            <td><?=($summary && $summary['position'] !== NULL) ? addOrdinalNumberSuffix($summary['position']) : $this->lang->line('examtranscriptreport_not_available')?></td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                        <?php $studentAttendance = isset($attendanceData[$student->srstudentID]) ? $attendanceData[$student->srstudentID] : ''; ?>
                        <?php $studentRemark = isset($remarksData[$student->srstudentID]) ? $remarksData[$student->srstudentID] : NULL; ?>
                        <?php if($studentAttendance !== '' && $studentAttendance !== NULL) { ?>
                        <div class="examtranscript-guardian">
                            <h4><?=$this->lang->line('examtranscriptreport_attendance')?></h4>
                            <p><?=$studentAttendance?></p>
                        </div>
                        <?php } ?>
                        <?php
                            $hasRemark = FALSE;
                            if($studentRemark) {
                                foreach(array('form_teacher', 'house_teacher', 'principal_teacher') as $remarkField) {
                                    if(isset($studentRemark->$remarkField) && trim((string) $studentRemark->$remarkField) !== '') {
                                        $hasRemark = TRUE;
                                        break;
                                    }
                                }
                            }
                        ?>
                        <?php if($hasRemark) { ?>
                        <div class="examtranscript-guardian">
                            <h4><?=$this->lang->line('examtranscriptreport_remarks')?></h4>
                            <table>
                                <tbody>
                                    <tr>
                                        <th><?=$this->lang->line('examtranscriptreport_form_teacher')?></th>
                                        <td><?=trim((string) $studentRemark->form_teacher) !== '' ? $studentRemark->form_teacher : $this->lang->line('examtranscriptreport_not_available')?></td>
                                    </tr>
                                    <tr>
                                        <th><?=$this->lang->line('examtranscriptreport_house_teacher')?></th>
                                        <td><?=trim((string) $studentRemark->house_teacher) !== '' ? $studentRemark->house_teacher : $this->lang->line('examtranscriptreport_not_available')?></td>
                                    </tr>
                                    <tr>
                                        <th><?=$this->lang->line('examtranscriptreport_principal_teacher')?></th>
                                        <td><?=trim((string) $studentRemark->principal_teacher) !== '' ? $studentRemark->principal_teacher : $this->lang->line('examtranscriptreport_not_available')?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                        <div class="examtranscript-chart">
                            <h4><?=$this->lang->line('examtranscriptreport_progress_chart_title')?></h4>
                            <div id="exam-progress-chart-<?=$student->srstudentID?>" class="exam-progress-chart"></div>
                            <?php
                                $chartDataAvailable = isset($chartSeries[$student->srstudentID]['studentScores']) ? array_filter($chartSeries[$student->srstudentID]['studentScores'], function($value) { return $value !== NULL; }) : array();
                                if(!customCompute($chartDataAvailable)) {
                                    echo '<p class="text-muted">'.$this->lang->line('examtranscriptreport_data_not_found').'</p>';
                                }
                            ?>
                        </div>
                    </div>
                    <p style="page-break-after: always;">&nbsp;</p>
                <?php } } } else { ?>
                    <div class="callout callout-danger">
                        <p><b class="text-info"><?=$this->lang->line('examtranscriptreport_data_not_found')?></b></p>
                    </div>
                <?php } ?>
                </div>
            </div><!-- row -->
        </div>
    </div>
</div>

<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?=base_url('examtranscriptreport/send_pdf_to_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=$this->lang->line('examtranscriptreport_close')?></span></button>
                <h4 class="modal-title"><?=$this->lang->line('examtranscriptreport_mail')?></h4>
            </div>
            <div class="modal-body">

                <?php
                    if(form_error('to'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="to" class="col-sm-2 control-label">
                        <?=$this->lang->line("examtranscriptreport_to")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("examtranscriptreport_subject")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("examtranscriptreport_message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('examtranscriptreport_close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("examtranscriptreport_send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>
<!-- email end here -->

<?php $this->load->view('report/examtranscript/ExamTranscriptChartJavascript'); ?>

<script type="text/javascript">
    function check_email(email) {
        var status = false;
        var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (email.search(emailRegEx) == -1) {
            $("#to_error").html('');
            $("#to_error").html("<?=$this->lang->line('examtranscriptreport_mail_valid')?>").css("text-align", "left").css("color", 'red');
        } else {
            status = true;
        }
        return status;
    }


    $('#send_pdf').click(function() {
        var field = {
            'to'                 : $('#to').val(),
            'subject'            : $('#subject').val(),
            'message'            : $('#message').val(),
            'examID[]'           : <?=json_encode(array_values($examIDs))?>,
            'classesID'          : '<?=$classesID?>',
            'sectionID'          : '<?=$sectionID?>',
            'studentID'          : '<?=$studentIDD?>',
            'examcompilationIDs[]' : <?=json_encode(array_values($examCompilationIDs))?>,
            'examrankingID'      : '<?=$examrankingID?>',
            'schoolyearID'       : '<?=$schoolyearID?>',
            'include_guardian'   : '<?=($includeGuardian ? 1 : 0)?>',
            'include_address'    : '<?=($includeAddress ? 1 : 0)?>',
            'include_student_address' : '<?=($includeStudentAddress ? 1 : 0)?>',
            'datasetMode'        : '<?=$datasetMode?>',
            'enableExamComparison' : '<?=($enableExamComparison ? 1 : 0)?>',
            'enableExamCompilationComparison' : '<?=($enableExamCompilationComparison ? 1 : 0)?>'
        };

        var to = $('#to').val();
        var subject = $('#subject').val();
        var error = 0;

        $("#to_error").html("");
        $("#subject_error").html("");

        if(to == "" || to == null) {
            error++;
            $("#to_error").html("<?=$this->lang->line('examtranscriptreport_mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        }

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("<?=$this->lang->line('examtranscriptreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('examtranscriptreport/send_pdf_to_mail')?>",
                data: field,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.status == false) {
                        $('#send_pdf').removeAttr('disabled');
                        if( response.to) {
                            $("#to_error").html("<?=$this->lang->line('examtranscriptreport_mail_to')?>").css("text-align", "left").css("color", 'red');
                        }

                        if( response.subject) {
                            $("#subject_error").html("<?=$this->lang->line('examtranscriptreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
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
