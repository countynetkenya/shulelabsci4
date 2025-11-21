<div class="row">
    <div class="col-sm-12">
        <h3><?=$this->lang->line('okr_add');?></h3>
    </div>
</div>

<?php if($this->session->flashdata('error')) { ?>
    <div class="alert alert-danger"><?=$this->session->flashdata('error');?></div>
<?php } ?>

<?=form_open(base_url('okr/create'));?>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group <?=form_error('title') ? 'has-error' : '';?>">
            <label class="control-label" for="title"><?=$this->lang->line('okr_title');?></label>
            <input type="text" class="form-control" name="title" id="title" value="<?=set_value('title', $objective->title);?>">
            <span class="text-danger"><?=form_error('title');?></span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="control-label" for="status"><?=$this->lang->line('okr_status');?></label>
            <input type="text" class="form-control" name="status" id="status" value="<?=set_value('status', $objective->status);?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label" for="description"><?=$this->lang->line('okr_description');?></label>
            <textarea class="form-control" name="description" id="description" rows="3"><?=set_value('description', $objective->description);?></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group <?=form_error('ownerType') ? 'has-error' : '';?>">
            <label class="control-label" for="ownerType"><?=$this->lang->line('okr_owner_type');?></label>
            <select name="ownerType" id="ownerType" class="form-control">
                <?php foreach($ownerTypes as $type) { ?>
                    <option value="<?=$type;?>" <?=set_value('ownerType', $objective->ownerType) === $type ? 'selected' : '';?>><?=$this->lang->line('okr_owner_type_'.$type) ?: ucfirst($type);?></option>
                <?php } ?>
            </select>
            <span class="text-danger"><?=form_error('ownerType');?></span>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group <?=form_error('ownerID') ? 'has-error' : '';?>">
            <label class="control-label" for="ownerID"><?=$this->lang->line('okr_owner_id');?></label>
            <input type="text" class="form-control" name="ownerID" id="ownerID" value="<?=set_value('ownerID', $objective->ownerID);?>">
            <span class="text-danger"><?=form_error('ownerID');?></span>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            <label class="control-label" for="start_date"><?=$this->lang->line('okr_start_date');?></label>
            <input type="date" class="form-control" name="start_date" id="start_date" value="<?=set_value('start_date', $objective->start_date);?>">
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            <label class="control-label" for="end_date"><?=$this->lang->line('okr_end_date');?></label>
            <input type="date" class="form-control" name="end_date" id="end_date" value="<?=set_value('end_date', $objective->end_date);?>">
        </div>
    </div>
</div>

<hr>
<h4><?=$this->lang->line('okr_key_results');?></h4>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group <?=form_error('kr_title') ? 'has-error' : '';?>">
            <label class="control-label" for="kr_title"><?=$this->lang->line('okr_key_result_title');?></label>
            <input type="text" class="form-control" name="kr_title" id="kr_title" value="<?=set_value('kr_title', $keyResult->title);?>">
            <span class="text-danger"><?=form_error('kr_title');?></span>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="kr_unit"><?=$this->lang->line('okr_key_result_unit');?></label>
            <input type="text" class="form-control" name="kr_unit" id="kr_unit" value="<?=set_value('kr_unit', $keyResult->unit);?>">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="kr_data_source"><?=$this->lang->line('okr_key_result_source');?></label>
            <select name="kr_data_source" id="kr_data_source" class="form-control">
                <?php foreach($dataSources as $source) { ?>
                    <option value="<?=$source;?>" <?=set_value('kr_data_source', $keyResult->data_source) === $source ? 'selected' : '';?>><?=$this->lang->line('okr_key_result_source_'.$source) ?: ucfirst($source);?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="kr_target_value"><?=$this->lang->line('okr_key_result_target');?></label>
            <input type="number" step="0.01" class="form-control" name="kr_target_value" id="kr_target_value" value="<?=set_value('kr_target_value', $keyResult->target_value);?>">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="kr_current_value"><?=$this->lang->line('okr_key_result_current');?></label>
            <input type="number" step="0.01" class="form-control" name="kr_current_value" id="kr_current_value" value="<?=set_value('kr_current_value', $keyResult->current_value);?>">
            <span class="text-danger"><?=form_error('kr_current_value');?></span>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="kr_weight"><?=$this->lang->line('okr_key_result_weight');?></label>
            <input type="number" step="0.01" class="form-control" name="kr_weight" id="kr_weight" value="<?=set_value('kr_weight', $keyResult->weight);?>">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="kr_status"><?=$this->lang->line('okr_status');?></label>
            <input type="text" class="form-control" name="kr_status" id="kr_status" value="<?=set_value('kr_status', 'active');?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label" for="kr_description"><?=$this->lang->line('okr_description');?></label>
            <textarea class="form-control" name="kr_description" id="kr_description" rows="3"><?=set_value('kr_description', $keyResult->description);?></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label" for="kr_data_config">Data Source Configuration (JSON)</label>
            <textarea class="form-control" name="kr_data_config" id="kr_data_config" rows="3"><?=set_value('kr_data_config', $keyResult->data_config);?></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 text-right">
        <button type="submit" class="btn btn-primary"><?=$this->lang->line('okr_save');?></button>
        <a href="<?=base_url('okr/index');?>" class="btn btn-default"><?=$this->lang->line('okr_cancel');?></a>
    </div>
</div>
<?=form_close();?>
