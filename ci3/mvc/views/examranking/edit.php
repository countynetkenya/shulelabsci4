<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-list-ol"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("examranking/index")?>"><?=$this->lang->line('menu_examranking')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_examranking')?></li>
        </ol>
    </div><!-- /.box-header -->

    <!-- form start -->
    <div class="box-body">
        <style type="text/css">
            .setting-fieldset {
                border: 1px solid #DBDEE0 !important;
                padding: 15px !important;
                margin: 0 0 25px 0 !important;
                box-shadow: 0px 0px 0px 0px #000;
            }
            .setting-legend {
                font-size: 1.1em !important;
                font-weight: bold !important;
                text-align: left !important;
                width: auto;
                color: #428BCA;
                padding: 5px 15px;
                border: 1px solid #DBDEE0 !important;
                margin: 0px;
            }

            .margintop {
                margin-top: 20px;
            }

            .margintopbottom {
                margin-top: 15px;
                margin-bottom: 10px;
            }

            .singlebox {
                padding: 10px;
                border: 1px solid #ddd
            }

            .singlebox .singleboxheader {
                border-bottom: 1px solid #ddd;
                margin-left: -10px;
                margin-right: -10px;
                padding: 5px;
                padding-top: 0px;
                margin-top: -3px;
            }

            .singlebox .checkbox {
                margin-left: 5px;
            }

            .singleboxtwo .checkbox {
                margin-left: 17px;
            }

            .classexamDiv {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
            }

            .classexamheader {
                margin: 0px;
                border-bottom: 1px solid #ddd;
                margin-left: -15px;
                margin-right: -15px;
                padding: 5px 15px;
                margin-top: -15px;
                margin-bottom: 15px;
            }
        </style>

        <form class="form-horizontal" role="form" method="post">

            <?php
                if(form_error('examranking'))
                    echo "<div class='form-group has-error' >";
                else
                    echo "<div class='form-group' >";
            ?>
                <label for="examranking" class="col-sm-2 control-label">
                    <?=$this->lang->line("examranking_examranking")?> <span class="text-red">*</span>
                </label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="examranking" name="examranking" value="<?=set_value('examranking', $examranking->examranking)?>" >
                </div>
                <span class="col-sm-4 control-label">
                    <?php echo form_error('examranking'); ?>
                </span>
            </div>

            <?php
                if(form_error('classesID'))
                    echo "<div class='form-group has-error' >";
                else
                    echo "<div class='form-group' >";
            ?>
                <label for="classesID" class="col-sm-2 control-label">
                    <?=$this->lang->line("examranking_classes")?> <span class="text-red">*</span>
                </label>
                <div class="col-sm-6">
                    <?php
                        $classArray = array(0 => $this->lang->line("examranking_select_class"));
                        foreach ($classes as $classa) {
                            $classArray[$classa->classesID] = $classa->classes;
                        }
                        echo form_dropdown("classesID", $classArray, set_value("classesID", $set), "id='classesID' class='form-control select2'");
                    ?>
                </div>
                <span class="col-sm-4 control-label">
                    <?php echo form_error('classesID'); ?>
                </span>
            </div>

            <?php if(customCompute($mandatorySubjects)){ ?>
            <fieldset class="setting-fieldset">
                <legend class="setting-legend" style="cursor:pointer"><?=$this->lang->line("examranking_mandatory_subjects")?></legend>
                <?php $mandatoryTopNumberArray = array('0' => $this->lang->line("examranking_select_top_number_subjects"));
                for ($i=1; $i<=count($mandatorySubjects); $i++) {
                    $mandatoryTopNumberArray[$i] = $i;
                }
                echo form_dropdown("mandatoryTopNumber", $mandatoryTopNumberArray, set_value("mandatoryTopNumber", $examranking->mandatory_top), "id='mandatoryTopNumber' class='form-control' style='width:auto'");?>
                <br/>
                <div class="row">
                    <?php
                        if(customCompute($mandatorySubjects)) {
                            foreach ($mandatorySubjects as $mandatorySubject) {
                                $checkbox = (in_array($mandatorySubject->subjectID, explode(",",$examranking->subjects))) ? true : false;
                                echo '<div class="col-sm-3">';
                                    echo '<div class="checkbox">';
                                        echo '<label>';
                                            echo '<input class="globalexam" type="checkbox" value="'.$mandatorySubject->subjectID.'" '.set_checkbox('subjects[]', $mandatorySubject->subjectID, $checkbox).' name="subjects[]"> &nbsp;';
                                            echo $mandatorySubject->subject;
                                        echo '</label>';
                                    echo '</div>';
                                echo '</div>';
                            }
                        }
                    ?>
                </div>
            </fieldset>
          <?php }
          if(customCompute($optionalSubjects)){ ?>
          <fieldset class="setting-fieldset">
              <legend class="setting-legend" style="cursor:pointer"><?=$this->lang->line("examranking_optional_subjects")?></legend>
              <?php $optionalTopNumberArray = array('0' => $this->lang->line("examranking_select_top_number_subjects"));
              for ($i=1; $i<=count($optionalSubjects); $i++) {
                  $optionalTopNumberArray[$i] = $i;
              }
              echo form_dropdown("optionalTopNumber", $optionalTopNumberArray, set_value("optionalTopNumber", $examranking->optional_top), "id='optionalTopNumber' class='form-control' style='width:auto'");?>
              <br/>
              <div class="row">
                  <?php
                      if(customCompute($optionalSubjects)) {
                          foreach ($optionalSubjects as $optionalSubject) {
                              $checkbox = (in_array($optionalSubject->subjectID, explode(",",$examranking->subjects))) ? true : false;
                              echo '<div class="col-sm-3">';
                                  echo '<div class="checkbox">';
                                      echo '<label>';
                                          echo '<input class="globalexam" type="checkbox" value="'.$optionalSubject->subjectID.'" '.set_checkbox('subjects[]', $optionalSubject->subjectID, $checkbox).' name="subjects[]"> &nbsp;';
                                          echo $optionalSubject->subject;
                                      echo '</label>';
                                  echo '</div>';
                              echo '</div>';
                          }
                      }
                  ?>
              </div>
          </fieldset>
        <?php }
        if(customCompute($nonexaminableSubjects)){ ?>
        <fieldset class="setting-fieldset">
            <legend class="setting-legend" style="cursor:pointer"><?=$this->lang->line("examranking_nonexaminable_subjects")?></legend>
            <?php $nonexaminableTopNumberArray = array('0' => $this->lang->line("examranking_select_top_number_subjects"));
            for ($i=1; $i <= count($nonexaminableSubjects); $i++) {
                $nonexaminableTopNumberArray[$i] = $i;
            }
            echo form_dropdown("nonexaminableTopNumber", $nonexaminableTopNumberArray, set_value("nonexaminableTopNumber", $examranking->nonexaminable_top), "id='nonexaminableTopNumber' class='form-control' style='width:auto'");?>
            <br/>
            <div class="row">
                <?php
                    if(customCompute($nonexaminableSubjects)) {
                        foreach ($nonexaminableSubjects as $nonexaminableSubject) {
                            $checkbox = (in_array($nonexaminableSubject->subjectID, explode(",",$examranking->subjects))) ? true : false;
                            echo '<div class="col-sm-3">';
                                echo '<div class="checkbox">';
                                    echo '<label>';
                                        echo '<input class="globalexam" type="checkbox" value="'.$nonexaminableSubject->subjectID.'" '.set_checkbox('subjects[]', $nonexaminableSubject->subjectID, $checkbox).' name="subjects[]"> &nbsp;';
                                        echo $nonexaminableSubject->subject;
                                    echo '</label>';
                                echo '</div>';
                            echo '</div>';
                        }
                    }
                ?>
            </div>
        </fieldset>
      <?php }
      if(customCompute($mandatorySubjects) || customCompute($optionalSubjects) || customCompute($nonexaminableSubjects)){?>
            <div class="row">
                <div class="col-sm-12">
                    <input type="submit" class="btn btn-success btn-md" value="<?=$this->lang->line("update_examranking")?>" >
                </div>
            </div>
        </form>
      <?php }?>
    </div>
</div>

<script type="text/javascript">
    $('.select2').select2();
    $('#classesID').change(function() {
        var classesID = $(this).val();
        if(classesID == 0) {
            $('.setting-fieldset').hide();
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('examranking/subject_list')?>",
                data: "id=" + classesID,
                dataType: "html",
                success: function(data) {
                    window.location.href = data;
                }
            });
        }
    });
    $(document).on('click', '.setting-legend', function() {
      var fieldset = $(this).parent();
      var isDisabled = fieldset.prop('disabled');
      if(isDisabled)
        fieldset.prop('disabled', false);
      else {
        fieldset.find(':checkbox').each(function(){
          $(this).attr('checked', false);
        });
        fieldset.prop('disabled', true);
      }
    });
</script>
