<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            echo btn_printReport('teacherexamreport', $this->lang->line('report_print'), 'printablediv');
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i>
        <?=$this->lang->line('teacherexamreport_report_for')?> <?=$this->lang->line('teacherexamreport_teacherexam')?>
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
                  <?php if(customCompute($teachers)) { foreach($teachers as $teacher) { ?>
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
                                <p><?=$this->lang->line('teacherexamreport_phone')?> : <?=$siteinfos->phone?></p>
                                <p><?=$this->lang->line('teacherexamreport_email')?> : <?=$siteinfos->email?></p>
                            </div>
                            <div class="student-profile">
                                <h4><b><?=$teacher->name?></b></h4>
                                <p><?=$this->lang->line('teacherexamreport_academic_year')?> : <b><?=$schoolyearsessionobj->schoolyear;?></b>
                            </div>
                            <div class="student-profile-img">
                                <img src="<?=imagelink($teacher->photo)?>" alt="">
                            </div>
                        </div>
                        <div class="terminal-contents terminalreporttable">
                            <h4><b><?=$this->lang->line('teacherexamreport_teacherexamreport')?></b></h4>
                            <?php if(customCompute($teacherPosition)) { ?>
                              <div id="<?=$teacher->teacherID?>" class="charts">
                                <div class="container"></div>
                                <div style="break-after:page"></div>
                                <div class="container2"></div>
                                <div style="break-after:page"></div>
                                <div class="container3"></div>
                              </div>
                            <?php } else { ?>
                                <div class="callout callout-danger">
                                    <p><b class="text-info"><?=$this->lang->line('remark_data_not_found')?></b></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <p style="page-break-after: always;">&nbsp;</p>
                  <?php
                  $this->load->view("report/teacherexam/SubjectPerformanceChartJavascript.php", array('teacherID' => $teacher->teacherID));
                  $this->load->view("report/teacherexam/ExamPerformanceChartJavascript.php", array('teacherID' => $teacher->teacherID));
                  $this->load->view("report/teacherexam/PositionPerformanceChartJavascript.php", array('teacherID' => $teacher->teacherID));
                  } }?>
                </div>
            </div>
        </div>
    </div>
</div>
