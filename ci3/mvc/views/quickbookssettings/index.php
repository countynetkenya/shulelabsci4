
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-wrench"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_quickbookssettings')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                  <div class="nav-tabs">
                      <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#config" aria-expanded="true"><?=$this->lang->line('configuration')?></a></li>
                        <li><a data-toggle="tab" href="#company" aria-expanded="true"><?=$this->lang->line('company_name')?></a></li>
                      </ul>

                      <div class="tab-content">
                        <div class="tab-pane active" id="config" role="tabpanel">
                          <br>
                          <div class="row">
                              <div class="col-sm-12">
                                <h2>Configuration</h2>
                                <h6>QuickBooks developer settings</h6>
                                  <div class="margin-bottom"></div>
                                  <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
                                      <?php if (isset($set_quickbooks) && customCompute($set_quickbooks)) {
                                          $options = $set_quickbooks;
                                          foreach ($options as $option) {
                                              $optionLang = $option->field_names;
                                              if ($option->type == 'text') {?>
                                                  <div class="form-group <?= form_error($option->field_names) ? 'text-danger' : '' ?>">
                                                      <label for="<?= $option->field_names ?>"
                                                             class="col-sm-2 control-label">
                                                          <?= $this->lang->line($optionLang) ?>
                                                          <span class="text-red">*</span>
                                                      </label>
                                                      <div class="col-sm-5">
                                                          <input type="text"
                                                                 class="form-control <?= form_error($option->field_names) ? 'is-invalid' : '' ?>"
                                                                 id="<?= $option->field_names ?>"
                                                                 name="<?= $option->field_names ?>"
                                                                 value="<?= set_value($option->field_names, $option->field_values) ?>">
                                                      </div>
                                                      <span class="col-sm-4 control-label">
                                                      <?= form_error($option->field_names) ?>
                                                  </span>
                                                  </div>
                                              <?php } else if ($option->type == 'select') {
                                                  $activityArr = json_decode($option->activities, true);
                                                  if (customCompute($activityArr)) { ?>
                                                      <div class="form-group <?= form_error($option->field_names) ? 'text-danger' : '' ?>">
                                                          <label class="col-sm-2 control-label"
                                                                 for="<?= $option->field_names ?>">
                                                              <?= $this->lang->line($optionLang) ?>
                                                              <span class="text-danger">*</span>
                                                          </label>
                                                          <div class="col-sm-5">
                                                              <select class="form-control select2"
                                                                      name="<?= $option->field_names ?>"
                                                                      id="<?= $option->field_names ?>">
                                                                  <?php
                                                                  foreach ($activityArr as $key => $select) {
                                                                      $optionSelected = '';
                                                                      if (!set_value($option->field_names)) {
                                                                          if ($option->field_values == $key) {
                                                                              $optionSelected = 'selected';
                                                                          }
                                                                      } else {
                                                                          $optionSelected = 'selected';
                                                                      }

                                                                      echo '<option value="' . $key . '" ' . $optionSelected . '>' . $select . '</option>';
                                                                  }
                                                                  ?>
                                                              </select>
                                                          </div>
                                                          <span class="col-sm-4 control-label">
                                                          <?= form_error($option->field_names) ?>
                                                      </span>
                                                      </div>
                                                  <?php }
                                              }
                                          }
                                      } ?>

                                      <div class="form-group">
                                          <div class="col-sm-offset-2 col-sm-8">
                                              <input type="submit" class="btn btn-success" value="<?=$this->lang->line("save")?>" >
                                          </div>
                                      </div>
                                  </form>
                              </div>
                      </div>

                        </div>

                        <div class="tab-pane" id="company" role="tabpanel">
                          <br>
                          <div class="row">
                            <div class="col-sm-12">
                              <h2>Company</h2>
                              <h6>QuickBooks company you are connected to</h6>
                              <div class="margin-bottom">
                                <div class="btn-group">
                                  <input onclick="oauth.loginPopup()" id="connectQuickBooksButton" type="button" class="btn btn-warning" value="<?=$this->lang->line("new_connection")?>" <?php if(!isset($authUrl) || $authUrl == '') { echo 'disabled="disabled" title="Please save your Client ID and Secret first."'; } ?> >
                                </div>
                              </div>
                              <table class="table table-striped table-bordered table-hover dataTable no-footer">
                                <thead>
                                  <th><?=$this->lang->line('companyID')?></th>
                                  <th><?=$this->lang->line('company_name')?></th>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td><?=$config['companyID']?></td>
                                    <td><?=$config['companyName']?></td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                  </div>

            </div> <!-- col-sm-12 -->

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
    $('.select2').select2();

    var url = '<?php if(isset($authUrl)) { echo $authUrl; } ?>';

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
