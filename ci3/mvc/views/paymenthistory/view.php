
<?php if(customCompute($payment)) {?>
    <div class="well">
        <div class="row">

            <div class="col-sm-6">
                <button class="btn-cs btn-sm-cs" onclick="javascript:printDiv('printablediv')"><span class="fa fa-print"></span> <?=$this->lang->line('print')?> </button>
                <?php if(($siteinfos->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) {
                    if(permissionChecker('paymenthistory_edit')) {
                            if($payment->paymenttype != 'Paypal' && $payment->paymenttype != 'Stripe' && $payment->paymenttype != 'PayUmoney') {
                                echo btn_sm_edit('paymenthistory/edit/'.$payment->paymentID, $this->lang->line('edit'));
                            }
                        }
                } ?>
			</div>

            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("paymenthistory/index")?>"><?=$this->lang->line('panel_title')?></a></li>
                    <li class="active"><?=$this->lang->line('view')?></li>
                </ol>
            </div>
        </div>
    </div>

    <div id="printablediv">
    	<section class="content invoice" >
    		<div class="row">
    		    <div class="col-xs-12">
    		        <h2 class="page-header">
    		            <?php
    	                    if($siteinfos->photo) {
    		                    $array = array(
    		                        "src" => base_url('uploads/images/'.$siteinfos->photo),
    		                        'width' => '25px',
    		                        'height' => '25px',
    		                        'class' => 'img-circle',
                                    'style' => 'margin-top:-10px',
    		                    );
    		                    echo img($array);
    		                }
    	                ?>
    	                <?php  echo $siteinfos->sname; ?>
    		            <small class="pull-right"><?=$this->lang->line('paymenthistory_create_date').' : '.date('d M Y')?></small>
    		        </h2>
    		    </div><!-- /.col -->
    		</div>
    		<div class="row invoice-info">
    		    <div class="col-sm-4 invoice-col" style="font-size: 16px;">
    				<?php  echo $this->lang->line("paymenthistory_payment_to"); ?>
    				<address>
    					<strong><?=$siteinfos->sname?></strong><br>
    					<?=$siteinfos->address?><br>
    					<?=$this->lang->line("paymenthistory_phone"). " : ". $siteinfos->phone?><br>
    					<?=$this->lang->line("paymenthistory_email"). " : ". $siteinfos->email?><br>
    				</address>


    		    </div><!-- /.col -->
    		    <div class="col-sm-4 invoice-col" style="font-size: 16px;">
    	        	<?=$this->lang->line("paymenthistory_payment_for")?>
    	        	<address >
    	        		<strong><?=$payment->srname?></strong><br>
    	        		<?=$this->lang->line("paymenthistory_classes"). " : ". $payment->srclasses?><br>
    	        		<?=$this->lang->line("paymenthistory_registerno"). " : ". $payment->srstudentID?>
    	        	</address>
    		    </div><!-- /.col -->
    		    <div class="col-sm-4 invoice-col" style="font-size: 16px;">
                <?=$this->lang->line('paymenthistory_referencenumber') ." : ". $payment->globalpaymentID?>
    		    </div>
    		</div>
            <br />

            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table class="table table-bordered product-style">
                            <thead>
                                <tr>
								    <th class="col-lg-10"><?=$this->lang->line('paymenthistory_payment')?></th>
                                    <th class="col-lg-2"><?=$this->lang->line('paymenthistory_amount')?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
								    <td>
										<?=$this->lang->line("paymenthistory_transaction"). " : ". $payment->transactionID ."; ". $payment->paymenttype?>
									</td>
									<td data-title="<?=$this->lang->line('paymenthistory_amount')?>">
										<?=number_format($payment->paymentamount, 2)?>
									</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><span class="pull-right"><b><?=$this->lang->line('paymenthistory_totalamount')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
                                    <td><b><?=number_format($payment->paymentamount, 2)?></b></td>
                                </tr>
								<tr>
									<td><span class="pull-right"><b>Balance before</b></td>
									<td><?=number_format($balance, 2)?></td>
								</tr>
								<tr>
									<td><span class="pull-right"><b>Balance after</b></td>
									<?php
										$balanceafter = $balance - $payment->paymentamount;
									?>
									<td><?=number_format($balanceafter, 2)?></td>
								</tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-sm-3 col-xs-12">
                    <div class="well well-sm">
                        <p>
                            <?=$this->lang->line('paymenthistory_memo')?> : <?=$payment->memo?>
                        </p>
                    </div>
                </div>

                <div class="col-sm-3 col-xs-12 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?=$this->lang->line('paymenthistory_create_by')?> : <?=$createuser?>
                            <br>
                            <?=$this->lang->line('paymenthistory_date')?> : <?=date('d M Y', strtotime($payment->paymentdate))?>
                        </p>
                    </div>
                </div>
            </div>


    		<!-- this row will not appear when printing -->
    	</section><!-- /.content -->
    </div>

    <script language="javascript" type="text/javascript">
        function printDiv(divID) {
            //Get the HTML of div
            var divElements = document.getElementById(divID).innerHTML;
            //Get the HTML of whole page
            var oldPage = document.body.innerHTML;

            //Reset the page's HTML with div's HTML only
            document.body.innerHTML =
              "<html><head><title></title></head><body>" +
              divElements + "</body>";

            //Print Page
            window.print();

            //Restore orignal HTML
            document.body.innerHTML = oldPage;
            window.location.reload();
        }
    </script>
<?php } ?>
