
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-invoice"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_fees_balance_tier')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php if(permissionChecker('fees_balance_tier_report')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('fees_balance_tier/report') ?>">
                            <i class="fa fa-list"></i>
                            <?=$this->lang->line('view_report')?>
                        </a>
                    </h5>
                <?php } ?>

                <div class="col-sm-12">

                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <?php if (customCompute($fees_balance_tiers)) {
                                $i = 0;
                                foreach ($fees_balance_tiers as $fees_balance_tier) { ?>
                                    <li class="<?php if ($i == 0) echo 'active'; ?>">
                                      <a data-toggle="tab" href="#tier<?= $fees_balance_tier->fees_balance_tier_id ?>" aria-expanded="true">
                                            <?= $fees_balance_tier->name ?>
                                      </a></li>
                                    <?php $i++;
                                }
                            } ?>
                        </ul>

                        <div class="tab-content">
                            <?php if (customCompute($fees_balance_tiers)) {
                                $i = 0;
                                foreach ($fees_balance_tiers as $fees_balance_tier) {?>
                                    <div class="tab-pane <?= ($i == 0) ? 'active' : '' ?>"
                                         id="tier<?= $fees_balance_tier->fees_balance_tier_id ?>" role="tabpanel">
                                        <br>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <form class="form-horizontal" role="form" method="POST">
                                                    <?php if (isset($fees_balance_tier_values[$fees_balance_tier->name]) && customCompute($fees_balance_tier_values[$fees_balance_tier->name])) {
                                                        $values = $fees_balance_tier_values[$fees_balance_tier->name];
                                                        foreach ($values as $value) {
                                                            $days = $value->days;
                                                            ?>
                                                                <div class="form-group <?= form_error($value->fees_balance_tier_id) ? 'text-danger' : '' ?>">
                                                                    <label for="<?= $value->days ?>" class="col-sm-2 control-label">
                                                                        <?= $days ?> days %
                                                                        <span class="text-red">*</span>
                                                                    </label>
                                                                    <div class="col-sm-5">
                                                                        <input type="text"
                                                                               class="form-control <?= form_error($value->fees_balance_tier_id) ? 'is-invalid' : '' ?>"
                                                                               name="<?= $value->fees_balance_tier_id ?>"
                                                                               value="<?= set_value($value->tier_value, $value->tier_value) ?>">
                                                                    </div>
                                                                    <span class="col-sm-4 control-label">
                                                                    <?= form_error($value->fees_balance_tier_id) ?>
                                                                    </span>
                                                                </div>
                                                            <?php
                                                        }
                                                    } ?>

                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-8">
                                                            <input type="submit" class="btn btn-success" value="Save">
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $i++;
                                }
                            } ?>
                        </div>

                    </div> <!-- nav-tabs-custom -->
                </div>
            </div>
        </div>
    </div>
</div>
