
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-invoice"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_mpesa')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

              <?php if(customCompute($allocated) > 0 || customCompute($unallocated) > 0) { ?>
                  <div class="nav-tabs-custom">
                      <ul class="nav nav-tabs">
                          <li class="active"><a data-toggle="tab" href="#allocated" aria-expanded="true"><?=$this->lang->line("mpesa_allocated_payments")?></a></li>
                          <li><a data-toggle="tab" href="#unallocated" aria-expanded="true"><?=$this->lang->line("mpesa_unallocated_payments")?></a></li>
                      </ul>



                      <div class="tab-content">
                          <div id="allocated" class="tab-pane active">
                              <div id="hide-table">
                                  <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                                      <thead>
                                          <tr>
                                              <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_name')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_msisdn')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_billrefnumber')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_transid')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_amount')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_date')?></th>
                                              <th class="col-sm-2"><?=$this->lang->line('mpesa_action')?></th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php if(customCompute($allocated)) {$i = 1; foreach($allocated as $payment) { ?>
                                              <tr>
                                                  <td data-title="<?=$this->lang->line('slno')?>">
                                                      <?php echo $i; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_name')?>">
                                                      <?php echo $payment->uname; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_msisdn')?>">
                                                      <?php echo $payment->MSISDN; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_billrefnumber')?>">
                                                      <?php echo $payment->studentID; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_transid')?>">
                                                      <?php echo $payment->transactionID; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_amount')?>">
                                                      <?php echo $payment->paymentamount; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_date')?>">
                                                      <?php echo $payment->paymentdate; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_action')?>">
                                                      <?php echo btn_edit('paymenthistory/edit/'.$payment->paymentID, $this->lang->line('mpesa_edit')); ?>
                                                  </td>
                                             </tr>
                                          <?php $i++; }} ?>
                                      </tbody>
                                  </table>
                              </div>

                          </div>

                          <div id="unallocated" class="tab-pane">
                              <div id="hide-table">
                                  <table id="example2" class="table table-striped table-bordered table-hover dataTable no-footer">
                                      <thead>
                                        <tr>
                                            <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_name')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_msisdn')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_billrefnumber')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_transid')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_amount')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_date')?></th>
                                            <th class="col-sm-2"><?=$this->lang->line('mpesa_action')?></th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                          <?php if(customCompute($unallocated)) {$i = 1; foreach($unallocated as $payment) { ?>
                                              <tr>
                                                  <td data-title="<?=$this->lang->line('slno')?>">
                                                      <?php echo $i; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_name')?>">
                                                      <?php echo $payment->uname; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_msisdn')?>">
                                                      <?php echo $payment->MSISDN; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_billrefnumber')?>">
                                                      <?php echo $payment->studentID; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_transid')?>">
                                                      <?php echo $payment->transactionID; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_amount')?>">
                                                      <?php echo $payment->paymentamount; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_date')?>">
                                                      <?php echo $payment->paymentdate; ?>
                                                  </td>
                                                  <td data-title="<?=$this->lang->line('mpesa_action')?>">
                                                      <?php echo btn_edit('paymenthistory/edit/'.$payment->paymentID, $this->lang->line('mpesa_edit')); ?>
                                                  </td>
                                             </tr>
                                          <?php $i++; }} ?>
                                      </tbody>
                                  </table>
                              </div>

                          </div>
                        </div>
                      </div> <!-- nav-tabs-custom -->
                  <?php }?>
            </div> <!-- col-sm-12 -->
        </div>
    </div>
</div>
