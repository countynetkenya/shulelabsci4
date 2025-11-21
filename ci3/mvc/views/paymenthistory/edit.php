
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-payment"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("paymenthistory/index")?>"><?=$this->lang->line('menu_paymenthistory')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_paymenthistory')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post">
                    <?php if(strtolower(str_replace('-','',$payment->paymenttype)) != "mpesa") {
                        if(form_error('amount'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="amount" class="col-sm-2 control-label">
                            <?=$this->lang->line("paymenthistory_amount")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="amount" name="amount" value="<?=set_value('amount', $payment->paymentamount)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('amount'); ?>
                        </span>
                    </div>
                  <?php }?>

                    <?php
                        if(form_error('paymentmethod'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="paymentmethod" class="col-sm-2 control-label">
                            <?=$this->lang->line("paymenthistory_paymentmethod")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $array = array('0' => $this->lang->line("paymenthistory_select_paymentmethod"));
                                if(customCompute($paymentmethods)) {
                                  foreach($paymentmethods as $paymentmethod)
                                    $array[$paymentmethod->paymenttypesID] = $paymentmethod->paymenttypes;
                                }
                                echo form_dropdown("paymentmethod", $array, set_value("paymentmethod", $payment->paymenttypeID), "id='paymentmethod' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('paymentmethod'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('studentID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="student" class="col-sm-2 control-label">
                            <?=$this->lang->line("paymenthistory_student")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $array = $array = array('0' => $this->lang->line("paymenthistory_select_student"));
                                foreach ($students as $student) {
                                    $array[$student->srstudentID] = $student->srname;
                                }
                                echo form_dropdown("studentID", $array, set_value("student", $payment->studentID), "id='studentID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('studentID'); ?>
                        </span>
                    </div>

                    <?php if(strtolower(str_replace('-','',$payment->paymenttype)) != "mpesa") {
                        if(form_error('transactionID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="transactionID" class="col-sm-2 control-label">
                            <?=$this->lang->line("paymenthistory_transaction")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="transactionID" name="transactionID" value="<?=set_value('transactionID', $payment->transactionID)?>" required>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('transactionID'); ?>
                        </span>
                    </div>
                    <?php }?>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update_payment")?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$('.select2').select2();
</script>
