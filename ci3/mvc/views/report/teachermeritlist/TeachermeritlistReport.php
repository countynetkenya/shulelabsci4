<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            echo btn_printReport('teachermeritlistreport', $this->lang->line('teachermeritlistreport_print'), 'printablediv');
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
        <?=$this->lang->line('teachermeritlistreport_report_for')?> - <?=$this->lang->line('teachermeritlistreport_teachermeritlist')?>
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
                                    echo $this->lang->line('teachermeritlistreport_class')." : ";
                                    echo isset($classes[$classesID]) ? $classes[$classesID] : $this->lang->line('teachermeritlistreport_all_class');
                                ?>
                            </h5>
                            <h5 class="pull-right">
                                <?php
                                   echo $this->lang->line('teachermeritlistreport_section')." : ";
                                   echo isset($sections[$sectionID]) ? $sections[$sectionID] : $this->lang->line('teachermeritlistreport_all_section');
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
                                        <th><?=$this->lang->line('teachermeritlistreport_name')?></th>
                                        <th><?=$this->lang->line('teachermeritlistreport_totalimprovement')?></th>
                                        <th><?=$this->lang->line('teachermeritlistreport_averagemarkscore')?></th>
                                        <th><?=$this->lang->line('teachermeritlistreport_averagegrade')?></th>
                                        <th><?=$this->lang->line('teachermeritlistreport_subjectnumber')?></th>
                                        <th><?=$this->lang->line('teachermeritlistreport_studentnumber')?></th>
                                        <th><?=$this->lang->line('teachermeritlistreport_classnumber')?></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if(customCompute($teachers)) { foreach($teachers as $teacher) { ?>
                                        <tr>
                                            <td><?=$teacher->name?></td>
                                            <?php
                                            $totalMarkAverage = 0;
                                            $totalStudentImprovement = 0;
                                            if(customCompute($exams)) {
                                                for ($i=0; $i<count($exams); $i++) {
                                                    $markAverage = 0;
                                                    if(isset($teacherPosition[$exams[$i]->examID][$teacher->teacherID]['totalSubjectMark']))
                                                      $markAverage = ini_round($teacherPosition[$exams[$i]->examID][$teacher->teacherID]['totalSubjectMark']);
                                                    if($i>0) {
                                                      $totalStudentImprovement += $markAverage-ini_round($teacherPosition[$exams[$i-1]->examID][$teacher->teacherID]['totalSubjectMark']);
                                                    }
                                                    $totalMarkAverage += $teacherPosition[$exams[$i]->examID][$teacher->teacherID]['classPositionMark'];
                                                }
                                            }?>
                                            <td><?=$totalStudentImprovement?></td>
                                            <td><?=ini_round($totalMarkAverage/count($exams))?></td>
                                            <?php $averageGrade = "U"; if(customCompute($grades)) { foreach($grades as $grade) {
                                              $averageMark = $totalMarkAverage/count($exams);
                                              if(($grade->gradefrom <= $averageMark) && ($grade->gradeupto >= $averageMark)) {
                                                  $averageGrade = $grade->grade;
                                            } } } ?>
                                            <td><?=$averageGrade?></td>
                                            <td><?=isset($subjectteacher[$teacher->teacherID]['subjects']) ? count($subjectteacher[$teacher->teacherID]['subjects']) : '0' ?></td>
                                            <td><?=isset($subjectteacher[$teacher->teacherID]['students']) ? $subjectteacher[$teacher->teacherID]['students'] : '0' ?></td>
                                            <td><?=isset($subjectteacher[$teacher->teacherID]['classes']) ? count($subjectteacher[$teacher->teacherID]['classes']) : '0' ?></td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-sm-12">
                        <br>
                        <div class="callout callout-danger">
                            <p><b class="text-info"><?=$this->lang->line('teachermeritlistreport_data_not_found')?></b></p>
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
