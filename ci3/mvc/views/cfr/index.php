<?php
$title = lang('menu_cfr');
if ($title === 'menu_cfr') {
    $title = 'CFR';
}

$range = isset($range) ? $range : '30';
$summary = isset($summary) ? $summary : [];
$scorecard = isset($scorecard) ? $scorecard : [];
$recentActivity = isset($recentActivity) ? $recentActivity : [];
$topContributors = isset($topContributors) ? $topContributors : [];
$lastUpdatedAt = isset($lastUpdatedAt) ? $lastUpdatedAt : '';
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-comments"></i> <?= html_escape($title); ?></h3>
        <?php if ($lastUpdatedAt): ?>
            <span class="label label-default pull-right"><i class="fa fa-clock-o"></i> <?= html_escape(lang('cfr_updated_at')); ?>: <?= html_escape(date('M d, Y H:i', strtotime($lastUpdatedAt))); ?></span>
        <?php endif; ?>
    </div>
    <div class="box-body">
        <form method="get" class="form-inline m-b-20">
            <div class="form-group">
                <label for="range" class="control-label m-r-10"><?= html_escape(lang('cfr_filter_range')); ?></label>
                <select name="range" id="range" class="form-control">
                    <?php
                        $ranges = [
                            '7' => sprintf(lang('cfr_range_days'), 7),
                            '30' => sprintf(lang('cfr_range_days'), 30),
                            '90' => sprintf(lang('cfr_range_days'), 90),
                            '365' => sprintf(lang('cfr_range_days'), 365),
                            'all' => lang('cfr_range_all'),
                        ];
                    ?>
                    <?php foreach ($ranges as $value => $label): ?>
                        <option value="<?= html_escape($value); ?>"<?= $range === $value ? ' selected' : ''; ?>>
                            <?= html_escape($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary m-l-10"><?= html_escape(lang('cfr_apply_filters')); ?></button>
        </form>

        <div class="row m-b-20">
            <div class="col-sm-4">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= html_escape(lang('cfr_alignment_score')); ?></span>
                        <span class="info-box-number"><?= isset($scorecard['alignment_index']) ? sprintf('%.2f', $scorecard['alignment_index']) : '0.00'; ?>%</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= min(100, max(0, (float) ($scorecard['alignment_index'] ?? 0))); ?>%"></div>
                        </div>
                        <span class="progress-description"><?= html_escape(lang('cfr_okr_progress')); ?>: <?= isset($scorecard['okr_progress']) ? sprintf('%.2f', $scorecard['okr_progress']) : '0.00'; ?>%</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-heart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= html_escape(lang('cfr_engagement_score')); ?></span>
                        <span class="info-box-number"><?= isset($scorecard['cfr_engagement']) ? sprintf('%.2f', $scorecard['cfr_engagement']) : '0.00'; ?>%</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= min(100, max(0, (float) ($scorecard['cfr_engagement'] ?? 0))); ?>%"></div>
                        </div>
                        <span class="progress-description"><?= html_escape(lang('cfr_summary_heading')); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?= html_escape(lang('cfr_recognition_rate')); ?></span>
                        <span class="info-box-number"><?= isset($summary['recognitionRate']) ? sprintf('%.2f', $summary['recognitionRate']) : '0.00'; ?>%</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= min(100, max(0, (float) ($summary['recognitionRate'] ?? 0))); ?>%"></div>
                        </div>
                        <span class="progress-description"><?= html_escape(lang('cfr_feedback_rate')); ?>: <?= isset($summary['feedbackRate']) ? sprintf('%.2f', $summary['feedbackRate']) : '0.00'; ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-b-20">
            <div class="col-sm-3">
                <div class="small-box bg-light-blue">
                    <div class="inner">
                        <h3><?= isset($summary['threads']) ? (int) $summary['threads'] : 0; ?></h3>
                        <p><?= html_escape(lang('cfr_threads_total')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-comments-o"></i></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3><?= isset($summary['messages']) ? (int) $summary['messages'] : 0; ?></h3>
                        <p><?= html_escape(lang('cfr_messages_total')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-envelope"></i></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?= isset($summary['recognition']) ? (int) $summary['recognition'] : 0; ?></h3>
                        <p><?= html_escape(lang('cfr_recognitions_total')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-trophy"></i></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3><?= isset($summary['feedback']) ? (int) $summary['feedback'] : 0; ?></h3>
                        <p><?= html_escape(lang('cfr_feedback_total')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-commenting"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-users"></i> <?= html_escape(lang('cfr_top_contributors')); ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($topContributors)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($topContributors as $contributor): ?>
                                    <li class="m-b-5">
                                        <strong><?= html_escape($contributor['name']); ?></strong>
                                        <span class="text-muted">&middot; <?= (int) $contributor['messages']; ?> <?= html_escape(lang('cfr_messages_total')); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted"><?= html_escape(lang('cfr_activity_empty')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-balance-scale"></i> <?= html_escape(lang('cfr_summary_heading')); ?></h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <th><?= html_escape(lang('cfr_conversations_total')); ?></th>
                                    <td><?= isset($summary['conversation']) ? (int) $summary['conversation'] : 0; ?></td>
                                </tr>
                                <tr>
                                    <th><?= html_escape(lang('cfr_sentiment_positive')); ?></th>
                                    <td><?= isset($summary['sentimentPositive']) ? sprintf('%.2f%%', $summary['sentimentPositive']) : '0.00%'; ?></td>
                                </tr>
                                <tr>
                                    <th><?= html_escape(lang('cfr_sentiment_neutral')); ?></th>
                                    <td><?= isset($summary['sentimentNeutral']) ? sprintf('%.2f%%', $summary['sentimentNeutral']) : '0.00%'; ?></td>
                                </tr>
                                <tr>
                                    <th><?= html_escape(lang('cfr_sentiment_negative')); ?></th>
                                    <td><?= isset($summary['sentimentNegative']) ? sprintf('%.2f%%', $summary['sentimentNegative']) : '0.00%'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-history"></i> <?= html_escape(lang('cfr_recent_activity')); ?></h3>
            </div>
            <div class="panel-body">
                <?php if (!empty($recentActivity)): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($recentActivity as $activity): ?>
                            <li class="m-b-15">
                                <div class="clearfix">
                                    <strong><?= html_escape($activity['subject'] ?: lang('cfr_type_' . $activity['type'])); ?></strong>
                                    <span class="pull-right text-muted"><i class="fa fa-calendar"></i> <?= html_escape(date('M d, Y H:i', strtotime($activity['created_at']))); ?></span>
                                </div>
                                <div class="text-muted">
                                    <span class="label label-info text-uppercase"><?= html_escape(lang('cfr_type_' . $activity['type'])); ?></span>
                                    <span class="label label-default text-uppercase"><?= html_escape(lang('cfr_sentiment_' . $activity['sentiment'])); ?></span>
                                    <span class="m-l-5"><i class="fa fa-user"></i> <?= html_escape($activity['author']); ?></span>
                                </div>
                                <?php if (!empty($activity['excerpt'])): ?>
                                    <p class="m-t-5"><?= nl2br(html_escape($activity['excerpt'])); ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted"><?= html_escape(lang('cfr_activity_empty')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
