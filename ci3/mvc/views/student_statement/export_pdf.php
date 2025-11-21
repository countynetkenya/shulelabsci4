<section class="content invoice">
    <?php $payload = isset($statementPayload) ? $statementPayload : ['students' => []]; ?>
    <?php if(customCompute($payload['students'])): ?>
        <?php foreach($payload['students'] as $student): ?>
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="page-header">
                        <?php if(isset($siteinfos->photo) && $siteinfos->photo): ?>
                            <?php echo img(['src' => base_url('uploads/images/'.$siteinfos->photo), 'width' => '25px', 'height' => '25px', 'class' => 'img-circle', 'style' => 'margin-top:-10px']); ?>
                        <?php endif; ?>
                        <?=$siteinfos->sname?>
                        <small class="pull-right"><?=$this->lang->line('global_date')?>: <?=date('d M Y')?></small>
                    </h2>
                </div>
            </div>
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong><?=$siteinfos->sname?></strong><br>
                        <?=$siteinfos->address?><br>
                        <?=$this->lang->line('global_phone_number')?>: <?=$siteinfos->phone?><br>
                        <?=$this->lang->line('global_email')?>: <?=$siteinfos->email?><br>
                    </address>
                </div>
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong><?=$student['student']['student_name']?></strong><br>
                        <?=$this->lang->line('global_register_no')?>: <?=$student['student']['studentID']?><br>
                        <?=$this->lang->line('global_classes')?>: <?=$student['student']['class']?><br>
                        <?=$this->lang->line('global_section')?>: <?=$student['student']['section']?><br>
                    </address>
                </div>
                <div class="col-sm-4 invoice-col text-right">
                    <p><?=$this->lang->line('global_debit')?>: <?=number_format($student['student']['total_debit'], 2)?></p>
                    <p><?=$this->lang->line('global_credit')?>: <?=number_format($student['student']['total_credit'], 2)?></p>
                    <p><strong><?=$this->lang->line('global_balance')?>: <?=number_format($student['student']['closing_balance'], 2)?></strong></p>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?=$this->lang->line('global_date')?></th>
                                <th><?=$this->lang->line('global_description')?></th>
                                <th class="text-right"><?=$this->lang->line('global_debit')?></th>
                                <th class="text-right"><?=$this->lang->line('global_credit')?></th>
                                <th class="text-right"><?=$this->lang->line('global_balance')?></th>
                                <th><?=$this->lang->line('global_schooltermID')?></th>
                                <th><?=$this->lang->line('global_month')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($student['rows'] as $row): ?>
                                <tr>
                                    <td><?=$row['day']?></td>
                                    <td><?=$row['description']?></td>
                                    <td class="text-right"><?=(float)$row['debit'] ? number_format($row['debit'], 2) : ''?></td>
                                    <td class="text-right"><?=(float)$row['credit'] ? number_format($row['credit'], 2) : ''?></td>
                                    <td class="text-right"><?=number_format($row['balance'], 2)?></td>
                                    <td><?=$row['term']?></td>
                                    <td><?=$row['month']?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="page-break-after:always;"></div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><?=$this->lang->line('global_not_found')?></p>
    <?php endif; ?>
</section>
