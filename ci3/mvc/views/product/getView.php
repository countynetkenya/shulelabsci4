<?php if(customCompute($profile)) {
        $movementProductID = isset($profile->productID) ? (int)$profile->productID : 0;
        $currentYearStart = date('Y-01-01');
        $todayDate = date('Y-m-d');

        $movementStart = $this->input->get('fromdate', true);
        if($movementStart) {
            $movementStart = trim($movementStart);
            $dateObj = \DateTime::createFromFormat('Y-m-d', $movementStart);
            if($dateObj instanceof \DateTime) {
                $movementStart = $dateObj->format('Y-m-d');
            } else {
                $timestamp = strtotime($movementStart);
                $movementStart = $timestamp ? date('Y-m-d', $timestamp) : '';
            }
        }
        if(!$movementStart) {
            $movementStart = $currentYearStart;
        }

        $movementEnd = $this->input->get('todate', true);
        if($movementEnd) {
            $movementEnd = trim($movementEnd);
            $dateObj = \DateTime::createFromFormat('Y-m-d', $movementEnd);
            if($dateObj instanceof \DateTime) {
                $movementEnd = $dateObj->format('Y-m-d');
            } else {
                $timestamp = strtotime($movementEnd);
                $movementEnd = $timestamp ? date('Y-m-d', $timestamp) : '';
            }
        }
        if(!$movementEnd) {
            $movementEnd = $todayDate;
        }

        $movementWarehouse = $this->input->get('warehouse', true);
        if($movementWarehouse !== null && $movementWarehouse !== '') {
            $movementWarehouse = (int)$movementWarehouse;
        } else {
            $movementWarehouse = isset($set) ? (int)$set : 0;
        }

        $movementHeading = $this->lang->line('product_movement_overview');
        if(!$movementHeading || $movementHeading === 'product_movement_overview') {
            $movementHeading = 'Inventory Movement';
        }
?>
	<div class="well">
	    <div class="row">
	        <div class="col-sm-6">
	        	<?php if(!permissionChecker('product_view') && permissionChecker('product_add')) { echo btn_sm_add('product/add', $this->lang->line('add_product')); } ?>
	            <button class="btn-cs btn-sm-cs" onclick="javascript:printDiv('printablediv')"><span class="fa fa-print"></span> <?=$this->lang->line('print')?> </button>
	            <?php if(permissionChecker('product_edit')) { echo btn_sm_edit('product/edit/'.$profile->productID."/".$set, $this->lang->line('edit')); }
	            ?>

	        </div>
	        <div class="col-sm-6">
	            <ol class="breadcrumb">
	                <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
	                <li><a href="<?=base_url("product/index/".$set)?>"><?=$this->lang->line('menu_product')?></a></li>
	                <li class="active"><?=$this->lang->line('view')?></li>
	            </ol>
	        </div>
	    </div>
	</div>

	<div id="printablediv">
		<style type="text/css">
		.container {
				min-width: 310px;
				max-width: 800px;
				height: 400px;
				margin: 0 auto;
		}
		</style>

		<div class="row">
		    <div class="col-sm-3">
		    	<div class="box box-primary backgroud-image">
		      		<div class="box-body box-profile">
					<!--<?=profileviewimage($profile->photo)?>-->
		              	<h3 class="profile-username text-center"><?=$profile->productname?></h3>

		              	<p class="text-muted text-center"><?=$productcategories[$profile->productcategoryID]?></p>

		              	<ul class="list-group list-group-unbordered">
											<li class="list-group-item" style="background-color: #FFF">
		                    <b><?=$this->lang->line('product_productID')?></b> <a class="pull-right"><?=$profile->productID?></a>
		                  </li>
		              		<li class="list-group-item" style="background-color: #FFF">
		                    <b><?=$this->lang->line('product_desc')?></b> <a class="pull-right"><?=$profile->productdesc?></a>
		                  </li>
							</ul>
		            </div>
		        </div>
		    </div>

		    <div class="col-sm-9">
		        <div class="nav-tabs-custom">
		            <ul class="nav nav-tabs">
				<li class="active"><a href="#profile" data-toggle="tab"><?=$this->lang->line('product_profile')?></a></li>
				<li><a href="#history" data-toggle="tab"><?=$this->lang->line('product_history')?></a></li>
		            </ul>

		            <div class="tab-content">
		                <div class="tab-pane active" id="profile">
		                    <div class="panel-body profile-view-dis">
		                        <div class="row">
		                            <div class="profile-view-tab">
		                                <p><span><?=$this->lang->line('product_lastbuyingprice')?> </span>: <?=number_format($lastbuyingprice->productpurchaseunitprice, 2)?></p>
		                            </div>
		                            <div class="profile-view-tab">
		                                <p><span><?=$this->lang->line('product_averagebuyingprice')?> </span>: <?=number_format($averageunitprice->averageunitprice, 2)?></p>
		                            </div>
                                            <div class="profile-view-tab">
                                                <p><span><?=$this->lang->line('product_sellingprice')?> </span>: <?=number_format($profile->productsellingprice, 2)?></p>
                                            </div>
                                            <div class="profile-view-tab">
                                                <p><span><?=$this->lang->line('product_is_billable_default')?> </span>: <?=$profile->is_billable_default ? $this->lang->line('product_billable') : $this->lang->line('product_non_billable')?></p>
                                            </div>
                                            <div class="profile-view-tab">
                                                <p><span><?=$this->lang->line('product_lastsupplier')?> </span>: <?=$lastsupplier->productsuppliername?></p>
                                            </div>
		                            <div class="profile-view-tab">
		                                <p><span><?=$this->lang->line('product_total_quantity')?> </span>: <?=$productquantity?></p>
		                            </div>
		                        </div>
		                    </div>
		                    <br>
		                    <h4><?=$this->lang->line('product_warehouse_list')?></h4>
		                    <div id="hide-table">
		                        <table class="table table-striped table-bordered table-hover">
		                            <thead>
		                                <tr>
		                                    <th><?=$this->lang->line('slno')?></th>
		                                    <th><?=$this->lang->line('product_warehouse')?></th>
		                                    <th><?=$this->lang->line('product_quantity')?></th>
		                                </tr>
		                            </thead>
		                            <tbody>
		                                <?php if(customCompute($warehouse_stocks)) {$i = 1; foreach($warehouse_stocks as $warehouse_name => $stock) { ?>
		                                    <tr>
		                                        <td data-title="<?=$this->lang->line('slno')?>">
		                                            <?php echo $i; ?>
		                                        </td>
		                                        <td data-title="<?=$this->lang->line('product_warehouse')?>">
		                                            <?php echo $warehouse_name; ?>
		                                        </td>
		                                        <td data-title="<?=$this->lang->line('product_quantity')?>">
		                                            <?php echo $stock; ?>
		                                        </td>
		                                    </tr>
		                                <?php $i++; }} ?>
		                            </tbody>
		                        </table>
		                    </div>
		                </div>

				<div class="tab-pane" id="history">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="report_period" class="control-label">
                                            <?=$this->lang->line('product_report_period')?>
                                        </label>
                                        <select id="report_period" class="form-control select2">
                                            <option value="all_dates" selected><?=$this->lang->line('product_all_dates')?></option>
                                            <option value="custom_dates"><?=$this->lang->line('product_custom_dates')?></option>
                                            <option value="today"><?=$this->lang->line('product_today')?></option>
                                            <option value="this_week"><?=$this->lang->line('product_this_week')?></option>
                                            <option value="this_week_to_date"><?=$this->lang->line('product_this_week_to_date')?></option>
                                            <option value="this_month"><?=$this->lang->line('product_this_month')?></option>
                                            <option value="this_month_to_date"><?=$this->lang->line('product_this_month_to_date')?></option>
                                            <option value="this_quarter"><?=$this->lang->line('product_this_quarter')?></option>
                                            <option value="this_quarter_to_date"><?=$this->lang->line('product_this_quarter_to_date')?></option>
                                            <option value="this_year"><?=$this->lang->line('product_this_year')?></option>
                                            <option value="this_year_to_date"><?=$this->lang->line('product_this_year_to_date')?></option>
                                            <option value="yesterday"><?=$this->lang->line('product_yesterday')?></option>
                                            <option value="last_month"><?=$this->lang->line('product_last_month')?></option>
                                            <option value="last_month_to_date"><?=$this->lang->line('product_last_month_to_date')?></option>
                                            <option value="last_month_to_today"><?=$this->lang->line('product_last_month_to_today')?></option>
                                            <option value="last_quarter"><?=$this->lang->line('product_last_quarter')?></option>
                                            <option value="last_quarter_to_date"><?=$this->lang->line('product_last_quarter_to_date')?></option>
                                            <option value="last_quarter_to_today"><?=$this->lang->line('product_last_quarter_to_today')?></option>
                                            <option value="last_year"><?=$this->lang->line('product_last_year')?></option>
                                            <option value="last_year_to_date"><?=$this->lang->line('product_last_year_to_date')?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="date_from" class="control-label">
                                            <?=$this->lang->line('product_from_date')?>
                                        </label>
                                        <input type="text" class="form-control" id="date_from" name="date_from" value="<?=set_value('date_from')?>" >
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="date_to" class="control-label">
                                            <?=$this->lang->line('product_to_date')?>
                                        </label>
                                        <input type="text" class="form-control" id="date_to" name="date_to" value="<?=set_value('date_to')?>" >
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="productwarehouseID" class="control-label">
                                            <?=$this->lang->line('product_warehouse')?>
                                        </label>
                                        <?php
                                            $array = array("0" => $this->lang->line("product_select_warehouse"));
                                            if(customCompute($productwarehouses)) {
                                                foreach ($productwarehouses as $productwarehouse) {
                                                    $array[$productwarehouse->productwarehouseID] = $productwarehouse->productwarehousename;
                                                }
                                            }
                                            echo form_dropdown("productwarehouseID", $array, set_value("productwarehouseID", $set), "id='productwarehouseID' class='form-control select2'");
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-2" id="apply_filter_container" style="display: none;">
                                    <div class="form-group">
                                        <label class="control-label">&nbsp;</label>
                                        <button id="apply_filter" class="btn btn-primary btn-block">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="history_table">
                    </div>
                    <div id="chart-container" style="margin-top: 20px;">
                        <canvas id="history-chart"></canvas>
                    </div>
		        </div>
		            </div>
		        </div>
		    </div>
		</div>
        </div>
        <div class="panel panel-default movement-chart-panel">
            <div class="panel-heading">
                <strong><?=htmlspecialchars($movementHeading)?></strong>
            </div>
            <div class="panel-body">
                <div id="movementChart" class="movement-chart">
                    <div id="movementChartStatus" class="movement-chart__status" role="status" aria-live="polite"></div>
                    <canvas id="movementChartCanvas" class="movement-chart__canvas" aria-describedby="movementChartStatus"></canvas>
                </div>
            </div>
        </div>

<script src="<?=base_url('assets/chartjs/chart.js')?>"></script>
<script src="<?=base_url('public/js/product_view.js')?>"></script>
<script src="<?=base_url('assets/daterangepicker/moment.js')?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2();
        var movementParams = {
            start: <?=json_encode($movementStart)?>,
            end: <?=json_encode($movementEnd)?>
        };
        <?php if(!empty($movementWarehouse)) { ?>
        movementParams.warehouse = <?=json_encode((int)$movementWarehouse)?>;
        <?php } ?>
        if(window.renderMovementChart) {
            window.renderMovementChart(<?=$movementProductID?>, movementParams);
        }
        $('#date_from').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
        });
        $('#date_to').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
        });

        function update_date_fields() {
            var period = $('#report_period').val();
            var from_date = '';
            var to_date = '';

            var custom_dates = (period === 'custom_dates');
            $('#date_from').prop('disabled', !custom_dates);
            $('#date_to').prop('disabled', !custom_dates);
            $('#apply_filter_container').toggle(custom_dates);

            if(custom_dates) {
                $('#date_from').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy',
                });
                $('#date_to').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy',
                });
                return;
            }

            switch(period) {
                case 'all_dates':
                    from_date = '';
                    to_date = '';
                    break;
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
                    var lastMonthMoment = moment().subtract(1, 'month');
                    var day = moment().date();
                    if(day > lastMonthMoment.daysInMonth()) {
                        day = lastMonthMoment.daysInMonth();
                    }
                    from_date = lastMonthMoment.clone().startOf('month').format('DD-MM-YYYY');
                    to_date = lastMonthMoment.clone().date(day).format('DD-MM-YYYY');
                    break;
                case 'last_month_to_today':
                    from_date = moment().subtract(1, 'month').startOf('month').format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
                    break;
                case 'last_quarter':
                    from_date = moment().subtract(1, 'quarter').startOf('quarter').format('DD-MM-YYYY');
                    to_date = moment().subtract(1, 'quarter').endOf('quarter').format('DD-MM-YYYY');
                    break;
                case 'last_quarter_to_date':
                    var lastQuarterMoment = moment().subtract(1, 'quarter');
                    var day = moment().date();
                    if(day > lastQuarterMoment.daysInMonth()) {
                        day = lastQuarterMoment.daysInMonth();
                    }
                    from_date = lastQuarterMoment.clone().startOf('quarter').format('DD-MM-YYYY');
                    to_date = lastQuarterMoment.clone().date(day).format('DD-MM-YYYY');
                    break;
                case 'last_quarter_to_today':
                    from_date = moment().subtract(1, 'quarter').startOf('quarter').format('DD-MM-YYYY');
                    to_date = moment().format('DD-MM-YYYY');
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

            $('#date_from').val(from_date).datepicker('update');
            $('#date_to').val(to_date).datepicker('update');
            get_history();
        }

        $('#report_period').on('change', function() {
            update_date_fields();
        });

        $('#apply_filter').on('click', function() {
            get_history();
        });

        $('#productwarehouseID').on('change', function() {
            get_history();
        });

        function get_history() {
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();
            var warehouseID = $('#productwarehouseID').val();
            var id = "<?=$profile->productID?>";

            function toISODate(dateStr) {
                if(!dateStr) {
                    return '';
                }
                if(/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
                    var parts = dateStr.split('-');
                    return parts[2] + '-' + parts[1] + '-' + parts[0];
                }
                return dateStr;
            }

            var movementParams = {};
            var isoFrom = toISODate(date_from);
            var isoTo = toISODate(date_to);

            function deriveGranularity(startDate, endDate) {
                if(!startDate || !endDate) {
                    return '';
                }
                var mStart = moment(startDate, 'YYYY-MM-DD', true);
                var mEnd = moment(endDate, 'YYYY-MM-DD', true);
                if(!mStart.isValid()) {
                    mStart = moment(startDate);
                }
                if(!mEnd.isValid()) {
                    mEnd = moment(endDate);
                }
                if(!mStart.isValid() || !mEnd.isValid()) {
                    return '';
                }
                var diffDays = Math.abs(mEnd.diff(mStart, 'days'));
                if(diffDays <= 31) {
                    return 'day';
                } else if(diffDays <= 180) {
                    return 'week';
                } else if(diffDays <= 730) {
                    return 'month';
                }
                return 'year';
            }

            if(isoFrom) {
                movementParams.start = isoFrom;
            }
            if(isoTo) {
                movementParams.end = isoTo;
            }
            if(warehouseID && warehouseID !== '0') {
                movementParams.warehouse = warehouseID;
            }

            var derivedGranularity = deriveGranularity(isoFrom, isoTo);
            if(derivedGranularity) {
                movementParams.granularity = derivedGranularity;
            }

            var from_url_date = isoFrom ? isoFrom : '0';
            var to_url_date = isoTo ? isoTo : '0';

            var pdf_url = `<?=base_url('product/pdf')?>/${id}/${warehouseID}/${from_url_date}/${to_url_date}`;
            var csv_url = `<?=base_url('product/csv')?>/${id}/${warehouseID}/${from_url_date}/${to_url_date}`;

            $('#history_table').html('<h4><i class="fa fa-spinner fa-spin"></i> Loading...</h4>');

            $.ajax({
                type: 'POST',
                url: "<?=base_url('product/get_history')?>",
                data: {
                    'id' : id,
                    'date_from' : date_from,
                    'date_to' : date_to,
                    'warehouseID' : warehouseID
                },
                success: function(data) {
                   if(data.status === 'success') {
                       $('#history_table').html(data.html);
                       $('#pdf-export-btn').attr('href', pdf_url);
                       $('#csv-export-btn').attr('href', csv_url);

                       if (window.historyChart instanceof Chart) {
                           window.historyChart.destroy();
                       }

                       var ctx = document.getElementById('history-chart').getContext('2d');

                       var monthlyData = {};
                       var historyData = data.historyData;
                       if(typeof historyData !== 'undefined' && historyData.length > 0) {
                           historyData.forEach(function(item) {
                               var month = item.date.substring(0, 7);
                               if(!monthlyData[month]) {
                                   monthlyData[month] = { sales: 0, purchases: 0 };
                               }
                               if(item.type === 'Sale') {
                                   monthlyData[month].sales += parseFloat(item.total);
                               } else {
                                   monthlyData[month].purchases += parseFloat(item.total);
                               }
                           });
                       }

                       var labels = Object.keys(monthlyData).sort();
                       var salesData = labels.map(function(label) { return monthlyData[label].sales; });
                       var purchasesData = labels.map(function(label) { return monthlyData[label].purchases; });

                       window.historyChart = new Chart(ctx, {
                           type: 'bar',
                           data: {
                               labels: labels,
                               datasets: [{
                                   label: 'Sales',
                                   data: salesData,
                                   backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                   borderColor: 'rgba(75, 192, 192, 1)',
                                   borderWidth: 1
                               }, {
                                   label: 'Purchases',
                                   data: purchasesData,
                                   backgroundColor: 'rgba(255, 99, 132, 0.6)',
                                   borderColor: 'rgba(255, 99, 132, 1)',
                                   borderWidth: 1
                               }]
                           },
                           options: {
                               scales: {
                                   y: {
                                       beginAtZero: true
                                   }
                               }
                           }
                       });
                   } else {
                       $('#history_table').html('<h4 class="text-red">Error: ' + data.message + '</h4>');
                   }

                   if(window.renderMovementChart) {
                       window.renderMovementChart(id, movementParams);
                   }
                },
                error: function() {
                    $('#history_table').html('<h4 class="text-red">Error loading data. Please try again.</h4>');
                    if(window.renderMovementChart) {
                        window.renderMovementChart(id, movementParams);
                    }
                }
            });
        }

        // Set initial values
        update_date_fields();
        get_history();
    });

    function printDiv(divID) {
        var divElements = document.getElementById(divID).innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }
</script>
<?php } ?>
