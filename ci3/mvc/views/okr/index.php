<div class="row">
    <div class="col-sm-6">
        <h3><?=$this->lang->line('panel_title');?></h3>
    </div>
    <div class="col-sm-6 text-right">
        <?php if(!empty($canManage)) { ?>
            <a href="<?=base_url('okr/create');?>" class="btn btn-success"><i class="fa fa-plus"></i> <?=$this->lang->line('okr_add');?></a>
        <?php } ?>
    </div>
</div>

<div class="row m-b-20">
    <div class="col-sm-3">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?=isset($summary['total_objectives']) ? $summary['total_objectives'] : 0;?></h3>
                <p><?=$this->lang->line('okr_summary_total');?></p>
            </div>
            <div class="icon"><i class="fa fa-bullseye"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?=isset($summary['active_objectives']) ? $summary['active_objectives'] : 0;?></h3>
                <p><?=$this->lang->line('okr_summary_active');?></p>
            </div>
            <div class="icon"><i class="fa fa-play"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?=isset($summary['completed_objectives']) ? $summary['completed_objectives'] : 0;?></h3>
                <p><?=$this->lang->line('okr_summary_completed');?></p>
            </div>
            <div class="icon"><i class="fa fa-check"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-light-blue">
            <div class="inner">
                <h3><?=isset($summary['average_progress']) ? $summary['average_progress'] : 0;?>%</h3>
                <p><?=$this->lang->line('okr_summary_average');?></p>
            </div>
            <div class="icon"><i class="fa fa-line-chart"></i></div>
        </div>
    </div>
</div>

<?php if(!empty($summary['last_activity_at'])) { ?>
    <div class="alert alert-info">
        <i class="fa fa-clock-o"></i> <?=$this->lang->line('okr_summary_last_activity');?>: <?=date('M d, Y H:i', strtotime($summary['last_activity_at']));?>
    </div>
<?php } ?>

<?php if(customCompute($objectives)) { ?>
    <?php foreach($objectives as $objective) { ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?=htmlspecialchars($objective->title);?></h3>
                <div class="box-tools pull-right">
                    <span class="label label-default text-uppercase"><?=htmlspecialchars($objective->ownerType);?></span>
                    <span class="label label-info"><?=sprintf('%.2f%%', $objective->progress_cached);?></span>
                    <?php if(!empty($canManage)) { ?>
                        <a href="<?=base_url('okr/recompute/'.$objective->okrObjectiveID);?>" class="btn btn-xs btn-default" title="<?=$this->lang->line('okr_progress_recalculate');?>"><i class="fa fa-refresh"></i></a>
                    <?php } ?>
                </div>
            </div>
            <div class="box-body">
                <?php if(!empty($objective->description)) { ?>
                    <p><?=nl2br(htmlspecialchars($objective->description));?></p>
                <?php } ?>
                <div class="row">
                    <div class="col-sm-4">
                        <strong><?=$this->lang->line('okr_start_date');?>:</strong> <?=($objective->start_date) ? date('M d, Y', strtotime($objective->start_date)) : '--';?>
                    </div>
                    <div class="col-sm-4">
                        <strong><?=$this->lang->line('okr_end_date');?>:</strong> <?=($objective->end_date) ? date('M d, Y', strtotime($objective->end_date)) : '--';?>
                    </div>
                    <div class="col-sm-4">
                        <strong><?=$this->lang->line('okr_status');?>:</strong> <?=htmlspecialchars($objective->status);?>
                    </div>
                </div>

                <div class="table-responsive m-t-15">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?=$this->lang->line('okr_key_result_title');?></th>
                                <th><?=$this->lang->line('okr_key_result_source');?></th>
                                <th><?=$this->lang->line('okr_key_result_target');?></th>
                                <th><?=$this->lang->line('okr_key_result_current');?></th>
                                <th><?=$this->lang->line('okr_key_result_weight');?></th>
                                <th><?=$this->lang->line('okr_key_result_progress');?></th>
                                <?php if(!empty($canManage)) { ?><th></th><?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($keyResults[$objective->okrObjectiveID])) { ?>
                                <?php foreach($keyResults[$objective->okrObjectiveID] as $keyResult) { ?>
                                    <tr>
                                        <td>
                                            <strong><?=htmlspecialchars($keyResult->title);?></strong><br>
                                            <?php if(!empty($keyResult->description)) { ?>
                                                <small><?=nl2br(htmlspecialchars($keyResult->description));?></small>
                                            <?php } ?>
                                        </td>
                                        <td><?=htmlspecialchars($this->lang->line('okr_key_result_source_'.strtolower($keyResult->data_source)) ?: ucfirst($keyResult->data_source));?></td>
                                        <td><?=sprintf('%.2f', $keyResult->target_value);?> <?=htmlspecialchars($keyResult->unit);?></td>
                                        <td><?=sprintf('%.2f', $keyResult->current_value);?> <?=htmlspecialchars($keyResult->unit);?></td>
                                        <td><?=sprintf('%.2f', $keyResult->weight);?></td>
                                        <td><span class="label label-primary"><?=sprintf('%.2f%%', $keyResult->progress_cached);?></span></td>
                                        <?php if(!empty($canManage)) { ?>
                                            <td><a href="<?=base_url('okr/recompute_key_result/'.$keyResult->okrKeyResultID);?>" class="btn btn-xs btn-default" title="<?=$this->lang->line('okr_progress_recalculate');?>"><i class="fa fa-refresh"></i></a></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="<?=!empty($canManage) ? 7 : 6;?>" class="text-center text-muted">--</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> <?=$this->lang->line('okr_no_objectives');?>
    </div>
<?php } ?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> <?=$this->lang->line('okr_logs');?></h3>
    </div>
    <div class="box-body">
        <?php if(customCompute($logs)) { ?>
            <ul class="list-unstyled">
                <?php foreach($logs as $log) { ?>
                    <li class="m-b-10">
                        <strong><?=date('M d, Y H:i', strtotime($log->created_at));?></strong>
                        - <?=htmlspecialchars($log->entry_type);
?>
                        <?php if(!empty($log->message)) { ?>
                            <div><?=htmlspecialchars($log->message);?></div>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p class="text-muted">--</p>
        <?php } ?>
    </div>
</div>
