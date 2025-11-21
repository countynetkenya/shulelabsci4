
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-feetypes"></i><?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("fees_balance_tier/index")?>"><?=$this->lang->line('menu_fees_balance_tier')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_fees_balance_tier')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post">

                   <?php
                        if(form_error('fifteen_days'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="fifteen_days" class="col-sm-2 control-label">
                            <?=$this->lang->line("fees_balance_tier_fifteen_days")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" id="fifteen_days" name="fifteen_days" placeholder="%" min="0" max="100" value="<?=set_value('fifteen_days', $fees_balance_tier->fifteen_days)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('fifteen_days'); ?>
                        </span>
                    </div>

                    <?php
                         if(form_error('thirty_days'))
                             echo "<div class='form-group has-error' >";
                         else
                             echo "<div class='form-group' >";
                     ?>
                         <label for="thirty_days" class="col-sm-2 control-label">
                             <?=$this->lang->line("fees_balance_tier_thirty_days")?> <span class="text-red">*</span>
                         </label>
                         <div class="col-sm-6">
                             <input type="number" class="form-control" id="thirty_days" name="thirty_days" placeholder="%" min="0" max="100" value="<?=set_value('thirty_days', $fees_balance_tier->thirty_days)?>" >
                         </div>
                         <span class="col-sm-4 control-label">
                             <?php echo form_error('thirty_days'); ?>
                         </span>
                     </div>

                     <?php
                          if(form_error('fortyfive_days'))
                              echo "<div class='form-group has-error' >";
                          else
                              echo "<div class='form-group' >";
                      ?>
                          <label for="fortyfive_days" class="col-sm-2 control-label">
                              <?=$this->lang->line("fees_balance_tier_fortyfive_days")?> <span class="text-red">*</span>
                          </label>
                          <div class="col-sm-6">
                              <input type="number" class="form-control" id="fortyfive_days" name="fortyfive_days" placeholder="%" min="0" max="100" value="<?=set_value('fortyfive_days', $fees_balance_tier->fortyfive_days)?>" >
                          </div>
                          <span class="col-sm-4 control-label">
                              <?php echo form_error('fortyfive_days'); ?>
                          </span>
                      </div>

                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-8">
                        <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update")?>">
                      </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
