

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
  	<div>
    	<table width="100%">
      		<tr>
        		<td widht="5%">
          			<h2>
		            	<?php
		              		if($siteinfos->photo) {
		                  		$array = array(
		                      		"src" => base_url('uploads/images/'.$siteinfos->photo),
		                      		'width' => '25px',
		                      		'height' => '25px',
		                      		'style' => 'margin-top:-8px'
		                  		);
		                  		echo img($array);
		              		}
		              	?>
          			</h2>
        		</td>
				<td widht="75%">
					<h3 class="top-site-header-title"><?php  echo $siteinfos->sname; ?></h3>
				</td>
				<td class="20%">
					<h5 class="top-site-header-create-title"><?php  echo $this->lang->line("global_date")." : ". date("d M Y"); ?></h5>
				</td>
			</tr>
		</table>
    	<br>
	    <table width="100%">
	      	<tr>
	        	<td width="50%">
	          		<table>
	            		<tbody>
	                		<tr>
	                    		<th class="site-header-title-float"><?php  echo $this->lang->line("global_from"); ?></th>
	                		</tr>
			                <?php if(customCompute($siteinfos)) { ?>
			                    <tr>
			                        <td><?=$siteinfos->sname?></td>
			                    </tr>
			                    <tr>
			                        <td><?=$siteinfos->address?></td>
			                    </tr>
			                    <tr>
			                        <td><?=$this->lang->line("global_phone_number"). " : ". $siteinfos->phone?></td>
			                    </tr>
			                    <tr>
			                        <td><?=$this->lang->line("global_email"). " : ". $siteinfos->email?></td>
			                    </tr>
			                <?php } ?>
	            		</tbody>
	          		</table>
	        	</td>
	        	<td width="50%">
	            	<table >
	              		<tbody>
	              			<tr>
			                    <th class="site-header-title-float"><?php  echo $this->lang->line("global_to"); ?></th>
			                </tr>
			                <tr>
			                    <td><?php  echo $student->srname; ?></td>
			                </tr>
			                <tr>
			                    <td><?php  echo $this->lang->line("global_classes"). " : ". $student->srclasses; ?></td>
			                </tr>
			                <tr>
			                    <td><?php  echo $this->lang->line("global_register_no"). " : ". $student->srstudentID; ?></td>
			                </tr>
			                <?php if(customCompute($student)) { ?>
				                <tr>
				                  <td><?=$this->lang->line("global_email"). " : ". $student->email?></td>
				                </tr>
			                <?php } ?>
	              		</tbody>
	            	</table>
	        	</td>
	      	</tr>
	    </table>
	    <br>
	    <table class="table table-bordered">
	      	<thead>
		        <tr>
		            <th><?=$this->lang->line('global_date')?></th>
		            <th><?=$this->lang->line('global_description')?></th>
		            <th><?=$this->lang->line('global_debit')?></th>
                <th><?=$this->lang->line('global_credit')?></th>
                <th><?=$this->lang->line('global_balance')?></th>
		        </tr>
	      	</thead>
	      	<tbody>
	          	<?php $tdebit = $tcredit = 0; if(customCompute($statement)) { foreach($statement as $data) { if ($data['column'] == "debit") { $tdebit += $data['amount']; } elseif ($data['column'] == "credit") { $tcredit += $data['amount']; }?>
		            <tr>
		                <td data-title="<?=$this->lang->line('global_date')?>">
		                    <?=$data['date']?>
		                </td>

		                <td data-title="<?=$this->lang->line('global_description')?>">
		                    <?=$data['fee_type']?>
		                </td>

		                <td data-title="<?=$this->lang->line('global_debit')?>">
		                    <?=$data['column'] == "debit" ? number_format($data['amount'], 2) : ''?>
		                </td>

                    <td data-title="<?=$this->lang->line('global_credit')?>">
		                    <?=$data['column'] == "credit" ? number_format($data['amount'], 2) : ''?>
		                </td>

                    <td data-title="<?=$this->lang->line('global_balance')?>">
		                    <?=number_format($data['balance'], 2)?>
		                </td>
		            </tr>
	          	<?php } } ?>
	      	</tbody>
	      	<tfoot>
	          	<tr>
                  <td></td>
                  <td><b><?=$this->lang->line('global_total')?></b></td>
                  <td><b><?=number_format($tdebit, 2)?></b></td>
                  <td><b><?=number_format($tcredit, 2)?></b></td>
                  <td></td>
	          	</tr>
	      	</tfoot>
	    </table>

	    <table width="100%">
	        <tr>
	            <td width="35%">
					        <table>
	                    <tr>
	                        <td>M-PESA Paybill</td>
                          <td><b><?=$paybill?></b></td>
	                    </tr>
                      <tr>
                          <td>Account number</td>
                          <td><b><?=$student->srstudentID?></b></td>
                      </tr>
	                </table>
	            </td>
	        </tr>
	    </table>
  	</div>
</body>
</html>
