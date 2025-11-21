<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            echo btn_printReport('studentmeritlistreport', $this->lang->line('studentmeritlistreport_print'), 'printablediv');
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
        <?=$this->lang->line('studentmeritlistreport_report_for')?> - <?=$this->lang->line('studentmeritlistreport_studentmeritlist')?>
        </h3>
    </div><!-- /.box-header -->
    <div id="printablediv">

        <style type="text/css">
            .maintabulationsheetreport table {
                text-align: center;
                width: 100%;
                padding: 10px;
            }

            .maintabulationsheetreport table th {
                padding: 2px;
                border:1px solid #ddd;
                text-align: center;
                font-size: 10px;
                min-height: 40px;
                line-height: 15px;
            }

            .maintabulationsheetreport table td{
                padding: 2px;
                border:1px solid #ddd;
                font-size: 10px;
            }
        </style>

        <div class="box-body" style="margin-bottom: 50px;">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>

                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="pull-left">
                                <?php
                                    echo $this->lang->line('studentmeritlistreport_class')." : ";
                                    echo isset($classes[$classesID]) ? $classes[$classesID] : $this->lang->line('studentmeritlistreport_all_class');
                                ?>
                            </h5>
                            <h5 class="pull-right">
                                <?php
                                   echo $this->lang->line('studentmeritlistreport_section')." : ";
                                   echo isset($sections[$sectionID]) ? $sections[$sectionID] : $this->lang->line('studentmeritlistreport_all_section');
                                ?>
                            </h5>
                        </div>
                    </div>
                </div>

                <?php if(customCompute($marks)) { ?>
                    <div class="col-sm-12">
                        <div class="maintabulationsheetreport">
                            <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('studentmeritlistreport_name')?></th>
                                        <th><?=$this->lang->line('studentmeritlistreport_registerno')?></th>
                                        <?php if(customCompute($exams)) { foreach ($exams as $exam) { ?>
                                            <th><?=$exam->exam?></th>
                                        <?php } } ?>
                                        <th><?=$this->lang->line('studentmeritlistreport_totalmarkscore')?></th>
                                        <th><?=$this->lang->line('studentmeritlistreport_averagemarkscore')?></th>
                                        <th><?=$this->lang->line('studentmeritlistreport_totalimprovement')?></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                        $grandTotalMarkAverage = [];
                                        $grandTotalStudentMarkAverage = 0;
                                        $grandTotalStudentMarkAverageAverage = 0;
                                        $grandTotalImprovement = 0;
                                        if(customCompute($students)) { foreach($students as $student) {
                                        $totalStudentMarkAverage = 0;
                                        $totalStudentMarkAverageAverage = 0;
                                        $totalStudentImprovement = 0; ?>
                                        <tr>
                                            <td><?=$student->srname?></td>
                                            <td><?=$student->srstudentID?></td>
                                            <?php if(customCompute($exams)) {
                                                for ($i=0; $i<count($exams); $i++) {
                                                    $markAverage = 0;
                                                    if(isset($studentPosition[$exams[$i]->examID][$student->srstudentID]['classPositionMark']))
                                                      $markAverage = ini_round($studentPosition[$exams[$i]->examID][$student->srstudentID]['classPositionMark']);
                                                    if($i>0) {
                                                      $totalStudentImprovement += $markAverage-ini_round($studentPosition[$exams[$i-1]->examID][$student->srstudentID]['classPositionMark']);
                                                    }
                                                    $totalStudentMarkAverage += $markAverage;
                                                    if(isset($grandTotalMarkAverage[$exams[$i]->examID]))
                                                      $grandTotalMarkAverage[$exams[$i]->examID] += $markAverage;
                                                    else
                                                      $grandTotalMarkAverage[$exams[$i]->examID] = $markAverage;?>
                                                    <td><?=$markAverage?></td>
                                            <?php }
                                            $grandTotalStudentMarkAverage += $totalStudentMarkAverage;
                                            $grandTotalImprovement += $totalStudentImprovement;
                                            } ?>
                                            <td><?=$totalStudentMarkAverage?></td>
                                            <?php
                                              $totalStudentMarkAverageAverage = ini_round($totalStudentMarkAverage / customCompute($exams));
                                              $grandTotalStudentMarkAverageAverage += $totalStudentMarkAverageAverage;
                                              ?>
                                            <td><?=$totalStudentMarkAverageAverage?></td>
                                            <td><?=ini_round($totalStudentImprovement)?></td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                                <tfoot>
                                  <tr>
                                    <td><b><?=$this->lang->line('studentmeritlistreport_total')?><b></td>
                                    <td></td>
                                    <?php if(customCompute($exams)) { foreach ($exams as $exam) { ?>
                                        <td><?=$grandTotalMarkAverage[$exam->examID]?></td>
                                    <?php } } ?>
                                    <td><?=$grandTotalStudentMarkAverage?></td>
                                    <td><?=$grandTotalStudentMarkAverageAverage?></td>
                                    <td><?=$grandTotalImprovement?></td>
                                  </tr>
                                  <tr>
                                    <td><b><?=$this->lang->line('studentmeritlistreport_studentnumber')?><b></td>
                                    <td></td>
                                    <?php if(customCompute($exams)) { foreach ($exams as $exam) { ?>
                                        <td><?=customCompute($students)?></td>
                                    <?php } } ?>
                                    <td><?=customCompute($students)?></td>
                                    <td><?=customCompute($students)?></td>
                                    <td><?=customCompute($students)?></td>
                                  </tr>
                                  <tr>
                                    <td><b><?=$this->lang->line('studentmeritlistreport_averagescore')?><b></td>
                                    <td></td>
                                    <?php if(customCompute($exams)) { foreach ($exams as $exam) { ?>
                                        <td><?=ini_round($grandTotalMarkAverage[$exam->examID] / customCompute($students))?></td>
                                    <?php } } ?>
                                    <td><?=ini_round($grandTotalStudentMarkAverage / customCompute($students))?></td>
                                    <td><?=ini_round($grandTotalStudentMarkAverageAverage / customCompute($students))?></td>
                                    <td><?=ini_round($grandTotalImprovement / customCompute($students))?></td>
                                  </tr>
                                  <tr>
                                    <td><b><?=$this->lang->line('studentmeritlistreport_averagegrade')?><b></td>
                                    <td></td>
                                    <?php if(customCompute($exams)) { foreach ($exams as $exam) {
                                      if(customCompute($grades)) { foreach($grades as $grade) {
                                        $averageMark = ini_round($grandTotalMarkAverage[$exam->examID] / customCompute($students));
                                        if(($grade->gradefrom <= $averageMark) && ($grade->gradeupto >= $averageMark)) { ?>
                                            <td><?=$grade->grade?></td>
                                      <?php } } } ?>
                                    <?php } } ?>
                                    <td></td>
                                    <?php if(customCompute($grades)) { foreach($grades as $grade) {
                                      $averageMark = ini_round($grandTotalStudentMarkAverageAverage / customCompute($students));
                                      if(($grade->gradefrom <= $averageMark) && ($grade->gradeupto >= $averageMark)) { ?>
                                          <td><?=$grade->grade?></td>
                                    <?php } } } ?>
                                    <td></td>
                                  </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-sm-12">
                        <br>
                        <div class="callout callout-danger">
                            <p><b class="text-info"><?=$this->lang->line('studentmeritlistreport_data_not_found')?></b></p>
                        </div>
                    </div>
                <?php } ?>

                <div class="col-sm-12 text-center footerAll">
                    <?=reportfooter($siteinfos, $schoolyearsessionobj)?>
                </div>
            </div><!-- row -->
        </div>
    </div>
</div>

<script type="text/javascript">
    $('.maintabulationsheetreport').mCustomScrollbar({
        axis:"x"
    });

    $(document).ready(function () {
      $('#example3, #example1, #example2, #example4').DataTable({
        dom : 'Bfrtip',
        pageLength: 100,
        buttons : [
          'copyHtml5',
          'excelHtml5',
          'csvHtml5',
          'pdfHtml5'
        ],
        search : false,
        "autoWidth": false
      });
    });
</script>
