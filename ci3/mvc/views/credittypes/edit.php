
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-feetypes"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("credittypes/index")?>"><?=$this->lang->line('menu_credittypes')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_credittypes')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post">

                   <?php
                        if(form_error('credittypes'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="credittypes" class="col-sm-2 control-label">
                            Credit Type <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="credittypes" name="credittypes" value="<?=set_value('credittypes', $credittypes->credittypes)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('credittypes'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('note'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="note" class="col-sm-2 control-label">
                            <?=$this->lang->line("credittypes_note")?>
                        </label>
                        <div class="col-sm-6">
                            <textarea class="form-control" style="resize:none;" id="note" name="note"><?=set_value('note', $credittypes->note);?></textarea>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('note'); ?>
                        </span>
                    </div>

                    <?php if ($config['active'] == "1") {?>
                    <hr>
                    <h4>QuickBooks Information</h4>

                    <?php
                        if(form_error('incomeaccount'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="incomeaccount" class="col-sm-2 control-label">
                            <?=$this->lang->line("credittypes_incomeaccount")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $incomeaccountArray = array('0' => $this->lang->line('credittypes_select_incomeaccount'));
                                foreach ($incomeaccounts as $key => $value) {
                                    $newKey = $key .",". $value;
                                    $incomeaccountArray[$newKey] = $value;
                                }
                            ?>
                            <?php
                                $select = $credittypes->incomeaccountID .",". $credittypes->incomeaccount;
                                echo form_dropdown("incomeaccount", $incomeaccountArray, set_value("incomeaccount", $select), "id='incomeaccount' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('incomeaccount'); ?>
                        </span>
                    </div>

                    <?php if (empty($config['sessionAccessToken'])) {?>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-8">
                        <input onclick="oauth.loginPopup()" id="connectQuickBooksButton" type="button" class="btn btn-warning" value="<?=$this->lang->line("connect_quickbooks")?>" >
                      </div>
                    </div>
                  <?php } elseif (now() > $config['sessionAccessTokenExpiry']) {?>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-8">
                        <a href="<?=base_url("quickbooks/refreshToken")?>" class="btn btn-warning"><?=$this->lang->line("reconnect_quickbooks")?></a>
                      </div>
                    </div>
                    <?php }
                    }?>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-8">
                        <input type="submit" class="btn btn-success" value="Update Credit Type" >
                      </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('.select2').select2();

    var url = '<?php echo $authUrl; ?>';

    var OAuthCode = function(url) {

        this.loginPopup = function (parameter) {
            this.loginPopupUri(parameter);
        }

        this.loginPopupUri = function (parameter) {

            // Launch Popup
            var parameters = "location=1,width=800,height=650";
            parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

            var win = window.open(url, 'connectPopup', parameters);
            var pollOAuth = window.setInterval(function () {
                try {

                    if (win.document.URL.indexOf("code") != -1) {
                        window.clearInterval(pollOAuth);
                        win.close();
                        location.reload();
                    }
                } catch (e) {
                    console.log(e)
                }
            }, 100);
        }
    }

    var oauth = new OAuthCode(url);

</script>
