
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-wrench"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_quickbooks')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                  <div class="nav-tabs">
                      <ul class="nav nav-tabs">
                        <li <?=$tab == 'balances' ? '' : 'class="active"'?>><a data-toggle="tab" href="#sync" aria-expanded="true"><?=$this->lang->line('sync_records')?></a></li>
                        <!--<li><a data-toggle="tab" href="#recover" aria-expanded="true"><?=$this->lang->line('recover_records')?></a></li>-->
                        <li><a data-toggle="tab" href="#logs" aria-expanded="true"><?=$this->lang->line('logs')?></a></li>
                        <li  <?=$tab == 'balances' ? 'class="active"' : ''?>><a data-toggle="tab" href="#balances" aria-expanded="true"><?=$this->lang->line('customer_balances')?></a></li>
                      </ul>

                      <div class="tab-content">
                        <div class="tab-pane <?=$tab == 'balances' ? '' : 'active'?>" id="sync" role="tabpanel">
                          <br>
                          <div class="row">
                            <div class="col-sm-12">
                              <h2>Sync Records</h2>
                              <h6>Sync invoices, credit notes and payments.</h6>
                              <div class="margin-bottom">
                                <div class="btn-group">
                                  <?php if (empty($config['sessionAccessToken'])) {?>
                                      <input onclick="oauth.loginPopup()" id="connectQuickBooksButton" type="button" class="btn btn-warning" value="<?=$this->lang->line("connect_quickbooks")?>" >
                                  <?php } else {
                                    if (now() > $config['sessionAccessTokenExpiry']) {?>
                                      <a href="<?=base_url("quickbooks/refreshToken")?>" class="btn btn-warning"><?=$this->lang->line("reconnect_quickbooks")?></a>
                                    <?php } else {?>
                                      <input id="syncButton" type="button" class="btn btn-success" value="<?=$this->lang->line('update')?>">
                                    <?php }
                                  }?>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-3">
                                  <div class="form-group">
                                      <label for="date from" class="control-label">
                                          <?=$this->lang->line('from')?>
                                      </label>
                                    <input name="dateFrom" id="dateFrom" type="date" class="form-control" value="<?=$set_dateFrom?>">
                                  </div>
                                </div>
                                <div class="col-md-3">
                                  <div class="form-group">
                                      <label for="date to" class="control-label">
                                          <?=$this->lang->line('to')?>
                                      </label>
                                    <input name="dateTo" id="dateTo" type="date" class="form-control" value="<?=$set_dateTo?>">
                                  </div>
                                </div>
                              </div>

                              <div class="well quickbooks-export-well">
                                <h3 class="no-margin">
                                  <?=$this->lang->line('quickbooks_export_engines')?>
                                </h3>
                                <p class="text-muted small">
                                  <?=$this->lang->line('quickbooks_export_engines_desc')?>
                                </p>

                                <form id="quickbooks-export-form" class="quickbooks-export-form" autocomplete="off">
                                  <div class="row">
                                    <div class="col-md-3 col-sm-6">
                                      <div class="form-group">
                                        <label for="qb-export-engine" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_engine')?>
                                        </label>
                                        <select id="qb-export-engine" name="engine" class="form-control">
                                          <option value="dry-run"><?=$this->lang->line('quickbooks_export_engine_dry_run')?></option>
                                          <option value="schedule-term"><?=$this->lang->line('quickbooks_export_engine_schedule_term')?></option>
                                          <option value="schedule-month"><?=$this->lang->line('quickbooks_export_engine_schedule_month')?></option>
                                          <option value="schedule-day"><?=$this->lang->line('quickbooks_export_engine_schedule_day')?></option>
                                          <option value="reconciliation"><?=$this->lang->line('quickbooks_export_engine_reconciliation')?></option>
                                        </select>
                                      </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6" id="qb-export-term-group" style="display:none;">
                                      <div class="form-group">
                                        <label for="qb-export-term" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_term')?>
                                        </label>
                                        <input type="text" class="form-control" id="qb-export-term" name="term" placeholder="2024 Term 1">
                                      </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6" id="qb-export-month-group" style="display:none;">
                                      <div class="form-group">
                                        <label for="qb-export-month" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_month')?>
                                        </label>
                                        <input type="month" class="form-control" id="qb-export-month" name="month">
                                      </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6" id="qb-export-day-group" style="display:none;">
                                      <div class="form-group">
                                        <label for="qb-export-day" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_day')?>
                                        </label>
                                        <input type="date" class="form-control" id="qb-export-day" name="day">
                                      </div>
                                    </div>
                                  </div>

                                  <div class="row">
                                    <div class="col-md-3 col-sm-6" id="qb-export-start-group" style="display:none;">
                                      <div class="form-group">
                                        <label for="qb-export-start" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_start')?>
                                        </label>
                                        <input type="date" class="form-control" id="qb-export-start" name="start_date">
                                      </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6" id="qb-export-end-group" style="display:none;">
                                      <div class="form-group">
                                        <label for="qb-export-end" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_end')?>
                                        </label>
                                        <input type="date" class="form-control" id="qb-export-end" name="end_date">
                                      </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6">
                                      <div class="form-group">
                                        <label for="qb-export-key" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_idempotency_key')?>
                                        </label>
                                        <input type="text" class="form-control" id="qb-export-key" name="idempotency_key" placeholder="optional">
                                      </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6">
                                      <div class="form-group">
                                        <label for="qb-export-notes" class="control-label">
                                          <?=$this->lang->line('quickbooks_export_notes')?>
                                        </label>
                                        <input type="text" class="form-control" id="qb-export-notes" name="notes" placeholder="<?=$this->lang->line('quickbooks_export_notes_placeholder')?>">
                                      </div>
                                    </div>
                                  </div>

                                  <div class="row">
                                    <div class="col-md-12 text-right">
                                      <button type="button" class="btn btn-default" id="qb-export-reset">
                                        <?=$this->lang->line('quickbooks_export_reset')?>
                                      </button>
                                      <button type="submit" class="btn btn-primary" id="qb-export-submit">
                                        <?=$this->lang->line('quickbooks_export_submit')?>
                                      </button>
                                    </div>
                                  </div>
                                </form>

                                <div id="quickbooks-export-result" class="alert" style="display:none;"></div>
                              </div>

                              <div class="nav-tabs-custom">
                                  <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#invoices" aria-expanded="true"><?=$this->lang->line('invoices')?></a></li>
                                    <li><a data-toggle="tab" href="#creditmemos" aria-expanded="true"><?=$this->lang->line('creditmemos')?></a></li>
                                    <li><a data-toggle="tab" href="#payments" aria-expanded="true"><?=$this->lang->line('payments')?></a></li>
                                  </ul>

                                  <div class="tab-content">
                                    <div class="tab-pane active" id="invoices" role="tabpanel">
                                      <br>
                                      <div class="row">
                                          <div class="col-sm-12">
                                            <table id="example6" class="table table-striped table-bordered table-hover dataTable no-footer">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="checkAll" title="Select All"/></th>
                                                        <th><?=$this->lang->line('student')?></th>
                                                        <th><?=$this->lang->line('class')?></th>
                                                        <th><?=$this->lang->line('invoice_amount')?></th>
                                                        <th><?=$this->lang->line('invoice_date')?></th>
                                                        <th><?=$this->lang->line('posted')?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(customCompute($invoices)) {$i = 1; foreach($invoices as $invoice) { ?>
                                                        <tr data-id="<?=$invoice->invoiceID?>">
                                                            <td data-title="<?=$this->lang->line('slno')?>">
                                                                <?php echo $invoice->invoiceID; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('student')?>">
                                                                <?php echo $invoice->name; ?>
                                                            </td>

                                                             <td data-title="<?=$this->lang->line('class')?>">
                                                                <?php echo $invoice->classesID; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('invoice_total')?>">
                                                                <?php echo number_format($invoice->amount, 2); ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('invoice_date')?>">
                                                                <?php echo date("d M Y", strtotime($invoice->date)) ; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('posted')?>">
                                                              <?= ($invoice->quickbooks_status == 1) ? '<i class="fa fa-check" aria-hidden="true" style="color:green"></i>' : '<i class="fa fa-exclamation" aria-hidden="true" style="color:red"></i>'?>                                                   </td>
                                                            </td>
                                                        </tr>
                                                    <?php $i++; }} ?>
                                                </tbody>
                                            </table>
                                          </div>
                                      </div>
                                    </div>

                                    <div class="tab-pane" id="creditmemos" role="tabpanel">
                                      <br>
                                      <div class="row">
                                          <div class="col-sm-12">
                                            <table id="example7" class="table table-striped table-bordered table-hover dataTable no-footer">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="checkAll" title="Select All"/></th>
                                                        <th><?=$this->lang->line('student')?></th>
                                                        <th><?=$this->lang->line('class')?></th>
                                                        <th><?=$this->lang->line('invoice_amount')?></th>
                                                        <th><?=$this->lang->line('invoice_date')?></th>
                                                        <th><?=$this->lang->line('posted')?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(customCompute($creditmemos)) {$i = 1; foreach($creditmemos as $creditmemo) { ?>
                                                        <tr data-id="<?=$creditmemo->creditmemoID?>">
                                                            <td data-title="<?=$this->lang->line('slno')?>">
                                                                <?php echo $creditmemo->creditmemoID; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('student')?>">
                                                                <?php echo $creditmemo->name; ?>
                                                            </td>

                                                             <td data-title="<?=$this->lang->line('class')?>">
                                                                <?php echo $creditmemo->classesID; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('invoice_total')?>">
                                                                <?php echo number_format($creditmemo->amount, 2); ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('invoice_date')?>">
                                                                <?php echo date("d M Y", strtotime($creditmemo->date)) ; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('posted')?>">
                                                              <?= ($creditmemo->quickbooks_status == 1) ? '<i class="fa fa-check" aria-hidden="true" style="color:green"></i>' : '<i class="fa fa-exclamation" aria-hidden="true" style="color:red"></i>'?>                                                   </td>
                                                            </td>
                                                        </tr>
                                                    <?php $i++; }} ?>
                                                </tbody>
                                            </table>
                                          </div>
                                      </div>
                                    </div>

                                    <div class="tab-pane" id="payments" role="tabpanel">
                                      <br>
                                      <div class="row">
                                          <div class="col-sm-12">
                                            <table id="example8" class="table table-striped table-bordered table-hover dataTable no-footer">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="checkAll" title="Select All"/></th>
                                                        <th><?=$this->lang->line('student')?></th>
                                                        <th><?=$this->lang->line('class')?></th>
                                                        <th><?=$this->lang->line('invoice_amount')?></th>
                                                        <th><?=$this->lang->line('invoice_date')?></th>
                                                        <th><?=$this->lang->line('posted')?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(customCompute($payments)) {$i = 1; foreach($payments as $payment) { ?>
                                                        <tr data-id="<?=$payment->paymentID?>">
                                                            <td data-title="<?=$this->lang->line('slno')?>">
                                                                <?php echo $payment->paymentID; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('student')?>">
                                                                <?php echo $payment->name; ?>
                                                            </td>

                                                             <td data-title="<?=$this->lang->line('class')?>">
                                                                <?php echo $payment->classesID; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('invoice_total')?>">
                                                                <?php echo number_format($payment->paymentamount, 2); ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('invoice_date')?>">
                                                                <?php echo date("d M Y", strtotime($payment->paymentdate)) ; ?>
                                                            </td>

                                                            <td data-title="<?=$this->lang->line('posted')?>">
                                                              <?= ($payment->quickbooks_status == 1) ? '<i class="fa fa-check" aria-hidden="true" style="color:green"></i>' : '<i class="fa fa-exclamation" aria-hidden="true" style="color:red"></i>'?>                                                   </td>
                                                            </td>
                                                        </tr>
                                                    <?php $i++; }} ?>
                                                </tbody>
                                            </table>
                                          </div>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                            </div> <!-- col-sm-12 -->
                          </div>
                        </div>

                        <div class="tab-pane" id="recover" role="tabpanel">
                          <br>
                          <div class="row">
                            <div class="col-sm-12">
                              <h2>Recover Records</h2>
                              <h6>Recover invoices, credit notes and payments.</h6>
                              <br />
                              <form>
                                  <div class="row">
                                      <div class="col-md-9">
                                          <div class="row">
                                            <div class="col-md-6">
                                              <div class="form-group">
                                                  <label for="start date" class="control-label">
                                                      <?=$this->lang->line('start_date')?>
                                                  </label>
                          											<input name="startdate" id="startdate" type="date" class="form-control" value="<?=$set_date?>">
                          										</div>
                          									</div>

                                            <div class="col-md-6">
                                              <div class="form-group">
                                                  <label for="end date" class="control-label">
                                                      <?=$this->lang->line('end_date')?>
                                                  </label>
                          											<input name="enddate" id="enddate" type="date" class="form-control" value="<?=$set_date?>">
                          										</div>
                          									</div>
                                          </div>
                                      </div>

                                      <div class="col-md-3 col-xs-12">
                                        <div class="row">
                                          <div class="col-md-12 col-xs-12">
                                            <div class="form-group">
                                              <?php if (empty($config['sessionAccessToken'])) {?>
                                                  <input onclick="oauth.loginPopup()" id="connectQuickBooksButton" type="button" class="btn btn-warning col-md-12 col-xs-12 global_payment_search" value="<?=$this->lang->line("connect_quickbooks")?>" >
                                              <?php } else {
                                                if (now() > $config['sessionAccessTokenExpiry']) {?>
                                                  <a href="<?=base_url("quickbooks/refreshToken")?>" class="btn btn-warning col-md-12 col-xs-12 global_payment_search"><?=$this->lang->line("reconnect_quickbooks")?></a>
                                                <?php } else {?>
                                                  <input id="recoverButton" type="button" class="btn btn-success col-md-12 col-xs-12 global_payment_search" value="<?=$this->lang->line('recover')?>">
                                                <?php }
                                              }?>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                  </div>
                              </form>
                              <div class="margin-bottom">

                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="tab-pane" id="logs" role="tabpanel">
                          <br>
                          <div class="row">
                            <div class="col-sm-12">
                              <h2>QuickBooks Logs</h2>
                              <h6>Logs of QuickBooks queries.</h6>
                              <!--<a href="quickbooks/download" type="button" class="btn btn-success"><?=$this->lang->line('download')?></a>-->
                              <br />
                              <form>
                                  <div class="row">
                                      <div class="col-md-10">
                                          <div class="row">
                                            <div class="col-md-3">
                                              <div class="form-group">
                                                  <label for="date from" class="control-label">
                                                      Date
                                                  </label>
                          											<input name="date" id="date" type="date" class="form-control" value="<?=$set_date?>">
                          										</div>
                          									</div>

                                            <div class="col-md-3">
                                              <div class="form-group" >
                                                  <label for="status">
                                                      <?=$this->lang->line("status")?>
                                                  </label>
                                                  <?php
                                                      $statusArray = array('' => $this->lang->line("select_status"), 'OK' => $this->lang->line("ok"), 'ERROR' => $this->lang->line("error"));

                                                      echo form_dropdown("status", $statusArray, '', "id='status' class='form-control select2'");
                                                  ?>
                                              </div>
                                            </div>
                                          </div>
                                      </div>
                                  </div>
                              </form>

                              <table id="example4" class="table table-striped table-bordered table-hover dataTable no-footer">
                                  <thead>
                                      <tr>
                                          <th><?=$this->lang->line('slno')?></th>
                                          <th><?=$this->lang->line('ip')?></th>
                                          <th><?=$this->lang->line('request')?></th>
                                          <th><?=$this->lang->line('status')?></th>
                                          <th><?=$this->lang->line('time')?></th>
                                          <th><?=$this->lang->line('message')?></th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php if(customCompute($logs)) {$i = 1; foreach($logs as $log) { ?>
                                          <tr>
                                              <td data-title="<?=$this->lang->line('slno')?>">
                                                  <?php echo $log->quickbookslogID; ?>
                                              </td>

                                              <td data-title="<?=$this->lang->line('ip')?>">
                                                  <?php echo $log->ip; ?>
                                              </td>

                                               <td data-title="<?=$this->lang->line('request')?>">
                                                  <?php echo $log->request; ?>
                                              </td>

                                              <td data-title="<?=$this->lang->line('status')?>">
                                                  <?php echo $log->status; ?>
                                              </td>

                                              <td data-title="<?=$this->lang->line('time')?>">
                                                  <?php echo $log->logged_date ." ". $log->logged_time;?>
                                              </td>

                                              <td data-title="<?=$this->lang->line('message')?>">
                                                  <?php echo $log->message; ?>
                                              </td>
                                          </tr>
                                      <?php $i++; }} ?>
                                  </tbody>
                              </table>
                            </div>
                          </div>
                        </div>

                        <div class="tab-pane <?=$tab == 'balances' ? 'active' : ''?>" id="balances" role="tabpanel">
                          <br>
                          <div class="row">
                            <div class="col-sm-12">
                              <h2>Customer Balances</h2>
                              <h6>A comparison between Shulelabs customer balance vis-à-vis QuickBooks customer balance</h6>
                              <br/>
                              <div class="col-sm-12">

                                  <form method="POST">
                                      <div class="row">
                                          <div class="col-md-10">
                                              <div class="row">

                                                  <div class="col-md-4">
                                                      <div class="<?php echo form_error('classesID') ? 'form-group has-error' : 'form-group'; ?>" >
                                                          <label for="classesID" class="control-label">
                                                              <?=$this->lang->line('class')?> <span class="text-red">*</span>
                                                          </label>
                                                          <?php
                                                              $classArray = array("0" => $this->lang->line("select_classes"));
                                                              foreach ($classes as $classa) {
                                                                  $classArray[$classa->classesID] = $classa->classes;
                                                              }
                                                              echo form_dropdown("classesID", $classArray, set_value("classesID", $set_classesID), "id='classesID' class='form-control select2'");
                                                          ?>
                                                      </div>
                                                  </div>

                                                  <div class="col-md-4">
                                                      <div class="<?php echo form_error('sectionID') ? 'form-group has-error' : 'form-group'; ?>" >
                                                          <label for="sectionID" class="control-label"><?=$this->lang->line('section')?></label>
                                                          <?php
                                                              $sectionArray = array('0' => $this->lang->line("select_section"));
                                                              if($sections != 0) {
                                                                  foreach ($sections as $section) {
                                                                      $sectionArray[$section->sectionID] = $section->section;
                                                                  }
                                                              }

                                                              echo form_dropdown("sectionID", $sectionArray, set_value("sectionID", $set_sectionID), "id='sectionID' class='form-control select2'");
                                                          ?>
                                                      </div>
                                                  </div>

                                                  <div class="col-md-4">
                                                      <div class="<?php echo form_error('studentID') ? 'form-group has-error' : 'form-group'; ?>" >
                                                          <label for="studentID" class="control-label">
                                                              <?=$this->lang->line('student')?> <!--<span class="text-red"></span>-->
                                                          </label>

                                                          <?php
                                                              $studentArray = array('0' => $this->lang->line("select_student"));
                                                              if(customCompute($students)) {
                                                                  foreach ($students as $student) {
                                                                      $studentArray[$student->srstudentID] = $student->srname.' - '.$this->lang->line('register_no').' - '.$student->srstudentID;
                                                                  }
                                                              }

                                                              echo form_dropdown("studentID", $studentArray, set_value("studentID", $set_studentID), "id='studentID' class='form-control select2'");
                                                          ?>
                                                     </div>
              									                 </div>
                                              </div>
                                          </div>

                                          <div class="col-md-2 col-xs-12">
                                              <div class="row">
                                                  <div class="col-md-12 col-xs-12">
                                                      <div class="form-group" >
                                                          <button type="submit" class="btn btn-success col-md-12 col-xs-12 global_payment_search"><?=$this->lang->line('submit')?></button>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </form>

                                  <div class="col-sm-12" >
                                      <?php if(customCompute($balances)) { ?>
                                        <div class="row">
                                          <div class="col-xs-12">
                                            <div class="table-responsive">
                                              <table class="table product-style">
                                                <thead>
                                  								<tr>
                                  									<th><?=$this->lang->line('slno')?></th>
                                  									<th><?=$this->lang->line('student')?></th>
                                  									<th><?=$this->lang->line('shulelabs')?></th>
                                  									<th><?=$this->lang->line('quickbooks')?></th>
                                  								</tr>
                                                </thead>
                                                <tbody>
                                                  <?php if(customCompute($students)) {
                                                    foreach($students as $student) { ?>
                                                      <tr>
                                                        <td><?=$student->srstudentID?></td>
                                                        <td><?=$student->name?></td>
                                                        <td><?=number_format($balances[$student->srstudentID]['shulelabs'],2)?></td>
                                                        <td><?=number_format($balances[$student->srstudentID]['quickbooks'],2)?></td>
                                                      </tr>
                                                  <?php } }?>
                                                </tbody>
                                              </table>
                                            </div>
                                          </div>
                                        </div>
                                      <?php }?>
                                  </div>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                  </div>
               </div> <!-- col-sm-12 -->
            </div> <!-- col-sm-12 -->
        </div> <!-- row -->
    </div> <!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
  $(document).ready(function () {
    $('.select2').select2();

    $('#startdate, #enddate').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd'
    });

    var qbExportStrings = {
      success: "<?=htmlspecialchars($this->lang->line('quickbooks_export_success'), ENT_QUOTES, 'UTF-8')?>",
      replayed: "<?=htmlspecialchars($this->lang->line('quickbooks_export_replayed'), ENT_QUOTES, 'UTF-8')?>",
      conflict: "<?=htmlspecialchars($this->lang->line('quickbooks_export_conflict'), ENT_QUOTES, 'UTF-8')?>",
      error: "<?=htmlspecialchars($this->lang->line('quickbooks_export_error'), ENT_QUOTES, 'UTF-8')?>",
      heading: "<?=htmlspecialchars($this->lang->line('quickbooks_export_result_heading'), ENT_QUOTES, 'UTF-8')?>",
      engine: "<?=htmlspecialchars($this->lang->line('quickbooks_export_engine_label'), ENT_QUOTES, 'UTF-8')?>",
      idLabel: "<?=htmlspecialchars($this->lang->line('quickbooks_export_idempotency_key'), ENT_QUOTES, 'UTF-8')?>",
      hash: "<?=htmlspecialchars($this->lang->line('quickbooks_export_request_hash'), ENT_QUOTES, 'UTF-8')?>",
      acceptance: "<?=htmlspecialchars($this->lang->line('quickbooks_export_acceptance'), ENT_QUOTES, 'UTF-8')?>",
      acceptanceValue: "<?=htmlspecialchars($this->lang->line('quickbooks_export_acceptance_safe'), ENT_QUOTES, 'UTF-8')?>",
      queuedAt: "<?=htmlspecialchars($this->lang->line('quickbooks_export_queued_at'), ENT_QUOTES, 'UTF-8')?>"
    };

    var qbExportEndpoint = "<?=base_url('quickbooks/export_skeleton')?>";
    var $qbExportForm = $('#quickbooks-export-form');
    var $qbExportButton = $('#qb-export-submit');
    var $qbExportReset = $('#qb-export-reset');
    var $qbExportResult = $('#quickbooks-export-result');
    var $qbEngineSelect = $('#qb-export-engine');

    function toggleExportFields() {
      var engine = $qbEngineSelect.val();
      var showTerm = engine === 'schedule-term';
      var showMonth = engine === 'schedule-month';
      var showDay = engine === 'schedule-day';
      var showWindow = engine === 'reconciliation';

      $('#qb-export-term-group').toggle(showTerm);
      $('#qb-export-month-group').toggle(showMonth);
      $('#qb-export-day-group').toggle(showDay || showWindow);
      $('#qb-export-start-group').toggle(showWindow || showMonth || showDay);
      $('#qb-export-end-group').toggle(showWindow || showMonth || showDay);
    }

    function resetExportResult() {
      $qbExportResult.hide().removeClass('alert-success alert-warning alert-danger alert-info').empty();
    }

    function buildExportPayload() {
      var engine = $qbEngineSelect.val();
      var payload = { engine: engine };
      var term = $.trim($('#qb-export-term').val());
      var month = $('#qb-export-month').val();
      var day = $('#qb-export-day').val();
      var start = $('#qb-export-start').val();
      var end = $('#qb-export-end').val();
      var notes = $.trim($('#qb-export-notes').val());
      var key = $.trim($('#qb-export-key').val());

      if (engine === 'schedule-term' && term !== '') {
        payload.term = term;
      }

      if (engine === 'schedule-month' && month !== '') {
        payload.month = month;
      }

      if (engine === 'schedule-day' && day !== '') {
        payload.day = day;
      }

      if ((engine === 'schedule-month' || engine === 'schedule-day' || engine === 'reconciliation') && start !== '') {
        payload.start_date = start;
      }

      if ((engine === 'schedule-month' || engine === 'schedule-day' || engine === 'reconciliation') && end !== '') {
        payload.end_date = end;
      }

      if (engine === 'reconciliation' && day !== '') {
        payload.day = day;
      }

      if (notes !== '') {
        payload.notes = notes;
      }

      if (key !== '') {
        payload.idempotency_key = key;
      }

      return payload;
    }

    function renderExportSummary(data) {
      var summary = data.response || {};
      var replayed = !!data.replayed;
      var message = replayed ? qbExportStrings.replayed : qbExportStrings.success;
      var html = '<h5 class="no-margin">' + qbExportStrings.heading + '</h5>';
      html += '<p>' + message + '</p>';

      if (summary.engine_label) {
        html += '<p><strong>' + qbExportStrings.engine + ':</strong> ' + summary.engine_label + '</p>';
      } else if (summary.engine) {
        html += '<p><strong>' + qbExportStrings.engine + ':</strong> ' + summary.engine + '</p>';
      }

      if (summary.queued_at) {
        html += '<p><strong>' + qbExportStrings.queuedAt + ':</strong> ' + summary.queued_at + '</p>';
      }

      if (summary.idempotency_key) {
        html += '<p><strong>' + qbExportStrings.idLabel + ':</strong> ' + summary.idempotency_key + '</p>';
      }

      if (summary.request_hash) {
        html += '<p><strong>' + qbExportStrings.hash + ':</strong> ' + summary.request_hash + '</p>';
      }

      if (summary.message) {
        html += '<p>' + summary.message + '</p>';
      }

      if (summary.acceptance && summary.acceptance.reason) {
        html += '<p><strong>' + qbExportStrings.acceptance + ':</strong> ' + qbExportStrings.acceptanceValue + ' – ' + summary.acceptance.reason + '</p>';
      }

      if (summary.filters && Object.keys(summary.filters).length) {
        html += '<pre class="small">' + JSON.stringify(summary.filters, null, 2) + '</pre>';
      }

      $qbExportResult
        .removeClass('alert-success alert-warning alert-danger alert-info')
        .addClass(replayed ? 'alert-info' : 'alert-success')
        .html(html)
        .show();
    }

    function handleExportError(xhr) {
      var response = xhr.responseJSON || {};
      var error = response.error || {};
      var message = error.message || qbExportStrings.error;
      var isConflict = xhr.status === 409;

      if (isConflict && (!error.message || error.message === '')) {
        message = qbExportStrings.conflict;
      }

      $qbExportResult
        .removeClass('alert-success alert-info alert-warning alert-danger')
        .addClass(isConflict ? 'alert-warning' : 'alert-danger')
        .html('<p>' + message + '</p>')
        .show();
    }

    $qbExportReset.on('click', function () {
      if ($qbExportForm.length) {
        $qbExportForm[0].reset();
      }
      toggleExportFields();
      resetExportResult();
    });

    $qbEngineSelect.on('change', function () {
      toggleExportFields();
      resetExportResult();
    });

    $qbExportForm.on('submit', function (e) {
      e.preventDefault();
      if ($qbExportButton.prop('disabled')) {
        return;
      }

      resetExportResult();
      var payload = buildExportPayload();

      $qbExportButton.prop('disabled', true).addClass('disabled');

      $.ajax({
        url: qbExportEndpoint,
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function (response) {
          if (response && response.data) {
            renderExportSummary(response.data);
          } else {
            handleExportError({ status: 400, responseJSON: { error: { message: qbExportStrings.error } } });
          }
        },
        error: function (xhr) {
          handleExportError(xhr);
        },
        complete: function () {
          $qbExportButton.prop('disabled', false).removeClass('disabled');
        }
      });
    });

    toggleExportFields();

    $(document).on('click', '#syncButton', function() {
      $(this).attr('disabled', 'disabled');
      $.ajax({
          type: 'POST',
          url: "<?=base_url('quickbooks/sync')?>",
          async: true,
          dataType: "html",
          success: function(data) {
              var response = JSON.parse(data);
              errrorLoader(response);
          },
          cache: false,
          contentType: false,
          processData: false
      });
    });

    $(document).on('click', '#recoverButton', function() {
      $(this).attr('disabled', 'disabled');
      var start = $("#startdate").val();
      var end = $("#enddate").val();
      $.ajax({
          type: 'POST',
          url: "<?=base_url('quickbooks/sync')?>",
          data: {"start" : start, "end" : end},
          async: true,
          dataType: "html",
          success: function(data) {
              var response = JSON.parse(data);
              errrorLoader(response);
          },
          cache: false,
          contentType: false,
          processData: false
      });
    });

    function errrorLoader(response) {
        if(response.status) {
            window.location = "<?=base_url("quickbooks/index")?>";
        } else {
            $('#syncButton').removeAttr('disabled');
            $('#recoverButton').removeAttr('disabled');
            $.each(response.error, function(index, val) {
                toastr["error"](val)
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
            });
        }
    }

    $("#classesID").change(function() {
        var id = $(this).val();
        if(parseInt(id)) {
            if(id === '0') {
                $('#sectionID').val(0);
                $('#studentID').val(0);
            } else {
                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('student_statement/sectioncall')?>",
                    data: {"id" : id},
                    dataType: "html",
                    success: function(data) {
                       $('#sectionID').html(data);
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('student_statement/studentcall')?>",
                    data: {"classesID" : id},
                    dataType: "html",
                    success: function(data) {
                       $('#studentID').html(data);
                    }
                });
            }
        }
    });

    $("#sectionID").change(function() {
        var id = $(this).val();
        var classesID = $('#classesID').val();
        if(parseInt(id)) {
            if(id === '0') {
                $('#sectionID').val(0);
            } else {
                if(classesID === '0') {
                    $('#classesID').val(0);
                } else {
                    $.ajax({
                        type: 'POST',
                        url: "<?=base_url('student_statement/studentcall')?>",
                        data: {"classesID" : classesID, "sectionID" : id},
                        dataType: "html",
                        success: function(data) {
                           $('#studentID').html(data);
                        }
                    });
                }
            }
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('student_statement/studentcall')?>",
                data: {"classesID" : classesID, "sectionID" : id},
                dataType: "html",
                success: function(data) {
                   $('#studentID').html(data);
                }
            });
        }
    });
  });

  var url = '<?php echo $authUrl; ?>';

  var OAuthCode = function(url) {

      this.loginPopup = function (parameter) {
          this.loginPopupUri(parameter);
      }

      this.loginPopupUri = function (parameter) {

          // Launch Popup
          var parameters = "location=1,width=800,height=650";
          parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

          var win = window.open(url, 'connectPopup', parameters);
          var pollOAuth = window.setInterval(function () {
              try {

                  if (win.document.URL.indexOf("code") != -1) {
                      window.clearInterval(pollOAuth);
                      win.close();
                      location.reload();
                  }
              } catch (e) {
                  console.log(e)
              }
          }, 100);
      }
  }

  var oauth = new OAuthCode(url);

</script>
