<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            $generatepdfurl = base_url("transactionsummary/pdf/".strtotime($fromdate)."/".strtotime($todate).'/1');
            $generatexmlurl = base_url("transactionsummary/xlsx/".strtotime($fromdate)."/".strtotime($todate).'/1');
            echo btn_printReport('transactionsummary', $this->lang->line('report_print'), 'printablediv');
            //echo btn_pdfPreviewReport('transactionsummary',$generatepdfurl, $this->lang->line('report_pdf_preview'));
            //echo btn_xmlReport('transactionreport',$generatexmlurl, $this->lang->line('report_xlsx'));
            //echo btn_sentToMailReport('transactionreport', $this->lang->line('report_send_pdf_to_mail'));
        ?>
		<button class="btn btn-default" onclick="javascript:csvDiv('printablediv')"> Download CSV </button>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?=$this->lang->line('transactionsummary_report_for')?> - <?=$this->lang->line('transactionsummary_transaction')?>  </h3>
    </div><!-- /.box-header -->

    <div id="printablediv">
            <!-- form start -->
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>
                <!--<?php if($fromdate >= 0 || $todate >= 0 ) { ?>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="pull-left" style="margin-top:0px">
                                    <?=$this->lang->line('transactionsummary_fromdate')?> : <?=date('d M Y',strtotime($fromdate))?></p>
                                </h5>
                                <h5 class="pull-right" style="margin-top:0px">
                                    <?=$this->lang->line('transactionsummary_todate')?> : <?=date('d M Y', strtotime($todate))?></p>
                                </h5>
                            </div>
                        </div>
                    </div>
                <?php } ?>-->

                <div class="col-sm-12" style="margin-top:0px">
                  <?php if ($reportType == "invoice_report") {
                    if ($reportDetails == "student_detail") {?>
                    <table class="table table-striped table-bordered table-hover">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_student_detail')?></th>
                            <th><?=$this->lang->line('transactionsummary_class')?></th>
                            <th><?=$this->lang->line('transactionsummary_group')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($students as $student) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalStudentInvoiceAmount[$student->srstudentID]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_student_detail')?>">
  														<?=$student->srname?> - <?=$student->srstudentID?> - <?=$student->srclasses?> - <?=$student->group?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_class')?>">
  														<?=$student->srclasses?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_group')?>">
  														<?=$student->group?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalStudentInvoiceAmount[$student->srstudentID][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalStudentInvoiceAmount[$student->srstudentID][$key]) ? number_format($totalStudentInvoiceAmount[$student->srstudentID][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalStudentInvoiceAmount[$student->srstudentID]['selected_total']) ? number_format($totalStudentInvoiceAmount[$student->srstudentID]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											  <?php }
                          }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "class_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_class_summary')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($classes as $class) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalClassInvoiceAmount[$class->classesID]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_class_summary')?>">
  														<?=$class->classes?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalClassInvoiceAmount[$class->classesID][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalClassInvoiceAmount[$class->classesID][$key]) ? number_format($totalClassInvoiceAmount[$class->classesID][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalClassInvoiceAmount[$class->classesID]['selected_total']) ? number_format($totalClassInvoiceAmount[$class->classesID]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "division_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_division_summary')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($divisions as $division) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalDivisionInvoiceAmount[$student->srstudentID]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_division_summary')?>">
  														<?=$division->divisions?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$division->divisionsID] += $totalDivisionInvoiceAmount[$student->srstudentID][$division->divisionsID];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalDivisionInvoiceAmount[$student->srstudentID][$division->divisionsID]) ? number_format($totalDivisionInvoiceAmount[$student->srstudentID][$division->divisionsID],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalDivisionInvoiceAmount[$student->srstudentID]['selected_total']) ? number_format($totalDivisionInvoiceAmount[$student->srstudentID]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
                      </tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "date_detail") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%">
  										<thead>
  											<tr>
                          <th><?=$this->lang->line('transactionsummary_date_detail')?></th>
  												<th><?=$this->lang->line('transactionsummary_transaction_number')?></th>
  												<th><?=$this->lang->line('transactionsummary_student_detail')?></th>
                          <?php foreach($columns as $key=>$value) {?>
                          <th><?=$value?></th>
                          <?php }?>
                          <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                      </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($invoices as $invoice) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalDatedetailInvoiceAmount[$invoice->invoiceID]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_date_detail')?>">
  														<?=$invoice->date?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_transaction_number')?>">
  														<?=$invoice->invoiceID?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_student_detail')?>">
  														<?=$invoice->srname?> - <?=$invoice->srstudentID?> - <?=$invoice->srclasses?> - <?=$invoice->group?>
  													</td>
  													<?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalDatedetailInvoiceAmount[$invoice->invoiceID][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalDatedetailInvoiceAmount[$invoice->invoiceID][$key]) ? number_format($totalDatedetailInvoiceAmount[$invoice->invoiceID][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalDatedetailInvoiceAmount[$invoice->invoiceID]['selected_total']) ? number_format($totalDatedetailInvoiceAmount[$invoice->invoiceID]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
                      </tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "date_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_date_summary')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($dates as $date) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalDateInvoiceAmount[$date]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_date_summary')?>">
  														<?=$date?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalDateInvoiceAmount[$date][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalDateInvoiceAmount[$date][$key]) ? number_format($totalDateInvoiceAmount[$date][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalDateInvoiceAmount[$date]['selected_total']) ? number_format($totalDateInvoiceAmount[$date]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
                      </tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "month_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_month_summary')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($months as $month) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalMonthInvoiceAmount[$month]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_month_summary')?>">
  														<?=date("M-y", strtotime($month))?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalMonthInvoiceAmount[$month][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalMonthInvoiceAmount[$month][$key]) ? number_format($totalMonthInvoiceAmount[$month][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalMonthInvoiceAmount[$month]['selected_total']) ? number_format($totalMonthInvoiceAmount[$month]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "term_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_term_summary')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($schoolterms as $schoolterm) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalTermInvoiceAmount[$schoolterm->schooltermID]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_term_summary')?>">
  														<?=$schoolterm->schooltermtitle?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalTermInvoiceAmount[$schoolterm->schooltermID][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalTermInvoiceAmount[$schoolterm->schooltermID][$key]) ? number_format($totalTermInvoiceAmount[$schoolterm->schooltermID][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalTermInvoiceAmount[$schoolterm->schooltermID]['selected_total']) ? number_format($totalTermInvoiceAmount[$schoolterm->schooltermID]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "year_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_year_summary')?></th>
                            <?php foreach($columns as $key=>$value) {?>
                            <th><?=$value?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_selected_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalTermFee = 0;
  												$totalFeetypes = [];
  												$totalTotal = 0;

  												foreach($years as $year) {
                          if ($selectedTotal == 0 || ($selectedTotal == 1 && $totalYearInvoiceAmount[$year]['selected_total'] > 0)) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_year_summary')?>">
  														<?=$year?>
  													</td>
                            <?php foreach($columns as $key=>$value) {
                              $totalColumns[$key] += $totalYearInvoiceAmount[$year][$key];
                              ?>
                            <td data-title="<?=$value?>"><?=isset($totalYearInvoiceAmount[$year][$key]) ? number_format($totalYearInvoiceAmount[$year][$key],2) : number_format(0, 2)?></td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount')?>"><?=isset($totalYearInvoiceAmount[$year]['selected_total']) ? number_format($totalYearInvoiceAmount[$year]['selected_total'],2) : number_format(0, 2)?></td>
  												</tr>
  											<?php }
                        }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($columns as $key=>$value) {?>
                        <td data-title="<?=$value?>"><?=isset($totalColumns[$key]) ? number_format($totalColumns[$key],2) : number_format(0, 2)?></td>
                        <?php }?>
                        <td data-title="<?=$this->lang->line('transactionsummary_selected_total_amount_total')?>" class="text-bold"></td>
  										</tfoot>
  									</table>
                  <?php }
                  }
                  elseif ($reportType == "creditmemo_report") {
                    if ($reportDetails == "student_detail") {?>
                      <table class="table table-striped table-bordered table-hover" style="width:100%">
    										<thead>
    											<tr>
                              <th><?=$this->lang->line('transactionsummary_student_detail')?></th>
                              <th><?=$this->lang->line('transactionsummary_class')?></th>
                              <th><?=$this->lang->line('transactionsummary_group')?></th>
                              <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                              <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                              <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                              <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                              <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                          </tr>
    										</thead>
    										<tbody>
    											<?php
    												$totalSiblingDiscount = 0;
    												$totalHeadteacherDiscount = 0;
    												$totalStaffDiscount = 0;
    												$totalDirectorDiscount = 0;
    												$totalTotal = 0;

    												foreach($students as $student) { ?>
    												<tr>
    													<td data-title="<?=$this->lang->line('transactionsummary_student_detail')?>">
    														<?=$student->srstudentID?> - <?=$student->srname?> - <?=$student->srclasses?> - <?=$student->group?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_class')?>">
    														<?=$student->srclasses?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_group')?>">
    														<?=$student->group?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
    														<?php
    															$totalSiblingDiscount += $totalStudentCreditmemoAmount[$student->srstudentID]['sibling_discount'];
    														?>
    														<?=isset($totalStudentCreditmemoAmount[$student->srstudentID]['sibling_discount']) ? number_format($totalStudentCreditmemoAmount[$student->srstudentID]['sibling_discount'],2) : number_format(0, 2)?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
    														<?php
    															$totalHeadteacherDiscount += $totalStudentCreditmemoAmount[$student->srstudentID]['headteacher_discount'];
    														?>
    														<?=isset($totalStudentCreditmemoAmount[$student->srstudentID]['headteacher_discount']) ? number_format($totalStudentCreditmemoAmount[$student->srstudentID]['headteacher_discount'],2) : number_format(0, 2)?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
    														<?php
    															$totalStaffDiscount += $totalStudentCreditmemoAmount[$student->srstudentID]['staff_discount'];
    														?>
    														<?=isset($totalStudentCreditmemoAmount[$student->srstudentID]['staff_discount']) ? number_format($totalStudentCreditmemoAmount[$student->srstudentID]['staff_discount'],2) : number_format(0, 2)?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
    														<?php
    															$totalDirectorDiscount += $totalStudentCreditmemoAmount[$student->srstudentID]['director_discount'];
    														?>
    														<?=isset($totalStudentCreditmemoAmount[$student->srstudentID]['director_discount']) ? number_format($totalStudentCreditmemoAmount[$student->srstudentID]['director_discount'],2) : number_format(0, 2)?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
    														<?php
    															$totalTotal += $totalStudentCreditmemoAmount[$student->srstudentID]['total'];
    														?>
    														<?=isset($totalStudentCreditmemoAmount[$student->srstudentID]['total']) ? number_format($totalStudentCreditmemoAmount[$student->srstudentID]['total'],2) : number_format(0, 2)?>
    													</td>
    												</tr>
    											<?php }?>
    										</tbody>
    										<tfoot>
    											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
    											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
    											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
    											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
    											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
    											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
    										</tfoot>
    									</table>
                  <?php }
                  elseif ($reportDetails == "class_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_class_summary')?></th>
                            <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($classes as $class) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_class_summary')?>">
  														<?=$class->classes?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalClassCreditmemoAmount[$class->classesID]['sibling_discount'];
  														?>
  														<?=isset($totalClassCreditmemoAmount[$class->classesID]['sibling_discount']) ? number_format($totalClassCreditmemoAmount[$class->classesID]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalClassCreditmemoAmount[$class->classesID]['headteacher_discount'];
  														?>
  														<?=isset($totalClassCreditmemoAmount[$class->classesID]['headteacher_discount']) ? number_format($totalClassCreditmemoAmount[$class->classesID]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalClassCreditmemoAmount[$class->classesID]['staff_discount'];
  														?>
  														<?=isset($totalClassCreditmemoAmount[$class->classesID]['staff_discount']) ? number_format($totalClassCreditmemoAmount[$class->classesID]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalClassCreditmemoAmount[$class->classesID]['director_discount'];
  														?>
  														<?=isset($totalClassCreditmemoAmount[$class->classesID]['director_discount']) ? number_format($totalClassCreditmemoAmount[$class->classesID]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalClassCreditmemoAmount[$class->classesID]['total'];
  														?>
  														<?=isset($totalClassCreditmemoAmount[$class->classesID]['total']) ? number_format($totalClassCreditmemoAmount[$class->classesID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "division_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_division_summary')?></th>
                            <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($divisions as $division) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_division_summary')?>">
  														<?=$division->divisions?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalDivisionCreditmemoAmount[$division->divisionsID]['sibling_discount'];
  														?>
  														<?=isset($totalDivisionCreditmemoAmount[$division->divisionsID]['sibling_discount']) ? number_format($totalDivisionCreditmemoAmount[$division->divisionsID]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalDivisionCreditmemoAmount[$division->divisionsID]['headteacher_discount'];
  														?>
  														<?=isset($totalDivisionCreditmemoAmount[$division->divisionsID]['headteacher_discount']) ? number_format($totalDivisionCreditmemoAmount[$division->divisionsID]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalDivisionCreditmemoAmount[$division->divisionsID]['staff_discount'];
  														?>
  														<?=isset($totalDivisionCreditmemoAmount[$division->divisionsID]['staff_discount']) ? number_format($totalDivisionCreditmemoAmount[$division->divisionsID]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalDivisionCreditmemoAmount[$division->divisionsID]['director_discount'];
  														?>
  														<?=isset($totalDivisionCreditmemoAmount[$division->divisionsID]['director_discount']) ? number_format($totalDivisionCreditmemoAmount[$division->divisionsID]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalDivisionCreditmemoAmount[$division->divisionsID]['total'];
  														?>
  														<?=isset($totalDivisionCreditmemoAmount[$division->divisionsID]['total']) ? number_format($totalDivisionCreditmemoAmount[$division->divisionsID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "date_detail") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                          <th><?=$this->lang->line('transactionsummary_date_detail')?></th>
  												<th><?=$this->lang->line('transactionsummary_transaction_number')?></th>
  												<th><?=$this->lang->line('transactionsummary_student_detail')?></th>
                          <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                          <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                          <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                          <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                          <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                      </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($creditmemos as $creditmemo) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_date_detail')?>">
  														<?=$creditmemo->date?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_transaction_number')?>">
  														<?=$creditmemo->creditmemoID?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_student_detail')?>">
  														<?=$creditmemo->srname?> - <?=$creditmemo->srstudentID?> - <?=$creditmemo->srclasses?> - <?=$creditmemo->group?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['sibling_discount'];
  														?>
  														<?=isset($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['sibling_discount']) ? number_format($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['headteacher_discount'];
  														?>
  														<?=isset($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['headteacher_discount']) ? number_format($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['staff_discount'];
  														?>
  														<?=isset($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['staff_discount']) ? number_format($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['director_discount'];
  														?>
  														<?=isset($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['director_discount']) ? number_format($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['total'];
  														?>
  														<?=isset($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['total']) ? number_format($totalDatedetailCreditmemoAmount[$creditmemo->creditmemoID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "date_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_date_summary')?></th>
                            <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($dates2 as $date) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_date_summary')?>">
  														<?=$date?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalDateCreditmemoAmount[$date]['sibling_discount'];
  														?>
  														<?=isset($totalDateCreditmemoAmount[$date]['sibling_discount']) ? number_format($totalDateCreditmemoAmount[$date]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalDateCreditmemoAmount[$date]['headteacher_discount'];
  														?>
  														<?=isset($totalDateCreditmemoAmount[$date]['headteacher_discount']) ? number_format($totalDateCreditmemoAmount[$date]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalDateCreditmemoAmount[$date]['staff_discount'];
  														?>
  														<?=isset($totalDateCreditmemoAmount[$date]['staff_discount']) ? number_format($totalDateCreditmemoAmount[$date]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalDateCreditmemoAmount[$date]['director_discount'];
  														?>
  														<?=isset($totalDateCreditmemoAmount[$date]['director_discount']) ? number_format($totalDateCreditmemoAmount[$date]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalDateCreditmemoAmount[$date]['total'];
  														?>
  														<?=isset($totalDateCreditmemoAmount[$date]['total']) ? number_format($totalDateCreditmemoAmount[$date]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "month_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_month_summary')?></th>
                            <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($months2 as $month) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_month_summary')?>">
  														<?=date("M-y", strtotime($month))?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalMonthCreditmemoAmount[$month]['sibling_discount'];
  														?>
  														<?=isset($totalMonthCreditmemoAmount[$month]['sibling_discount']) ? number_format($totalMonthCreditmemoAmount[$month]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalMonthCreditmemoAmount[$month]['headteacher_discount'];
  														?>
  														<?=isset($totalMonthCreditmemoAmount[$month]['headteacher_discount']) ? number_format($totalMonthCreditmemoAmount[$month]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalMonthCreditmemoAmount[$month]['staff_discount'];
  														?>
  														<?=isset($totalMonthCreditmemoAmount[$month]['staff_discount']) ? number_format($totalMonthCreditmemoAmount[$month]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalMonthCreditmemoAmount[$month]['director_discount'];
  														?>
  														<?=isset($totalMonthCreditmemoAmount[$month]['director_discount']) ? number_format($totalMonthCreditmemoAmount[$month]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalMonthCreditmemoAmount[$month]['total'];
  														?>
  														<?=isset($totalMonthCreditmemoAmount[$month]['total']) ? number_format($totalMonthCreditmemoAmount[$month]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "term_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_term_summary')?></th>
                            <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($schoolterms as $schoolterm) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_term_summary')?>">
  														<?=$schoolterm->schooltermtitle?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalTermCreditmemoAmount[$schoolterm->schooltermID]['sibling_discount'];
  														?>
  														<?=isset($totalTermCreditmemoAmount[$schoolterm->schooltermID]['sibling_discount']) ? number_format($totalTermCreditmemoAmount[$schoolterm->schooltermID]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalTermCreditmemoAmount[$schoolterm->schooltermID]['headteacher_discount'];
  														?>
  														<?=isset($totalTermCreditmemoAmount[$schoolterm->schooltermID]['headteacher_discount']) ? number_format($totalTermCreditmemoAmount[$schoolterm->schooltermID]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalTermCreditmemoAmount[$schoolterm->schooltermID]['staff_discount'];
  														?>
  														<?=isset($totalTermCreditmemoAmount[$schoolterm->schooltermID]['staff_discount']) ? number_format($totalTermCreditmemoAmount[$schoolterm->schooltermID]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalTermCreditmemoAmount[$schoolterm->schooltermID]['director_discount'];
  														?>
  														<?=isset($totalTermCreditmemoAmount[$schoolterm->schooltermID]['director_discount']) ? number_format($totalTermCreditmemoAmount[$schoolterm->schooltermID]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalTermCreditmemoAmount[$schoolterm->schooltermID]['total'];
  														?>
  														<?=isset($totalTermCreditmemoAmount[$schoolterm->schooltermID]['total']) ? number_format($totalTermCreditmemoAmount[$schoolterm->schooltermID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "year_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_year_summary')?></th>
                            <th><?=$this->lang->line('transactionsummary_sibling_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_headteacher_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_staff_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_director_discount')?></th>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$totalSiblingDiscount = 0;
  												$totalHeadteacherDiscount = 0;
  												$totalStaffDiscount = 0;
  												$totalDirectorDiscount = 0;
  												$totalTotal = 0;

  												foreach($years2 as $year) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_year_summary')?>">
  														<?=$year?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>">
  														<?php
  															$totalSiblingDiscount += $totalYearCreditmemoAmount[$year]['sibling_discount'];
  														?>
  														<?=isset($totalYearCreditmemoAmount[$year]['sibling_discount']) ? number_format($totalYearCreditmemoAmount[$year]['sibling_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>">
  														<?php
  															$totalHeadteacherDiscount += $totalYearCreditmemoAmount[$year]['headteacher_discount'];
  														?>
  														<?=isset($totalYearCreditmemoAmount[$year]['headteacher_discount']) ? number_format($totalYearCreditmemoAmount[$year]['headteacher_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>">
  														<?php
  															$totalStaffDiscount += $totalYearCreditmemoAmount[$year]['staff_discount'];
  														?>
  														<?=isset($totalYearCreditmemoAmount[$year]['staff_discount']) ? number_format($totalYearCreditmemoAmount[$year]['staff_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>">
  														<?php
  															$totalDirectorDiscount += $totalYearCreditmemoAmount[$year]['director_discount'];
  														?>
  														<?=isset($totalYearCreditmemoAmount[$year]['director_discount']) ? number_format($totalYearCreditmemoAmount[$year]['director_discount'],2) : number_format(0, 2)?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalYearCreditmemoAmount[$year]['total'];
  														?>
  														<?=isset($totalYearCreditmemoAmount[$year]['total']) ? number_format($totalYearCreditmemoAmount[$year]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
  											<td data-title="<?=$this->lang->line('transactionsummary_sibling_discount')?>" class="text-bold"><?=number_format($totalSiblingDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_headteacher_discount')?>" class="text-bold"><?=number_format($totalHeadteacherDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_staff_discount')?>" class="text-bold"><?=number_format($totalStaffDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_director_discount')?>" class="text-bold"><?=number_format($totalDirectorDiscount,2)?></td>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  }
                  elseif ($reportType == "payment_report") {
                    if ($reportDetails == "student_detail") {?>
                      <table class="table table-striped table-bordered table-hover" style="width:100%">
    										<thead>
    											<tr>
                              <th><?=$this->lang->line('transactionsummary_student_detail')?></th>
                              <th><?=$this->lang->line('transactionsummary_class')?></th>
                              <th><?=$this->lang->line('transactionsummary_group')?></th>
                              <?php foreach($paymenttypes as $paymenttype) {?>
                                <th><?=$paymenttype->paymenttypes?></th>
                              <?php }?>
                              <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                          </tr>
    										</thead>
    										<tbody>
    											<?php
    												$total = [];
    												$totalTotal = 0;

    												foreach($students as $student) { ?>
    												<tr>
    													<td data-title="<?=$this->lang->line('transactionsummary_student_detail')?>">
    														<?=$student->srstudentID?> - <?=$student->srname?> - <?=$student->srclasses?> - <?=$student->group?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_class')?>">
    														<?=$student->srclasses?>
    													</td>
    													<td data-title="<?=$this->lang->line('transactionsummary_group')?>">
    														<?=$student->group?>
    													</td>
                              <?php foreach($paymenttypes as $paymenttype) {?>
                                <td data-title="<?=$paymenttype->paymenttypes?>">
      														<?php
      															$total[$paymenttype->paymenttypesID] += $totalStudentPaymentAmount[$student->srstudentID][$paymenttype->paymenttypesID];
      														?>
      														<?=isset($totalStudentPaymentAmount[$student->srstudentID][$paymenttype->paymenttypesID]) ? number_format($totalStudentPaymentAmount[$student->srstudentID][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
      													</td>
                              <?php }?>
    													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
    														<?php
    															$totalTotal += $totalStudentPaymentAmount[$student->srstudentID]['total'];
    														?>
    														<?=isset($totalStudentPaymentAmount[$student->srstudentID]['total']) ? number_format($totalStudentPaymentAmount[$student->srstudentID]['total'],2) : number_format(0, 2)?>
    													</td>
    												</tr>
    											<?php }?>
    										</tbody>
    										<tfoot>
    											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                          <?php foreach($paymenttypes as $paymenttype) {?>
                            <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                          <?php }?>
    											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
    										</tfoot>
    									</table>
                  <?php }
                  elseif ($reportDetails == "class_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_class_summary')?></th>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <th><?=$paymenttype->paymenttypes?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($classes as $class) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_class_summary')?>">
  														<?=$class->classes?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalClassPaymentAmount[$class->classesID][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalClassPaymentAmount[$class->classesID][$paymenttype->paymenttypesID]) ? number_format($totalClassPaymentAmount[$class->classesID][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalClassPaymentAmount[$class->classesID]['total'];
  														?>
  														<?=isset($totalClassPaymentAmount[$class->classesID]['total']) ? number_format($totalClassPaymentAmount[$class->classesID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "division_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_division_summary')?></th>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <th><?=$paymenttype->paymenttypes?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($divisions as $division) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_division_summary')?>">
  														<?=$division->divisions?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalDivisionPaymentAmount[$division->divisions][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalDivisionPaymentAmount[$division->divisions][$paymenttype->paymenttypesID]) ? number_format($totalDivisionPaymentAmount[$division->divisions][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalDivisionPaymentAmount[$division->divisions]['total'];
  														?>
  														<?=isset($totalDivisionPaymentAmount[$division->divisions]['total']) ? number_format($totalDivisionPaymentAmount[$division->divisions]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "date_detail") {?>
                    <table id="date_detail3" class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                          <th><?=$this->lang->line('transactionsummary_date_detail')?></th>
  												<th><?=$this->lang->line('transactionsummary_transaction_number')?></th>
  												<th><?=$this->lang->line('transactionsummary_student_detail')?></th>
                          <?php foreach($paymenttypes as $paymenttype) {?>
                            <th><?=$paymenttype->paymenttypes?></th>
                          <?php }?>
                          <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($payments as $payment) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_date_detail')?>">
  														<?=$payment->paymentdate?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_transaction_number')?>">
  														<?=$payment->globalpaymentID?>
  													</td>
  													<td data-title="<?=$this->lang->line('transactionsummary_student_detail')?>">
  														<?=$payment->srstudentID?> - <?=$payment->srname?> - <?=$payment->srclasses?> - <?=$payment->group?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalDatedetailPaymentAmount[$payment->paymentID][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalDatedetailPaymentAmount[$payment->paymentID][$paymenttype->paymenttypesID]) ? number_format($totalDatedetailPaymentAmount[$payment->paymentID][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
                            <td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalDatedetailPaymentAmount[$payment->paymentID]['total'];
  														?>
  														<?=isset($totalDatedetailPaymentAmount[$payment->paymentID]['total']) ? number_format($totalDatedetailPaymentAmount[$payment->paymentID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="3"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "date_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_date_summary')?></th>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <th><?=$paymenttype->paymenttypes?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($dates3 as $date) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_date_summary')?>">
  														<?=$date?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalDatePaymentAmount[$date][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalDatePaymentAmount[$date][$paymenttype->paymenttypesID]) ? number_format($totalDatePaymentAmount[$date][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalDatePaymentAmount[$date]['total'];
  														?>
  														<?=isset($totalDatePaymentAmount[$date]['total']) ? number_format($totalDatePaymentAmount[$date]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "month_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_month_summary')?></th>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <th><?=$paymenttype->paymenttypes?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($months3 as $month) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_month_summary')?>">
  														<?=date("M-y", strtotime($month))?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalMonthPaymentAmount[$month][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalMonthPaymentAmount[$month][$paymenttype->paymenttypesID]) ? number_format($totalMonthPaymentAmount[$month][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalMonthPaymentAmount[$month]['total'];
  														?>
  														<?=isset($totalMonthPaymentAmount[$month]['total']) ? number_format($totalMonthPaymentAmount[$month]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "term_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_term_summary')?></th>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <th><?=$paymenttype->paymenttypes?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($schoolterms as $schoolterm) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_term_summary')?>">
  														<?=$schoolterm->schooltermtitle?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalTermPaymentAmount[$schoolterm->schooltermID][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalTermPaymentAmount[$schoolterm->schooltermID][$paymenttype->paymenttypesID]) ? number_format($totalTermPaymentAmount[$schoolterm->schooltermID][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalTermPaymentAmount[$schoolterm->schooltermID]['total'];
  														?>
  														<?=isset($totalTermPaymentAmount[$schoolterm->schooltermID]['total']) ? number_format($totalTermPaymentAmount[$schoolterm->schooltermID]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                  elseif ($reportDetails == "year_summary") {?>
                    <table class="table table-striped table-bordered table-hover" style="width:100%;">
  										<thead>
  											<tr>
                            <th><?=$this->lang->line('transactionsummary_year_summary')?></th>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <th><?=$paymenttype->paymenttypes?></th>
                            <?php }?>
                            <th><?=$this->lang->line('transactionsummary_total_amount')?></th>
                        </tr>
  										</thead>
  										<tbody>
  											<?php
  												$total = [];
  												$totalTotal = 0;

  												foreach($years3 as $year) {?>
  												<tr>
  													<td data-title="<?=$this->lang->line('transactionsummary_year_summary')?>">
  														<?=$year?>
  													</td>
                            <?php foreach($paymenttypes as $paymenttype) {?>
                              <td data-title="<?=$paymenttype->paymenttypes?>">
                                <?php
                                  $total[$paymenttype->paymenttypesID] += $totalYearPaymentAmount[$year][$paymenttype->paymenttypesID];
                                ?>
                                <?=isset($totalYearPaymentAmount[$year][$paymenttype->paymenttypesID]) ? number_format($totalYearPaymentAmount[$year][$paymenttype->paymenttypesID],2) : number_format(0, 2)?>
                              </td>
                            <?php }?>
  													<td data-title="<?=$this->lang->line('transactionsummary_total_amount')?>">
  														<?php
  															$totalTotal += $totalYearPaymentAmount[$year]['total'];
  														?>
  														<?=isset($totalYearPaymentAmount[$year]['total']) ? number_format($totalYearPaymentAmount[$year]['total'],2) : number_format(0, 2)?>
  													</td>
  												</tr>
  											<?php }?>
  										</tbody>
  										<tfoot>
  											<td data-title="<?=$this->lang->line('transactionreport_grand_total')?>" class="text-right text-bold" colspan="<?=$colspan?>"><?=$this->lang->line('transactionreport_grand_total')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?> </td>
                        <?php foreach($paymenttypes as $paymenttype) {?>
                          <td data-title="<?=$paymenttype->paymenttypes?>" class="text-bold"><?=number_format($total[$paymenttype->paymenttypesID],2)?></td>
                        <?php }?>
  											<td data-title="<?=$this->lang->line('transactionsummary_total_amount_total')?>" class="text-bold"><?=number_format($totalTotal,2)?></td>
  										</tfoot>
  									</table>
                  <?php }
                }?>
                </div>
                <div class="col-sm-12 text-center footerAll">
                    <?=reportfooter($siteinfos, $schoolyearsessionobj)?>
                </div>
            </div><!-- row -->
        </div><!-- Body -->
    </div>
</div>

<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?=base_url('transactionreport/send_pdf_to_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=$this->lang->line('transactionreport_close')?></span></button>
                <h4 class="modal-title"><?=$this->lang->line('transactionreport_mail')?></h4>
            </div>
            <div class="modal-body">

                <?php
                    if(form_error('to'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="to" class="col-sm-2 control-label">
                        <?=$this->lang->line("transactionreport_to")?> <span class="text-red">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="email" class="form-control" id="to" name="to" value="<?=set_value('to')?>" >
                    </div>
                    <span class="col-sm-4 control-label" id="to_error">
                    </span>
                </div>

                <?php
                    if(form_error('subject'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="subject" class="col-sm-2 control-label">
                        <?=$this->lang->line("transactionreport_subject")?> <span class="text-red">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="subject" name="subject" value="<?=set_value('subject')?>" >
                    </div>
                    <span class="col-sm-4 control-label" id="subject_error">
                    </span>

                </div>

                <?php
                    if(form_error('message'))
                        echo "<div class='form-group has-error' >";
                    else
                        echo "<div class='form-group' >";
                ?>
                    <label for="message" class="col-sm-2 control-label">
                        <?=$this->lang->line("transactionreport_message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("transactionreport_send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>
<!-- email end here -->

<script type="text/javascript">
	function csvDiv(divID) {
  	//Get the HTML of div
  	var divElements = document.getElementById(divID).innerHTML;

  	//Reset the page's HTML with div's HTML only
  	var html =
  	  "<html><head><title></title></head><body>" +
  	  divElements + "</body>";
  	htmlToCSV(html, "statement.csv");
  }

  function htmlToCSV(html, filename) {
  	var data = [];
  	var rows = document.querySelectorAll('table tr');

  	for (var i = 0; i < rows.length; i++) {
  		var row = [], cols = rows[i].querySelectorAll("td, th");

  		for (var j = 0; j < cols.length; j++) {
  				row.push("\""+cols[j].innerText+"\"");
          }

  		data.push(row.join(","));
  	}

  	downloadCSVFile(data.join("\n"), filename);
  }

  function downloadCSVFile(csv, filename) {
  	var csv_file, download_link;

  	csv_file = new Blob([csv], {type: "text/csv"});

  	download_link = document.createElement("a");

  	download_link.download = filename;

  	download_link.href = window.URL.createObjectURL(csv_file);

  	download_link.style.display = "none";

  	document.body.appendChild(download_link);

  	download_link.click();
  }

  function check_email(email) {
      var status = false;
      var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
      if (email.search(emailRegEx) == -1) {
          $("#to_error").html('');
          $("#to_error").html("<?=$this->lang->line('transactionreport_mail_valid')?>").css("text-align", "left").css("color", 'red');
      } else {
          status = true;
      }
      return status;
  }

    gherf = '1';
    $(document).on('click','.transction-tab', function() {
        var href = $(this).attr('href');

        if(href == "#fees_collection_details") {
            var querydata = 1;
        } else if(href == "#income_details") {
            var querydata = 2;
        } else if(href == "#expense_details") {
            var querydata = 3;
        } else if(href == "#salary_details") {
            var querydata = 4;
        }
        gherf = querydata;

        var pdfUrlGenarete = "<?=base_url('/transactionreport/pdf')?>"+"/"+fromdate+"/"+todate+"/"+querydata;
        var xmlUrlGenarete = "<?=base_url('/transactionreport/xlsx')?>"+"/"+fromdate+"/"+todate+"/"+querydata;

        $('.pdfurl').attr('href', pdfUrlGenarete);
        $('.xmlurl').attr('href', xmlUrlGenarete);

    });

    $('#send_pdf').click(function() {
        var field = {
            'to'         : $('#to').val(),
            'subject'    : $('#subject').val(),
            'message'    : $('#message').val(),
            'fromdate'   : "<?=strtotime($fromdate)?>",
            'todate'     : "<?=strtotime($todate)?>",
            'querydata'  : gherf,
        };

        var to = $('#to').val();
        var subject = $('#subject').val();
        var error = 0;

        $("#to_error").html("");
        $("#subject_error").html("");

        if(to == "" || to == null) {
            error++;
            $("#to_error").html("<?=$this->lang->line('transactionreport_mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        }

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("<?=$this->lang->line('transactionreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('transactionreport/send_pdf_to_mail')?>",
                data: field,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if(response.status == false) {
                        $('#send_pdf').removeAttr('disabled');
                        $.each(response, function(index, value) {
                            if(index != 'status') {
                                toastr["error"](value)
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
                            }
                        });
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });

    $('table').DataTable({
      "paging": false,
      "scrollX": true
    });
</script>
