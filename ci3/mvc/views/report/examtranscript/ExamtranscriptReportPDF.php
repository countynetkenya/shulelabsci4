<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style type="text/css">
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color:#000;
            margin:0;
            padding:0;
        }
        .examtranscript-report {
            margin: 0 auto 20px auto;
            border:1px solid #ddd;
            padding:20px;
        }
        .examtranscript-header {
            border-bottom:1px solid #ddd;
            overflow:hidden;
            padding-bottom:15px;
            margin-bottom:20px;
        }
        .examtranscript-logo {
            float:left;
            width:80px;
            text-align:center;
        }
        .examtranscript-logo img {
            max-width:80px;
            max-height:80px;
        }
        .examtranscript-title {
            float:left;
            width:calc(100% - 80px);
            padding-left:20px;
        }
        .examtranscript-title h2 {
            margin:0;
        }
        .examtranscript-title h4 {
            margin:5px 0 0 0;
            color:#555;
        }
        .examtranscript-info:after {
            content:"";
            display:block;
            clear:both;
        }
        .examtranscript-info .info-block {
            float:left;
            width:45%;
        }
        .examtranscript-info .info-block ul {
            list-style:none;
            padding:0;
            margin:0;
        }
        .examtranscript-info .info-block ul li {
            margin-bottom:6px;
            font-size:13px;
        }
        .examtranscript-info .student-photo {
            float:right;
            width:110px;
            text-align:right;
        }
        .examtranscript-info .student-photo img {
            width:110px;
            height:110px;
            border:1px solid #ddd;
        }
        table {
            width:100%;
            border-collapse: collapse;
            margin-bottom:15px;
        }
        table th, table td {
            border:1px solid #ddd;
            padding:6px;
            font-size:12px;
            text-align:left;
        }
        table th {
            background-color:#f5f5f5;
        }
        .section-title {
            font-weight:bold;
            margin:15px 0 10px 0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<?php
    $include_guardian        = isset($include_guardian) ? $include_guardian : TRUE;
    $include_address         = $include_guardian ? (isset($include_address) ? $include_address : TRUE) : FALSE;
    $include_student_address = isset($include_student_address) ? $include_student_address : FALSE;
    $selectedExamCompilations = isset($selectedExamCompilations) ? $selectedExamCompilations : array();
    $selectedExamRanking     = isset($selectedExamRanking) ? $selectedExamRanking : NULL;
    $datasets                = isset($datasets) ? $datasets : array();
    $datasetMarks            = isset($datasetMarks) ? $datasetMarks : array();
    $showSubjectPosition     = isset($showSubjectPosition) ? (bool) $showSubjectPosition : TRUE;
    $showClassPosition       = isset($showClassPosition) ? (bool) $showClassPosition : TRUE;
    $studentSubjectsData     = isset($studentSubjects) ? $studentSubjects : array();
    $datasetSubjectGrades    = isset($datasetSubjectGrades) ? $datasetSubjectGrades : array();
    $datasetSummaryGrades    = isset($datasetSummaryGrades) ? $datasetSummaryGrades : array();
    $remarksData             = isset($remarks) ? $remarks : array();
    $attendanceData          = isset($attendance) ? $attendance : array();
?>
<?php if(customCompute($studentLists)) { foreach($studentLists as $student) { if($studentID == $student->srstudentID || $studentID == 0) { ?>
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
                    if($include_student_address) {
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
        <?php if($include_guardian && customCompute($studentGuardianContacts)) { ?>
        <div class="section-title"><?=$this->lang->line('examtranscriptreport_guardian_details')?></div>
        <table>
            <thead>
                <tr>
                    <th><?=$this->lang->line('examtranscriptreport_guardian_name')?></th>
                    <th><?=$this->lang->line('examtranscriptreport_guardian_relation')?></th>
                    <th><?=$this->lang->line('examtranscriptreport_guardian_phone')?></th>
                    <th><?=$this->lang->line('examtranscriptreport_guardian_email')?></th>
                    <?php if($include_address) { ?><th><?=$this->lang->line('examtranscriptreport_guardian_address')?></th><?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($studentGuardianContacts as $contact) { ?>
                    <tr>
                        <td><?=($contact['name'] !== '') ? $contact['name'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                        <td><?=($contact['relation'] !== '') ? $contact['relation'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                        <td><?=($contact['phone'] !== '') ? $contact['phone'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                        <td><?=($contact['email'] !== '') ? $contact['email'] : $this->lang->line('examtranscriptreport_not_available')?></td>
                        <?php if($include_address) { ?><td><?=($contact['address'] !== '') ? $contact['address'] : $this->lang->line('examtranscriptreport_not_available')?></td><?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
        <?php if(customCompute($datasets)) { foreach($datasets as $dataset) { $datasetKey = $dataset->key; ?>
            <div class="section-title"><?=$dataset->name?></div>
            <?php if($dataset->type === 'compilation' && customCompute($dataset->components)) { ?>
                <p class="text-muted" style="margin:0 0 10px 0;">
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
                            <td colspan="<?=$subjectColumnCount?>"><?=$this->lang->line('examtranscriptreport_data_not_found')?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } } else { ?>
            <p><?=$this->lang->line('examtranscriptreport_data_not_found');?></p>
        <?php } ?>
        <?php $studentExamSummary = isset($examSummaries[$student->srstudentID]) ? $examSummaries[$student->srstudentID] : array(); ?>
        <?php if(customCompute($datasets)) { ?>
        <div class="section-title"><?=$this->lang->line('examtranscriptreport_overall_summary')?></div>
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
        <?php } ?>
        <?php $studentAttendance = isset($attendanceData[$student->srstudentID]) ? $attendanceData[$student->srstudentID] : ''; ?>
        <?php $studentRemark = isset($remarksData[$student->srstudentID]) ? $remarksData[$student->srstudentID] : NULL; ?>
        <?php if($studentAttendance !== '' && $studentAttendance !== NULL) { ?>
        <div class="section-title"><?=$this->lang->line('examtranscriptreport_attendance')?></div>
        <p><?=$studentAttendance?></p>
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
        <div class="section-title"><?=$this->lang->line('examtranscriptreport_remarks')?></div>
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
        <?php } ?>
        <?php if(customCompute($datasets)) { ?>
        <div class="section-title"><?=$this->lang->line('examtranscriptreport_progress_chart_title')?></div>
        <table>
            <thead>
                <tr>
                    <th><?=$this->lang->line('examtranscriptreport_exam')?></th>
                    <th><?=$this->lang->line('examtranscriptreport_average_mark')?> (<?=$student->srname?>)</th>
                    <th><?=$this->lang->line('examtranscriptreport_class_average')?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($datasets as $dataset) { $datasetKey = $dataset->key; $summary = isset($studentExamSummary[$datasetKey]) ? $studentExamSummary[$datasetKey] : NULL; ?>
                    <tr>
                        <td><?=$dataset->name?></td>
                        <td><?=($summary && $summary['averageMark'] !== NULL) ? ini_round($summary['averageMark']) : $this->lang->line('examtranscriptreport_not_available')?></td>
                        <td><?=($summary && $summary['classAverage'] !== NULL) ? ini_round($summary['classAverage']) : $this->lang->line('examtranscriptreport_not_available')?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
    <div class="page-break"></div>
<?php } } } else { ?>
    <p><?=$this->lang->line('examtranscriptreport_data_not_found');?></p>
<?php } ?>
</body>
</html>
