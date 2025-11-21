
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-permission"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_permission')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form style="" action="#" class="form-horizontal" role="form" method="post" id="usertype">
                    <div class="<?php if(form_error('usertypeID')) {echo 'form-group has-error';} else {echo 'form-group';} ?>" >
                        <label for="usertypeID" class="col-sm-2 col-md-offset-2 control-label">
                            <?=$this->lang->line("select_usertype")?> <span class="text-red">*</span>
                        </label>

                        <div class="col-sm-4">
                           <?php
                                $array = array("0" => $this->lang->line("permission_select_usertype"));
                                if (isset($set)) {
                                    $set = $set;
                                } else {
                                    $set = null;
                                }
                                foreach ($usertypes as $usertype) {
                                    $array[$usertype->usertypeID] = $usertype->usertype;
                                }
                                echo form_dropdown("usertypeID", $array, set_value("usertypeID", $set), "id='usertypeID' class='form-control select2'");
                            ?>
                        </div>

                        <!-- <div class="col-sm-1 rep-mar">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("permission_table")?>" >
                        </div> -->
                    </div>
                </form>
            </div>
        </div><!-- row -->
        <?php if (isset($set)): ?>
            <div class="row">
                <div class="col-sm-12">
                    <form action="<?=base_url('permission/save/'.$set)?>" class="form-horizontal" role="form" method="post" id="usertype">
                        <div id="hide-table">
                            <table id="" class="table table-striped table-bordered table-hover dataTable no-footer">
                                <?php
                                        $actions = isset($permissionActions) ? $permissionActions : array();
                                        $groupedModules = isset($groupedPermissions) ? $groupedPermissions : array();
                                        $columnCount = 2 + count($actions);
                                        $actionLabels = array();
                                        foreach ($actions as $actionKey) {
                                            $label = $this->lang->line('permission_' . $actionKey);
                                            if(!$label || $label === 'permission_' . $actionKey) {
                                                $label = ucwords(str_replace('_', ' ', $actionKey));
                                            }
                                            $actionLabels[$actionKey] = $label;
                                        }
                                ?>
                                <thead>
                                    <tr>
                                        <th class="col-lg-1"><?=$this->lang->line('slno')?></th>
                                        <th class="col-lg-3"><?=$this->lang->line('module_name')?></th>
                                        <?php foreach ($actionLabels as $actionKey => $label): ?>
                                            <th class="col-lg-1"><?=$label?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(customCompute($groupedModules)): ?>
                                        <?php foreach ($groupedModules as $sectionName => $sectionData): ?>
                                            <tr class="permission-section">
                                                <td colspan="<?=$columnCount?>" class="permission-section-title"><strong><?=$sectionName?></strong></td>
                                            </tr>
                                            <?php foreach ($sectionData['modules'] as $moduleKey => $moduleData): ?>
                                                <?php
                                                    $moduleInfo = isset($moduleData['module']) ? $moduleData['module'] : NULL;
                                                    $moduleName = $moduleInfo ? $moduleInfo['name'] : $moduleKey;
                                                    $moduleLabel = ($moduleInfo && $moduleInfo['description']) ? $moduleInfo['description'] : ucwords(str_replace('_', ' ', $moduleKey));
                                                    $moduleChecked = $moduleInfo ? ($moduleInfo['active'] === 'yes') : TRUE;
                                                    $modulePermissionID = $moduleInfo ? $moduleInfo['permissionID'] : NULL;
                                                ?>
                                                <tr>
                                                    <td data-title="<?=$this->lang->line('slno')?>">
                                                        <?php if($modulePermissionID): ?>
                                                            <input type="checkbox"
                                                                name="<?=$moduleName?>"
                                                                value="<?=$modulePermissionID?>"
                                                                id="<?=$moduleName?>"
                                                                data-module="<?=$moduleName?>"
                                                                onClick="$(this).processCheck();"
                                                                <?php if($moduleChecked): ?>checked="checked"<?php endif; ?>
                                                            />
                                                        <?php else: ?>
                                                            <span class="text-muted">&mdash;</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td data-title="<?=$this->lang->line('module_name')?>"><?=$moduleLabel?></td>
                                                    <?php foreach ($actions as $actionKey): ?>
                                                        <?php
                                                            $actionInfo = isset($moduleData['actions'][$actionKey]) ? $moduleData['actions'][$actionKey] : NULL;
                                                            $actionLabel = isset($actionLabels[$actionKey]) ? $actionLabels[$actionKey] : ucwords(str_replace('_', ' ', $actionKey));
                                                        ?>
                                                        <td data-title="<?=$actionLabel?>">
                                                            <?php if($actionInfo): ?>
                                                                <input type="checkbox"
                                                                    name="<?=$actionInfo['name']?>"
                                                                    value="<?=$actionInfo['permissionID']?>"
                                                                    id="<?=$actionInfo['name']?>"
                                                                    data-parent="<?=$moduleName?>"
                                                                    <?php if($actionInfo['active'] === 'yes'): ?>checked="checked"<?php endif; ?>
                                                                    <?php if($moduleInfo && !$moduleChecked): ?>disabled<?php endif; ?>
                                                                />
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td colspan="<?=$columnCount?>">
                                                <input class="btn btn-success" type="submit" value="Save Permission">
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="<?=$columnCount?>" class="text-center text-muted"><?='No permissions available.'?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>
                    </form>
                </div>
            </div><!-- row -->
        <?php endif ?>
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
$('.select2').select2();
var usertypeID = $("#usertypeID").val();

$('#usertypeID').change(function(event) {
    var usertypeID = $(this).val();
    $.ajax({
        type: 'POST',
        url: "<?=base_url('permission/permission_list')?>",
        data: "usertypeID=" + usertypeID,
        dataType: "html",
        success: function(data) {
            console.log(data);
           window.location.href = data;
        }
    });
});
$.fn.processCheck = function() {
    var module = $(this).data('module') || $(this).attr('id');
    var $children = $('input[data-parent=\'' + module + '\']');
    if ($(this).is(':checked')) {
        $children.prop('disabled', false);
        $children.prop('checked', true);
    } else {
        $children.prop('disabled', true);
        $children.prop('checked', false);
    }
};


</script>
