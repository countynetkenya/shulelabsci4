<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-feesreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_feesreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">

            <div class="col-sm-12">

                <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("feesreport_class")?></label>
                    <?php
                        $classesArray = array(
                            "0" => $this->lang->line("feesreport_please_select"),
                        );
                        foreach ($classes as $classaKey => $classa) {
                            $classesArray[$classa->classesID] = $classa->classes;
                        }
                        echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="sectionDiv">
                    <label><?=$this->lang->line("feesreport_section")?></label>
                    <?php
                        $sectionArray = array(
                            "0" => $this->lang->line("feesreport_please_select"),
                        );
                        echo form_dropdown("sectionID", $sectionArray, set_value("sectionID"), "id='sectionID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="studentDiv">
                    <label><?=$this->lang->line("feesreport_student")?></label>
                    <?php
                        $studentArray = array(
                            "0" => $this->lang->line("feesreport_please_select"),
                        );
                        echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="feetypeDiv">
                    <label><?=$this->lang->line("feesreport_feetype")?></label>
                    <?php
                        $feetypeArray = array(
                            "0" => $this->lang->line("feesreport_please_select"),
                        );
                        if(customCompute($feetypes)) {
                            foreach($feetypes as $feetype) {
                                $feetypeArray[$feetype->feetypesID] = $feetype->feetypes;
                            }
                        }
                        echo form_dropdown("feetypeID", $feetypeArray, set_value("feetypeID"), "id='feetypeID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="reportperiodDiv">
                    <label for="report_period"><?=$this->lang->line('feesreport_report_period')?></label>
                    <select id="report_period" name="report_period" class="form-control select2">
                        <option value="all_dates"><?=$this->lang->line('feesreport_all_dates')?></option>
                        <option value="custom_dates"><?=$this->lang->line('feesreport_custom_dates')?></option>
                        <option value="today"><?=$this->lang->line('feesreport_today')?></option>
                        <option value="this_week"><?=$this->lang->line('feesreport_this_week')?></option>
                        <option value="this_week_to_date"><?=$this->lang->line('feesreport_this_week_to_date')?></option>
                        <option value="this_month" selected><?=$this->lang->line('feesreport_this_month')?></option>
                        <option value="this_month_to_date"><?=$this->lang->line('feesreport_this_month_to_date')?></option>
                        <option value="this_quarter"><?=$this->lang->line('feesreport_this_quarter')?></option>
                        <option value="this_quarter_to_date"><?=$this->lang->line('feesreport_this_quarter_to_date')?></option>
                        <option value="this_year"><?=$this->lang->line('feesreport_this_year')?></option>
                        <option value="this_year_to_date"><?=$this->lang->line('feesreport_this_year_to_date')?></option>
                        <option value="yesterday"><?=$this->lang->line('feesreport_yesterday')?></option>
                        <option value="last_month"><?=$this->lang->line('feesreport_last_month')?></option>
                        <option value="last_month_to_date"><?=$this->lang->line('feesreport_last_month_to_date')?></option>
                        <option value="last_year"><?=$this->lang->line('feesreport_last_year')?></option>
                        <option value="last_year_to_date"><?=$this->lang->line('feesreport_last_year_to_date')?></option>
                    </select>
                </div>

                <div class="form-group col-sm-4" id="fromdateDiv">
                    <label for="fromdate" ><?=$this->lang->line("feesreport_fromdate")?></label>
                    <input type="text" name="fromdate" class="form-control" id="fromdate">
                </div>

                <div class="form-group col-sm-4" id="todateDiv">
                    <label><?=$this->lang->line("feesreport_todate")?></label>
                    <input type="text" name="todate" class="form-control" id="todate">
                </div>

                <div class="col-sm-4">
                    <button id="get_feesreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("feesreport_submit")?></button>
                </div>

            </div>

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_feesreport"></div>

<script src="<?=base_url('assets/daterangepicker/moment.js')?>"></script>
<script type="text/javascript">
    $('.select2').select2();
    $(function(){
        $('#sectionDiv').hide('slow');
        $('#studentDiv').hide('slow');
    });

    $(document).ready(function() {
        $('#fromdate').datepicker();
        $('#todate').datepicker();

        function update_date_fields() {
            var period = $('#report_period').val();
            var from_date = '';
            var to_date = '';

            $('#fromdate').prop('disabled', period !== 'custom_dates');
            $('#todate').prop('disabled', period !== 'custom_dates');

            switch(period) {
                case 'all_dates':
                    from_date = '';
                    to_date = '';
                    break;
                case 'custom_dates':
                    return; // Do not change dates
                case 'today':
                    from_date = moment().format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
                    break;
                case 'this_week':
                    from_date = moment().startOf('week').format('DD-MM-YYYY');
                    to_date = moment().endOf('week').format('DD-MM-YYYY');
                    break;
                case 'this_week_to_date':
                    from_date = moment().startOf('week').format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
                    break;
                case 'this_month':
                    from_date = moment().startOf('month').format('DD-MM-YYYY');
                    to_date = moment().endOf('month').format('DD-MM-YYYY');
                    break;
                case 'this_month_to_date':
                    from_date = moment().startOf('month').format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
                    break;
                case 'this_quarter':
                    from_date = moment().startOf('quarter').format('DD-MM-YYYY');
                    to_date = moment().endOf('quarter').format('DD-MM-YYYY');
                    break;
                case 'this_quarter_to_date':
                    from_date = moment().startOf('quarter').format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
                    break;
                case 'this_year':
                    from_date = moment().startOf('year').format('DD-MM-YYYY');
                    to_date = moment().endOf('year').format('DD-MM-YYYY');
                    break;
                case 'this_year_to_date':
                    from_date = moment().startOf('year').format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
                    break;
                case 'yesterday':
                    from_date = moment().subtract(1, 'days').format('DD-MM-YYYY');
                    to_date = moment().subtract(1, 'days').format('DD-MM-YYYY');
                    break;
                case 'last_month':
                    from_date = moment().subtract(1, 'month').startOf('month').format('DD-MM-YYYY');
                    to_date = moment().subtract(1, 'month').endOf('month').format('DD-MM-YYYY');
                    break;
                case 'last_month_to_date':
                    from_date = moment().subtract(1, 'month').startOf('month').format('DD-MM-YYYY');
                    to_date = moment().subtract(1, 'month').date(moment().date()).format('DD-MM-YYYY');
                    break;
                case 'last_year':
                    from_date = moment().subtract(1, 'year').startOf('year').format('DD-MM-YYYY');
                    to_date = moment().subtract(1, 'year').endOf('year').format('DD-MM-YYYY');
                    break;
                case 'last_year_to_date':
                    from_date = moment().subtract(1, 'year').startOf('year').format('DD-MM-YYYY');
                    to_date = moment().subtract(1, 'year').month(moment().month()).date(moment().date()).format('DD-MM-YYYY');
                    break;
            }

            $('#fromdate').val(from_date);
            $('#todate').val(to_date);
        }

        $('#report_period').on('change', function() {
            update_date_fields();
        });

        // Set initial values
        update_date_fields();
    });

    $(document).on('change', "#classesID", function() {
        $('#load_feesreport').html("");
        var classesID = $(this).val();
        if(classesID == '0'){
            $("#sectionDiv").hide('slow');
            $("#studentDiv").hide('slow');
        } else {
            $("#sectionDiv").show('slow');
            $("#studentDiv").show('slow');
        }

        if(classesID !=0) {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('feesreport/getSection')?>",
                data: {"classesID" : classesID},
                dataType: "html",
                success: function(data) {
                   $('#sectionID').html(data);
                }
            });
        }
    });

    $(document).on('change', "#sectionID", function() {
        $('#load_feesreport').html("");
        
        $('#studentID').html("<option value='0'>" + "<?=$this->lang->line("feesreport_please_select")?>" +"</option>");
        $('#studentID').val(0);


        var sectionID = $(this).val();
        var classesID = $('#classesID').val();
        if(sectionID != 0 && classesID != 0) {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('feesreport/getStudent')?>",
                data: {"classesID":classesID,"sectionID" : sectionID},
                dataType: "html",
                success: function(data) {
                   $('#studentID').html(data);
                }
            });
        }
    }); 

    $(document).on('change', "#studentID", function() {
        $('#load_feesreport').html("");
    });

    $(document).on('click','#get_feesreport', function() {
        $('#load_feesreport').html("");
        var classesID = $('#classesID').val();
        var sectionID = $('#sectionID').val();
        var studentID = $('#studentID').val();
        var feetypeID = $('#feetypeID').val();
        var fromdate  = $('#fromdate').val();
        var todate    = $('#todate').val();
        var error = 0;

        var field = {
            "classesID" : classesID,
            "sectionID" : sectionID,
            "studentID" : studentID,
            "feetypeID" : feetypeID,
            "fromdate"  : fromdate,
            "todate"    : todate,
        }

        if(fromdate != '' && todate == '') {
            error++;
            $('#todateDiv').addClass('has-error');
        } else{
            $('#todateDiv').removeClass('has-error');
        }

        if(fromdate == '' && todate != '') {
            error++;
            $('#fromdateDiv').addClass('has-error');
        } else {
            $('#fromdateDiv').removeClass('has-error');
        }

        if(fromdate != '' && todate != '') {
            var fromdate = fromdate.split('-');
            var todate = todate.split('-');
            var currentdate = new Date();
            var newfromdate = new Date(fromdate[2], fromdate[1]-1, fromdate[0]);
            var newtodate   = new Date(todate[2], todate[1]-1, todate[0]);

            if(newfromdate.getTime() > newtodate.getTime()) {
                error++;
                $('#todateDiv').addClass('has-error');
            } else {
                $('#todateDiv').removeClass('has-error');
            }
        }

        if(error == 0 ) {
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
            url: "<?=base_url('feesreport/getFeesReport')?>",
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
            $('#load_feesreport').html(response.render);
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


