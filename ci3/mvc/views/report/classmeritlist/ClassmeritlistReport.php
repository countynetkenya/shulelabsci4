<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            echo btn_printReport('classmeritlistreport', $this->lang->line('classmeritlistreport_print'), 'printablediv');
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
        <?=$this->lang->line('classmeritlistreport_report_for')?> - <?=$this->lang->line('classmeritlistreport_classmeritlist')?>
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
                                    echo $this->lang->line('classmeritlistreport_class')." : ";
                                    echo isset($classes[$classesID]) ? $classes[$classesID] : $this->lang->line('classmeritlistreport_all_class');
                                ?>
                            </h5>
                            <h5 class="pull-right">
                                <?php
                                   echo $this->lang->line('classmeritlistreport_section')." : ";
                                   echo isset($sections[$sectionID]) ? $sections[$sectionID] : $this->lang->line('classmeritlistreport_all_section');
                                ?>
                            </h5>
                        </div>
                    </div>
                </div>

                <?php if(customCompute($marks)) { ?>
                    <div class="col-sm-12">
                        <div class="maintabulationsheetreport">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?=$this->lang->line('classmeritlistreport_name')?></th>
                                        <th><?=$this->lang->line('classmeritlistreport_totalimprovement')?></th>
                                        <th><?=$this->lang->line('classmeritlistreport_averagemarkscore')?></th>
                                        <th><?=$this->lang->line('classmeritlistreport_averagegrade')?></th>
                                        <th><?=$this->lang->line('classmeritlistreport_subjectnumber')?></th>
                                        <th><?=$this->lang->line('classmeritlistreport_studentnumber')?></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if(customCompute($classesList)) { foreach($classesList as $class) { ?>
                                        <tr>
                                            <td><?=$class->classes?></td>
                                            <td><?=$classPosition[$examID][$class->classesID]['totalSubjectMark'] - $classPosition[$preExamID][$class->classesID]['totalSubjectMark']?></td>
                                            <td><?=ini_round($classPosition[$examID][$class->classesID]['classPositionMark'])?></td>
                                            <?php $averageGrade = "U"; if(customCompute($grades)) { foreach($grades as $grade) {
                                              $averageMark = $classPosition[$examID][$class->classesID]['classPositionMark'];
                                              if(($grade->gradefrom <= $averageMark) && ($grade->gradeupto >= $averageMark)) {
                                                  $averageGrade = $grade->grade;
                                            } } } ?>
                                            <td><?=$averageGrade?></td>
                                            <td><?=$subjects[$class->classesID]?></td>
                                            <td><?=$students[$class->classesID]?></td>
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
                            <p><b class="text-info"><?=$this->lang->line('classmeritlistreport_data_not_found')?></b></p>
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
</script>
