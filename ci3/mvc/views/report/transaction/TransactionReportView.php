<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-transactionreport"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_transactionreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">

            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="reportperiodDiv">
                    <label for="report_period"><?=$this->lang->line('transactionreport_report_period')?></label>
                    <select id="report_period" name="report_period" class="form-control select2">
                        <option value="all_dates"><?=$this->lang->line('transactionreport_all_dates')?></option>
                        <option value="custom_dates"><?=$this->lang->line('transactionreport_custom_dates')?></option>
                        <option value="today"><?=$this->lang->line('transactionreport_today')?></option>
                        <option value="this_week"><?=$this->lang->line('transactionreport_this_week')?></option>
                        <option value="this_week_to_date"><?=$this->lang->line('transactionreport_this_week_to_date')?></option>
                        <option value="this_month" selected><?=$this->lang->line('transactionreport_this_month')?></option>
                        <option value="this_month_to_date"><?=$this->lang->line('transactionreport_this_month_to_date')?></option>
                        <option value="this_quarter"><?=$this->lang->line('transactionreport_this_quarter')?></option>
                        <option value="this_quarter_to_date"><?=$this->lang->line('transactionreport_this_quarter_to_date')?></option>
                        <option value="this_year"><?=$this->lang->line('transactionreport_this_year')?></option>
                        <option value="this_year_to_date"><?=$this->lang->line('transactionreport_this_year_to_date')?></option>
                        <option value="yesterday"><?=$this->lang->line('transactionreport_yesterday')?></option>
                        <option value="last_month"><?=$this->lang->line('transactionreport_last_month')?></option>
                        <option value="last_month_to_date"><?=$this->lang->line('transactionreport_last_month_to_date')?></option>
                        <option value="last_year"><?=$this->lang->line('transactionreport_last_year')?></option>
                        <option value="last_year_to_date"><?=$this->lang->line('transactionreport_last_year_to_date')?></option>
                    </select>
                </div>

                <div class="form-group col-sm-4" id="fromdateDiv">
                    <label><?=$this->lang->line("transactionreport_fromdate")?></label>
                   <input class="form-control" type="text" name="fromdate" id="fromdate">
                </div>

                <div class="form-group col-sm-4" id="todateDiv">
                    <label><?=$this->lang->line("transactionreport_todate")?></label>
                    <input class="form-control" type="text" name="todate" id="todate">
                </div>

                <div class="col-sm-4">
                    <button id="get_classreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("transactionreport_submit")?></button>
                </div>
            </div>

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_transactionreport"></div>

<script src="<?=base_url('assets/daterangepicker/moment.js')?>"></script>
<script type="text/javascript">
    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('transactionreport_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    $(document).ready(function() {
        $('.select2').select2();
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

    $('#get_classreport').click(function() {
        var fromdate = $('#fromdate').val();
        var todate   = $('#todate').val();
        var error = 0;

        if(fromdate == '' && $('#report_period').val() != 'all_dates') {
            error++;
            $('#fromdateDiv').addClass('has-error');
        } else{
            $('#fromdateDiv').removeClass('has-error');
        }

        if(todate == '' && $('#report_period').val() != 'all_dates') {
            error++;
            $('#todateDiv').addClass('has-error');
        } else{
            $('#todateDiv').removeClass('has-error');
        } 

        var field = {
            'fromdate': fromdate,
            'todate': todate,
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
            url: "<?=base_url('transactionreport/getTransactionReport')?>",
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
            $('#load_transactionreport').html(response.render);
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
