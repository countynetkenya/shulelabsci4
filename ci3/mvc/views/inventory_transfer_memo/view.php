
<?php if(customCompute($mainstock)) { ?>
    <div class="well">
        <div class="row">

            <div class="col-sm-6">
                <button class="btn-cs btn-sm-cs" onclick="javascript:printDiv('printablediv')"><span class="fa fa-print"></span> <?=$this->lang->line('print')?> </button>
                <?php if($this->session->userdata('loginuserID') != $mainstock->mainstockuserID) {?>
                <button class="btn-cs btn-sm-cs" onclick="window.location.href='<?php echo base_url("stock/approve/$mainstock->mainstockID")?>'"><span class="fa fa-check"></span> <?=$this->lang->line('approve')?> </button>
                <?php }?>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("inventory_transfer_memo/index")?>"><?=$this->lang->line('menu_inventory_transfer_memo')?></a></li>
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
    		            <small class="pull-right"><?=$this->lang->line('stock_create_date').' : '.date('d M Y', strtotime($mainstock->mainstockcreate_date))?></small>
    		        </h2>
    		    </div><!-- /.col -->
    		</div>
    		<div class="row invoice-info">
    		    <div class="col-sm-6 invoice-col" style="font-size: 16px;">
    				<?php  echo $this->lang->line("stock_from"); ?>
    				<address>
    					<strong><?=$productwarehouses[$mainstock->stockfromwarehouseID]?></strong>
    				</address>


    		    </div><!-- /.col -->
    		    <div class="col-sm-6 invoice-col" style="font-size: 16px;">
    	        	<?=$this->lang->line("stock_to")?>
    	        	<address >
    	        		<strong><?=$productwarehouses[$mainstock->stocktowarehouseID]?></strong>
    	        	</address>
    		    </div><!-- /.col -->
    		</div>
            <br />

            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table class="table table-bordered product-style">
                            <thead>
                                <tr>
                                    <th class="col-lg-2" ><?=$this->lang->line('slno')?></th>
                                    <th class="col-lg-6"><?=$this->lang->line('stock_product')?></th>
                                    <th class="col-lg-2"><?=$this->lang->line('stock_quantity')?></th>
                                    <th class="col-lg-2"><?=$this->lang->line('stock_cost')?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $total = 0; $i = 1; if(customCompute($stocks)) { foreach($stocks as $stock) { $cost = $stock->quantity * $products[$stock->productID]['productbuyingprice']; $total += $cost;?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_product')?>">
                                        <?=$products[$stock->productID]['productname']?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_quantity')?>">
                                        <?=$stock->quantity?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('stock_quantity')?>">
                                        <?=number_format($cost, 2)?>
                                    </td>
                                </tr>
                              <?php $i++; } }?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><span class="pull-right"><b><?=$this->lang->line('stock_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
                                    <td><b><?=number_format($total, 2)?></b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

				<div class="col-sm-3 col-xs-12">
                    <div class="well well-sm">
                        <p>
                            <?=$this->lang->line('stock_memo')?>:
                            <br>
                            <?=$mainstock->memo?>
                        </p>
                    </div>
                </div>

                <div class="col-sm-3 col-xs-12 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?=$this->lang->line('stock_create_by')?> : <?=$createuser?>
                            <br>
                            <?=$this->lang->line('stock_create_date')?> : <?=date('d M Y', strtotime($mainstock->mainstockcreate_date))?>
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
