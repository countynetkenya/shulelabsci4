<div class="box">
    <div class="box-header">
        <h3 class="box-title">
            <?=(isset($form_mode) && $form_mode === 'edit') ? $this->lang->line('menuoverrides_edit_title') : $this->lang->line('menuoverrides_add_title');?>
        </h3>
        <div class="pull-right box-tools">
            <a href="<?=base_url('menuoverrides/index');?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?=$this->lang->line('menuoverrides_back_to_list');?></a>
        </div>
    </div>
    <div class="box-body">
        <?php
        $translationKey = set_value('menuName', isset($override['menuName']) ? $override['menuName'] : '');
        $translationPreview = '';
        if ($translationKey) {
            $fullKey = 'menu_' . $translationKey;
            $line = $this->lang->line($fullKey);
            if ($line === false || $line === '') {
                $translationPreview = '<span class="text-warning">' . $this->lang->line('menuoverrides_translation_missing') . '</span>';
            } else {
                $translationPreview = htmlentities($line, ENT_QUOTES, 'UTF-8');
            }
        }

        $menuNameOptions = isset($menu_name_options) && is_array($menu_name_options) ? $menu_name_options : [];
        $menuNameTranslations = isset($menu_name_translations) && is_array($menu_name_translations) ? $menu_name_translations : [];
        $parentOptions = isset($parent_options) && is_array($parent_options) ? $parent_options : [];

        $selectedOverrideType = set_value('override_type', isset($override['override_type']) ? $override['override_type'] : 'custom');
        $isRelocationType = $selectedOverrideType === 'relocation';

        $currentMenuName = set_value('menuName', isset($override['menuName']) ? $override['menuName'] : '');
        if ($currentMenuName !== '' && !isset($menuNameOptions[$currentMenuName])) {
            $menuNameOptions = array_merge([
                $currentMenuName => $currentMenuName . ' [' . $this->lang->line('menuoverrides_option_label_custom') . ']'
            ], $menuNameOptions);
        }

        $parentOptions = array_merge(['' => $this->lang->line('menuoverrides_parent_root')], $parentOptions);
        $currentParent = set_value('parent', isset($override['parent']) ? $override['parent'] : '');
        if ($currentParent !== '' && !isset($parentOptions[$currentParent])) {
            $parentOptions[$currentParent] = $currentParent . ' [' . $this->lang->line('menuoverrides_option_label_custom') . ']';
        }

        $menuTranslationsJson = htmlspecialchars(json_encode($menuNameTranslations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
        $menuTranslationMissing = htmlspecialchars($this->lang->line('menuoverrides_translation_missing'), ENT_QUOTES, 'UTF-8');
        $menuTranslationLabel = htmlspecialchars($this->lang->line('menuoverrides_translation_preview'), ENT_QUOTES, 'UTF-8');
        ?>
        <?=form_open('', ['class' => 'form-horizontal']);?>
            <div class="form-group <?=form_error('override_type') ? 'has-error' : '';?>">
                <label for="override_type" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_override_type');?>
                </label>
                <div class="col-sm-6">
                    <?php
                        echo form_dropdown(
                            'override_type',
                            [
                                'custom' => $this->lang->line('menuoverrides_type_custom'),
                                'relocation' => $this->lang->line('menuoverrides_type_relocation'),
                            ],
                            set_value('override_type', isset($override['override_type']) ? $override['override_type'] : 'custom'),
                            'class="form-control js-select2" id="override_type"'
                        );
                    ?>
                    <span class="help-block"><?=$this->lang->line('menuoverrides_override_type_help');?></span>
                    <?=form_error('override_type');?>
                </div>
            </div>

            <div class="form-group js-relocation-hint <?=$isRelocationType ? '' : 'hidden';?>">
                <div class="col-sm-offset-3 col-sm-6">
                    <div class="alert alert-info">
                        <?=$this->lang->line('menuoverrides_relocation_hint');?>
                    </div>
                </div>
            </div>

            <div class="form-group <?=form_error('menuName') ? 'has-error' : '';?>">
                <label for="menuName" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_menu_name');?>
                </label>
                <div class="col-sm-6">
                    <?php
                        echo form_dropdown(
                            'menuName',
                            $menuNameOptions,
                            $currentMenuName,
                            'class="form-control js-select2" id="menuName" data-allow-new="1" data-placeholder="' . htmlspecialchars($this->lang->line('menuoverrides_menu_name_placeholder'), ENT_QUOTES, 'UTF-8') . '" data-menu-translations="' . $menuTranslationsJson . '" data-translation-missing="' . $menuTranslationMissing . '" data-translation-label="' . $menuTranslationLabel . '" data-translation-target="#menu-translation-preview"'
                        );
                    ?>
                    <span class="help-block">
                        <?=sprintf($this->lang->line('menuoverrides_menu_name_help'), '<code>menu_'.htmlentities(set_value('menuName', isset($override['menuName']) ? $override['menuName'] : ''), ENT_QUOTES, 'UTF-8').'</code>');?>
                    </span>
                    <p class="help-block <?=($translationPreview === '') ? 'hidden' : '';?>" id="menu-translation-preview">
                        <?php if($translationPreview !== ''): ?>
                            <?=$this->lang->line('menuoverrides_translation_preview');?>: <?=$translationPreview;?>
                        <?php endif; ?>
                    </p>
                    <?=form_error('menuName');?>
                </div>
            </div>

            <div class="form-group <?=form_error('parent') ? 'has-error' : '';?>">
                <label for="parent" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_parent');?>
                </label>
                <div class="col-sm-6">
                    <?php
                        echo form_dropdown(
                            'parent',
                            $parentOptions,
                            $currentParent,
                            'class="form-control js-select2" id="parent" data-allow-new="1" data-allow-clear="1" data-placeholder="' . htmlspecialchars($this->lang->line('menuoverrides_parent_placeholder'), ENT_QUOTES, 'UTF-8') . '"'
                        );
                    ?>
                    <span class="help-block"><?=$this->lang->line('menuoverrides_parent_help');?></span>
                    <?=form_error('parent');?>
                </div>
            </div>

            <div class="form-group js-custom-only <?=($isRelocationType ? 'hidden' : '');?> <?=form_error('link') ? 'has-error' : '';?>">
                <label for="link" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_link');?>
                </label>
                <div class="col-sm-6">
                    <input type="text" name="link" id="link" class="form-control" value="<?=set_value('link', isset($override['link']) ? $override['link'] : '');?>" <?=($isRelocationType ? 'disabled' : '');?> />
                    <span class="help-block"><?=$this->lang->line('menuoverrides_link_help');?></span>
                    <?=form_error('link');?>
                </div>
            </div>

            <div class="form-group js-custom-only <?=($isRelocationType ? 'hidden' : '');?> <?=form_error('icon') ? 'has-error' : '';?>">
                <label for="icon" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_icon');?>
                </label>
                <div class="col-sm-6">
                    <?php $iconValue = set_value('icon', isset($override['icon']) ? $override['icon'] : ''); ?>
                    <div class="input-group iconpicker-wrapper">
                        <span class="input-group-addon iconpicker-preview"><i class="fa<?=($iconValue ? ' ' . htmlentities($iconValue, ENT_QUOTES, 'UTF-8') : '');?>" id="icon_preview"></i></span>
                        <input type="text" name="icon" id="icon" class="form-control" value="<?=htmlentities($iconValue, ENT_QUOTES, 'UTF-8');?>" <?=($isRelocationType ? 'disabled' : '');?> />
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default js-iconpicker-toggle" data-target="#icon" data-preview="#icon_preview" data-icon-source="<?=base_url('assets/iconpicker/icons.json');?>" data-icon-search-placeholder="<?=htmlspecialchars($this->lang->line('menuoverrides_icon_picker_search'), ENT_QUOTES, 'UTF-8');?>" data-icon-empty-text="<?=htmlspecialchars($this->lang->line('menuoverrides_icon_picker_empty'), ENT_QUOTES, 'UTF-8');?>" <?=($isRelocationType ? 'disabled' : '');?>>
                                <i class="fa fa-search"></i> <span class="hidden-xs"><?=$this->lang->line('menuoverrides_icon_browse');?></span>
                            </button>
                        </span>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default js-iconpicker-clear" <?=($isRelocationType ? 'disabled' : '');?>>
                                <i class="fa fa-times"></i> <span class="hidden-xs"><?=$this->lang->line('menuoverrides_icon_clear');?></span>
                            </button>
                        </span>
                    </div>
                    <span class="help-block"><?=$this->lang->line('menuoverrides_icon_help');?></span>
                    <?=form_error('icon');?>
                </div>
            </div>

            <div class="form-group <?=form_error('priority') ? 'has-error' : '';?>">
                <label for="priority" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_priority');?>
                </label>
                <div class="col-sm-3">
                    <input type="number" name="priority" id="priority" class="form-control" value="<?=set_value('priority', isset($override['priority']) ? $override['priority'] : 0);?>" />
                    <span class="help-block"><?=$this->lang->line('menuoverrides_priority_help');?></span>
                    <?=form_error('priority');?>
                </div>
            </div>

            <div class="form-group <?=form_error('status') ? 'has-error' : '';?>">
                <label for="status" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_status');?>
                </label>
                <div class="col-sm-3">
                    <?php
                        echo form_dropdown(
                            'status',
                            [
                                1 => $this->lang->line('menuoverrides_enabled'),
                                0 => $this->lang->line('menuoverrides_disabled'),
                            ],
                            set_value('status', isset($override['status']) ? $override['status'] : 1),
                            'class="form-control" id="status"'
                        );
                    ?>
                    <?=form_error('status');?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="skip_permission">
                    <?=$this->lang->line('menuoverrides_skip_permission');?>
                </label>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="skip_permission" id="skip_permission" value="1" <?=set_checkbox('skip_permission', 1, !empty($override['skip_permission']));?> />
                            <?=$this->lang->line('menuoverrides_skip_permission_help');?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group <?=(form_error('create_if_missing_icon') || form_error('create_if_missing_priority') || form_error('create_if_missing_status')) ? 'has-error' : '';?>">
                <label class="col-sm-3 control-label" for="create_if_missing_enabled">
                    <?=$this->lang->line('menuoverrides_create_if_missing');?>
                </label>
                <div class="col-sm-6">
                    <?php $createIfMissingEnabled = !empty($override['create_if_missing_enabled']); ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="create_if_missing_enabled" id="create_if_missing_enabled" value="1" <?=set_checkbox('create_if_missing_enabled', 1, $createIfMissingEnabled);?> />
                            <?=$this->lang->line('menuoverrides_create_if_missing_help');?>
                        </label>
                    </div>
                    <div class="panel panel-default js-create-if-missing-fields <?=$createIfMissingEnabled ? '' : 'hidden';?>">
                        <div class="panel-body">
                            <div class="form-group <?=form_error('create_if_missing_icon') ? 'has-error' : '';?>">
                                <label class="control-label" for="create_if_missing_icon"><?=$this->lang->line('menuoverrides_create_if_missing_icon');?></label>
                                <?php $placeholderIcon = set_value('create_if_missing_icon', isset($override['create_if_missing_icon']) ? $override['create_if_missing_icon'] : ''); ?>
                                <div class="input-group iconpicker-wrapper">
                                    <span class="input-group-addon iconpicker-preview"><i class="fa<?=($placeholderIcon ? ' ' . htmlentities($placeholderIcon, ENT_QUOTES, 'UTF-8') : '');?>" id="create_if_missing_icon_preview"></i></span>
                                    <input type="text" name="create_if_missing_icon" id="create_if_missing_icon" class="form-control" value="<?=htmlentities($placeholderIcon, ENT_QUOTES, 'UTF-8');?>" <?=!$createIfMissingEnabled ? 'disabled' : '';?> />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default js-iconpicker-toggle" data-target="#create_if_missing_icon" data-preview="#create_if_missing_icon_preview" data-icon-source="<?=base_url('assets/iconpicker/icons.json');?>" data-icon-search-placeholder="<?=htmlspecialchars($this->lang->line('menuoverrides_icon_picker_search'), ENT_QUOTES, 'UTF-8');?>" data-icon-empty-text="<?=htmlspecialchars($this->lang->line('menuoverrides_icon_picker_empty'), ENT_QUOTES, 'UTF-8');?>">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default js-iconpicker-clear">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </span>
                                </div>
                                <span class="help-block"><?=$this->lang->line('menuoverrides_create_if_missing_icon_help');?></span>
                                <?=form_error('create_if_missing_icon');?>
                            </div>
                            <div class="form-group <?=form_error('create_if_missing_priority') ? 'has-error' : '';?>">
                                <label class="control-label" for="create_if_missing_priority"><?=$this->lang->line('menuoverrides_create_if_missing_priority');?></label>
                                <input type="number" name="create_if_missing_priority" id="create_if_missing_priority" class="form-control" value="<?=htmlentities(set_value('create_if_missing_priority', isset($override['create_if_missing_priority']) ? $override['create_if_missing_priority'] : ''), ENT_QUOTES, 'UTF-8');?>" <?=!$createIfMissingEnabled ? 'disabled' : '';?> />
                                <span class="help-block"><?=$this->lang->line('menuoverrides_create_if_missing_priority_help');?></span>
                                <?=form_error('create_if_missing_priority');?>
                            </div>
                            <div class="form-group <?=form_error('create_if_missing_status') ? 'has-error' : '';?>">
                                <label class="control-label" for="create_if_missing_status"><?=$this->lang->line('menuoverrides_create_if_missing_status');?></label>
                                <?php
                                    echo form_dropdown(
                                        'create_if_missing_status',
                                        [
                                            1 => $this->lang->line('menuoverrides_create_if_missing_status_enabled'),
                                            0 => $this->lang->line('menuoverrides_create_if_missing_status_disabled'),
                                        ],
                                        set_value('create_if_missing_status', isset($override['create_if_missing_status']) ? $override['create_if_missing_status'] : 1),
                                        'class="form-control" id="create_if_missing_status" ' . (!$createIfMissingEnabled ? 'disabled' : '')
                                    );
                                ?>
                                <?=form_error('create_if_missing_status');?>
                            </div>
                        </div>
                    </div>
                    <span class="help-block"><?=$this->lang->line('menuoverrides_create_if_missing_payload_help');?></span>
                </div>
            </div>

            <div class="form-group <?=form_error('notes') ? 'has-error' : '';?>">
                <label for="notes" class="col-sm-3 control-label">
                    <?=$this->lang->line('menuoverrides_notes');?>
                </label>
                <div class="col-sm-6">
                    <textarea name="notes" id="notes" class="form-control" rows="3"><?=set_value('notes', isset($override['notes']) ? $override['notes'] : '');?></textarea>
                    <span class="help-block"><?=$this->lang->line('menuoverrides_notes_help');?></span>
                    <?=form_error('notes');?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?=$this->lang->line('menuoverrides_save');?></button>
                    <a href="<?=base_url('menuoverrides/index');?>" class="btn btn-default"><?=$this->lang->line('menuoverrides_cancel');?></a>
                </div>
            </div>
        <?=form_close();?>
    </div>
</div>

