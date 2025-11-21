<div class="box">
    <div class="box-header">
        <h3 class="box-title"><?=$this->lang->line('menuoverrides_panel_title');?></h3>
        <div class="pull-right box-tools">
            <a href="<?=base_url('menuoverrides/add');?>" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> <?=$this->lang->line('menuoverrides_add_button');?>
            </a>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted">
            <?=$this->lang->line('menuoverrides_overview');?>
        </p>
        <div class="alert alert-info">
            <?=$this->lang->line('menuoverrides_cache_hint');?>
        </div>
        <div class="alert alert-warning">
            <?=sprintf(
                $this->lang->line('menuoverrides_translation_hint'),
                '<code>mvc/language/'.$this->session->userdata('lang').'/topbar_menu_lang.php</code>'
            );?>
        </div>
        <?php
            $hasCustomSummary = isset($grouped_overrides['custom_nodes']) && customCompute($grouped_overrides['custom_nodes']);
            $hasRelocationSummary = isset($grouped_overrides['relocations']) && customCompute($grouped_overrides['relocations']);
            $CI = &get_instance();

            $renderCreateIfMissing = function($payload) use ($CI) {
                if ($payload === null) {
                    return '';
                }

                $html = '<div class="text-muted small"><strong>'.$CI->lang->line('menuoverrides_summary_create_if_missing').':</strong> ';

                if ($payload === true) {
                    $html .= $CI->lang->line('menuoverrides_yes');
                } elseif ($payload === false) {
                    $html .= $CI->lang->line('menuoverrides_no');
                } elseif (is_array($payload)) {
                    $html .= '<span class="label label-success">'.$CI->lang->line('menuoverrides_yes').'</span>';
                    $details = [];

                    if (isset($payload['icon']) && $payload['icon'] !== '') {
                        $details[] = $CI->lang->line('menuoverrides_summary_create_if_missing_icon').' <code>'.htmlentities($payload['icon'], ENT_QUOTES, 'UTF-8').'</code>';
                    }

                    if (isset($payload['priority'])) {
                        $details[] = $CI->lang->line('menuoverrides_summary_create_if_missing_priority').' <span class="label label-default">'.htmlentities($payload['priority'], ENT_QUOTES, 'UTF-8').'</span>';
                    }

                    if (isset($payload['status'])) {
                        $statusText = (int) $payload['status'] === 1
                            ? $CI->lang->line('menuoverrides_summary_create_if_missing_status_enabled')
                            : $CI->lang->line('menuoverrides_summary_create_if_missing_status_disabled');
                        $details[] = $CI->lang->line('menuoverrides_summary_create_if_missing_status').' '.$statusText;
                    }

                    if (!empty($details)) {
                        $html .= '<ul class="list-inline menuoverride-summary-placeholder"><li>'.implode('</li><li>', $details).'</li></ul>';
                    }
                } else {
                    $html .= '<code>'.htmlentities(is_string($payload) ? $payload : json_encode($payload), ENT_QUOTES, 'UTF-8').'</code>';
                }

                $html .= '</div>';
                return $html;
            };
        ?>
        <?php if($hasCustomSummary || $hasRelocationSummary): ?>
            <style>
                .menuoverride-summary-list > li { margin-bottom: 12px; }
                .menuoverride-summary-placeholder { margin: 6px 0 0; padding-left: 0; }
                .menuoverride-summary-placeholder > li { margin-right: 12px; }
            </style>
            <div class="panel panel-default menuoverride-summary">
                <div class="panel-heading">
                    <i class="fa fa-sitemap"></i> <?=$this->lang->line('menuoverrides_summary_heading');?>
                </div>
                <div class="panel-body">
                    <?php if($hasCustomSummary): ?>
                        <h4><?=$this->lang->line('menuoverrides_summary_custom');?></h4>
                        <ul class="list-unstyled menuoverride-summary-list">
                            <?php foreach($grouped_overrides['custom_nodes'] as $node): ?>
                                <li>
                                    <strong><code><?=htmlentities($node['menuName'], ENT_QUOTES, 'UTF-8');?></code></strong>
                                    <span class="label label-default"><?=$this->lang->line('menuoverrides_summary_priority_label');?> <?=htmlentities($node['priority'], ENT_QUOTES, 'UTF-8');?></span>
                                    <?php $statusLabel = (isset($node['status']) && (int) $node['status'] === 1) ? 'success' : 'default'; ?>
                                    <span class="label label-<?=$statusLabel;?>"><?=(isset($node['status']) && (int) $node['status'] === 1)
                                        ? $this->lang->line('menuoverrides_summary_status_enabled')
                                        : $this->lang->line('menuoverrides_summary_status_disabled');
                                    ?></span>
                                    <?php if(isset($node['parent'])): ?>
                                        <div class="text-muted small"><?=$this->lang->line('menuoverrides_summary_parent_label');?>: <code><?=htmlentities($node['parent'], ENT_QUOTES, 'UTF-8');?></code></div>
                                    <?php endif; ?>
                                    <?php if(isset($node['link'])): ?>
                                        <div class="text-muted small"><?=$this->lang->line('menuoverrides_summary_link_label');?>: <code><?=htmlentities($node['link'], ENT_QUOTES, 'UTF-8');?></code></div>
                                    <?php endif; ?>
                                    <?php if(isset($node['icon'])): ?>
                                        <div class="text-muted small"><?=$this->lang->line('menuoverrides_summary_icon_label');?>: <i class="fa <?=htmlentities($node['icon'], ENT_QUOTES, 'UTF-8');?>"></i> <code><?=htmlentities($node['icon'], ENT_QUOTES, 'UTF-8');?></code></div>
                                    <?php endif; ?>
                                    <?php if(!empty($node['skip_permission'])): ?>
                                        <div class="text-muted small"><span class="label label-info"><?=$this->lang->line('menuoverrides_summary_skip_permission');?></span></div>
                                    <?php endif; ?>
                                    <?= $renderCreateIfMissing(isset($node['create_if_missing']) ? $node['create_if_missing'] : null); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if($hasRelocationSummary): ?>
                        <h4><?=$this->lang->line('menuoverrides_summary_relocations');?></h4>
                        <ul class="list-unstyled menuoverride-summary-list">
                            <?php foreach($grouped_overrides['relocations'] as $relocation): ?>
                                <li>
                                    <strong><code><?=htmlentities($relocation['menuName'], ENT_QUOTES, 'UTF-8');?></code></strong>
                                    <?php if(isset($relocation['parent'])): ?>
                                        <span class="fa fa-long-arrow-right text-muted"></span> <code><?=htmlentities($relocation['parent'], ENT_QUOTES, 'UTF-8');?></code>
                                    <?php endif; ?>
                                    <?php if(isset($relocation['priority'])): ?>
                                        <span class="label label-default"><?=$this->lang->line('menuoverrides_summary_priority_label');?> <?=htmlentities($relocation['priority'], ENT_QUOTES, 'UTF-8');?></span>
                                    <?php endif; ?>
                                    <?php $relocationStatus = (isset($relocation['status']) && (int) $relocation['status'] === 1) ? 'success' : 'default'; ?>
                                    <span class="label label-<?=$relocationStatus;?>"><?=(isset($relocation['status']) && (int) $relocation['status'] === 1)
                                        ? $this->lang->line('menuoverrides_summary_status_enabled')
                                        : $this->lang->line('menuoverrides_summary_status_disabled');
                                    ?></span>
                                    <?= $renderCreateIfMissing(isset($relocation['create_if_missing']) ? $relocation['create_if_missing'] : null); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><?=$this->lang->line('menuoverrides_table_type');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_menu_key');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_translation');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_parent');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_link');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_icon');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_priority');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_status');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_skip_permission');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_create_if_missing');?></th>
                        <th><?=$this->lang->line('menuoverrides_table_notes');?></th>
                        <th><?=$this->lang->line('menuoverrides_actions');?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(customCompute($overrides)) { foreach($overrides as $override) { ?>
                    <?php
                        $translationKey = 'menu_' . $override->menuName;
                        $translation = $this->lang->line($translationKey);
                        if($translation === false || $translation === '') {
                            $translation = '<span class="text-warning">'.$this->lang->line('menuoverrides_translation_missing').'</span>';
                        } else {
                            $translation = htmlentities($translation);
                        }
                        $createIfMissing = $this->menu_override_m->decode_create_if_missing($override->create_if_missing);
                        if(is_array($createIfMissing)) {
                            $createIfMissingDisplay = '<code>'.htmlentities(json_encode($createIfMissing)).'</code>';
                        } elseif($createIfMissing === true) {
                            $createIfMissingDisplay = $this->lang->line('menuoverrides_yes');
                        } elseif($createIfMissing === false || $createIfMissing === null) {
                            $createIfMissingDisplay = $this->lang->line('menuoverrides_no');
                        } else {
                            $createIfMissingDisplay = '<code>'.htmlentities($createIfMissing).'</code>';
                        }
                        $statusLabel = (int)$override->status === 1 ? $this->lang->line('menuoverrides_enabled') : $this->lang->line('menuoverrides_disabled');
                    ?>
                    <tr>
                        <td><span class="label label-default">
                            <?=$override->override_type === 'relocation' ? $this->lang->line('menuoverrides_type_relocation') : $this->lang->line('menuoverrides_type_custom');?>
                        </span></td>
                        <td><code><?=$override->menuName;?></code></td>
                        <td><?=$translation;?></td>
                        <td><?=$override->parent ? '<code>'.$override->parent.'</code>' : '-';?></td>
                        <td><?=$override->link ? anchor($override->link, $override->link) : '-';?></td>
                        <td><?=$override->icon ? '<i class="fa '.$override->icon.'"></i> '.$override->icon : '-';?></td>
                        <td><?=$override->priority;?></td>
                        <td><?=$statusLabel;?></td>
                        <td><?=$override->skip_permission ? '<span class="label label-info">'.$this->lang->line('menuoverrides_yes').'</span>' : $this->lang->line('menuoverrides_no');?></td>
                        <td><?=$createIfMissingDisplay;?></td>
                        <td><?=!empty($override->notes) ? nl2br(htmlentities($override->notes)) : '-';?></td>
                        <td>
                            <a href="<?=base_url('menuoverrides/edit/'.$override->menuOverrideID);?>" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> <?=$this->lang->line('menuoverrides_edit');?></a>
                            <a href="<?=base_url('menuoverrides/delete/'.$override->menuOverrideID);?>" class="btn btn-danger btn-xs" onclick="return confirm('<?=$this->lang->line('menuoverrides_delete_confirm');?>');"><i class="fa fa-trash"></i> <?=$this->lang->line('menuoverrides_delete');?></a>
                        </td>
                    </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="12" class="text-center text-muted">
                            <?=$this->lang->line('menuoverrides_empty');?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
