
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-student"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("student/index")?>"><?=$this->lang->line('menu_student')?></a></li>
            <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('menu_student')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">

                    <?php
                        if(form_error('name'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="name_id" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_name")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="name_id" name="name" value="<?=set_value('name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('name'); ?>
                        </span>
                    </div>


                    <?php
                        if(form_error('guargianID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guargianID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_guargian")?> <span class="text-red">*</span>
                        </label>
                            <div class="col-sm-6">
                                <?php
                                    $array = array('' => $this->lang->line('student_select_guargian'));
									                  $array['0'] = "Add new";
                                    foreach ($parents as $parent) {
                                        $parentsemail = '';
                                        if($parent->email) {
                                            $parentsemail = " (" . $parent->email ." )";
                                        }
                                        $array[$parent->parentsID] = $parent->name.$parentsemail;
                                    }
                                    echo form_dropdown("guargianID", $array, '', "id='guargianID' class='form-control guargianID select2'");
                                ?>
                            </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guargianID'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('admission_date'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="admission_date" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_admission_date")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="admission_date" name="admission_date" value="<?=set_value('admission_date')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('admission_date'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('dob'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="dob" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_dob")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="dob" name="dob" value="<?=set_value('dob')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('dob'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('sex'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="sex" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_sex")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                echo form_dropdown("sex", array($this->lang->line('student_sex_male') => $this->lang->line('student_sex_male'), $this->lang->line('student_sex_female') => $this->lang->line('student_sex_female')), set_value("sex"), "id='sex' class='form-control'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('sex'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('bloodgroup'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="bloodgroup" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_bloodgroup")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $bloodArray = array(
                                    '0' => $this->lang->line('student_select_bloodgroup'),
                                    'A+' => 'A+',
                                    'A-' => 'A-',
                                    'B+' => 'B+',
                                    'B-' => 'B-',
                                    'O+' => 'O+',
                                    'O-' => 'O-',
                                    'AB+' => 'AB+',
                                    'AB-' => 'AB-'
                                );
                                echo form_dropdown("bloodgroup", $bloodArray, set_value("bloodgroup"), "id='bloodgroup' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('bloodgroup'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('religion'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="religion" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_religion")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="religion" name="religion" value="<?=set_value('religion')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('religion'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('email'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="email" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_email")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="email" name="email" value="<?=set_value('email')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('email'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('phone'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="phone" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_phone")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="phone" name="phone" value="<?=set_value('phone')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('phone'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('address'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="address" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_address")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="address" name="address" value="<?=set_value('address')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('address'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('state'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="state" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_state")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="state" name="state" value="<?=set_value('state')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('state'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('country'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="country" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_country")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $country['0'] = $this->lang->line('student_select_country');
                                foreach ($allcountry as $allcountryKey => $allcountryit) {
                                    $country[$allcountryKey] = $allcountryit;
                                }
                            ?>
                            <?php
                                echo form_dropdown("country", $country, set_value("country"), "id='country' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('country'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('classesID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="classesID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_classes")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $classArray = array(0 => $this->lang->line("student_select_class"));
                                foreach ($classes as $classa) {
                                    $classArray[$classa->classesID] = $classa->classes;
                                }
                                echo form_dropdown("classesID", $classArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('classesID'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('sectionID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="sectionID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_section")?> <span class="text-red">*</span>
                        </label>

                        <div class="col-sm-6">
                            <?php
                                $sectionArray = array(0 => $this->lang->line("student_select_section"));
                                if($sections != "empty") {
                                    foreach ($sections as $section) {
                                        $sectionArray[$section->sectionID] = $section->section;
                                    }
                                }

                                $sID = 0;
                                if($sectionID == 0) {
                                    $sID = 0;
                                } else {
                                    $sID = $sectionID;
                                }

                                echo form_dropdown("sectionID", $sectionArray, set_value("sectionID", $sID), "id='sectionID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('sectionID'); ?>
                        </span>
                    </div>


                    <div class="form-group <?=form_error('studentGroupID') ? ' has-error' : ''  ?>">
                        <label for="studentGroupID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_studentgroup")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $groupArray = array(0 => $this->lang->line("student_select_studentgroup"));
                                if(customCompute($studentgroups)) {
                                    foreach ($studentgroups as $studentgroup) {
                                        $groupArray[$studentgroup->studentgroupID] = $studentgroup->group;
                                    }
                                }
                                echo form_dropdown("studentGroupID", $groupArray, set_value("studentGroupID"), "id='studentGroupID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('studentGroupID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('optionalSubjectID') ? ' has-error' : ''  ?>">
                        <label for="optionalSubjectID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_optionalsubject")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                            $optionalSubjectArray = array(0 => $this->lang->line("student_select_optionalsubject"));
                            if($optionalSubjects != "empty") {
                                foreach ($optionalSubjects as $optionalSubject) {
                                    $optionalSubjectArray[$optionalSubject->subjectID] = $optionalSubject->subject;
                                }
                            }

                            echo form_dropdown("optionalSubjectID[]", $optionalSubjectArray, set_value("optionalSubjectID", $optionalSubjectID), "id='optionalSubjectID' class='form-control select2' multiple");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('optionalSubjectID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('nonexaminableSubjectID') ? ' has-error' : ''  ?>">
                        <label for="nonexaminableSubjectID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_nonexaminablesubject")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                            $nonexaminableSubjectArray = array(0 => $this->lang->line("student_select_nonexaminablesubject"));
                            if($nonexaminableSubjects != "empty") {
                                foreach ($nonexaminableSubjects as $nonexaminableSubject) {
                                    $nonexaminableSubjectArray[$nonexaminableSubject->subjectID] = $nonexaminableSubject->subject;
                                }
                            }

                            echo form_dropdown("nonexaminableSubjectID[]", $nonexaminableSubjectArray, set_value("nonexaminableSubjectID"), "id='nonexaminableSubjectID' class='form-control select2' multiple");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('nonexaminableSubjectID'); ?>
                        </span>
                    </div>

                    <!--<?php
                        if(form_error('registerNO'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="registerNO" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_registerNO")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="registerNO" name="registerNO" value="<?=set_value('registerNO')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('registerNO'); ?>
                        </span>
                    </div>-->

                    <?php
                        if(form_error('roll'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="roll" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_roll")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="roll" name="roll" value="<?=set_value('roll')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('roll'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('photo'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="photo" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_photo")?>
                        </label>
                        <div class="col-sm-6">
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename" disabled="disabled">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                        <span class="fa fa-remove"></span>
                                        <?=$this->lang->line('student_clear')?>
                                    </button>
                                    <div class="btn btn-success image-preview-input">
                                        <span class="fa fa-repeat"></span>
                                        <span class="image-preview-input-title">
                                        <?=$this->lang->line('student_file_browse')?></span>
                                        <input type="file" accept="image/png, image/jpeg, image/gif" name="photo"/>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <span class="col-sm-4">
                            <?php echo form_error('photo'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('extraCurricularActivities') ? ' has-error' : ''  ?>">
                        <label for="extraCurricularActivities" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_extracurricularactivities")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="extraCurricularActivities" name="extraCurricularActivities" value="<?=set_value('extraCurricularActivities')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('extraCurricularActivities'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('remarks') ? ' has-error' : ''  ?>">
                        <label for="remarks" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_remarks")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="remarks" name="remarks" value="<?=set_value('remarks')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('remarks'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('username'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="username" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_username")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="username" name="username" value="<?=set_value('username')?>" >
                        </div>
                         <span class="col-sm-4 control-label">
                            <?php echo form_error('username'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('password'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="password" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_password")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="password" name="password" value="<?=set_value('password')?>" >
                        </div>
                         <span class="col-sm-4 control-label">
                            <?php echo form_error('password'); ?>
                        </span>
                    </div>

					<div id="parent" style="display:none">
					<hr />
					<h4>Parent details</h4>
					<?php
                        if(form_error('parent_name'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="name_id" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_guargian_name")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="parent_name_id" name="parent_name" value="<?=set_value('parent_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('parent_name'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('father_name'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_father_name")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_name" name="father_name" value="<?=set_value('father_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_name'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('mother_name'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_mother_name")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_name" name="mother_name" value="<?=set_value('mother_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_name'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('father_profession'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_profession" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_father_profession")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_profession" name="father_profession" value="<?=set_value('father_profession')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_profession'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('mother_profession'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_profession" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_mother_profession")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_profession" name="mother_profession" value="<?=set_value('mother_profession')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_profession'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('email'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="email" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_email")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="parent_email" name="parent_email" value="<?=set_value('parent_email')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('parent_email'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('parent_phone'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="phone" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_phone")?>
                        </label>
                        <div class="col-sm-2">
                          <select id="countrycode" name="countrycode" class='form-control select2'>
                            <option data-countryCode="DZ" value="213">Algeria (+213)</option>
                        		<option data-countryCode="AD" value="376">Andorra (+376)</option>
                        		<option data-countryCode="AO" value="244">Angola (+244)</option>
                        		<option data-countryCode="AI" value="1264">Anguilla (+1264)</option>
                        		<option data-countryCode="AG" value="1268">Antigua &amp; Barbuda (+1268)</option>
                        		<option data-countryCode="AR" value="54">Argentina (+54)</option>
                        		<option data-countryCode="AM" value="374">Armenia (+374)</option>
                        		<option data-countryCode="AW" value="297">Aruba (+297)</option>
                        		<option data-countryCode="AU" value="61">Australia (+61)</option>
                        		<option data-countryCode="AT" value="43">Austria (+43)</option>
                        		<option data-countryCode="AZ" value="994">Azerbaijan (+994)</option>
                        		<option data-countryCode="BS" value="1242">Bahamas (+1242)</option>
                        		<option data-countryCode="BH" value="973">Bahrain (+973)</option>
                        		<option data-countryCode="BD" value="880">Bangladesh (+880)</option>
                        		<option data-countryCode="BB" value="1246">Barbados (+1246)</option>
                        		<option data-countryCode="BY" value="375">Belarus (+375)</option>
                        		<option data-countryCode="BE" value="32">Belgium (+32)</option>
                        		<option data-countryCode="BZ" value="501">Belize (+501)</option>
                        		<option data-countryCode="BJ" value="229">Benin (+229)</option>
                        		<option data-countryCode="BM" value="1441">Bermuda (+1441)</option>
                        		<option data-countryCode="BT" value="975">Bhutan (+975)</option>
                        		<option data-countryCode="BO" value="591">Bolivia (+591)</option>
                        		<option data-countryCode="BA" value="387">Bosnia Herzegovina (+387)</option>
                        		<option data-countryCode="BW" value="267">Botswana (+267)</option>
                        		<option data-countryCode="BR" value="55">Brazil (+55)</option>
                        		<option data-countryCode="BN" value="673">Brunei (+673)</option>
                        		<option data-countryCode="BG" value="359">Bulgaria (+359)</option>
                        		<option data-countryCode="BF" value="226">Burkina Faso (+226)</option>
                        		<option data-countryCode="BI" value="257">Burundi (+257)</option>
                        		<option data-countryCode="KH" value="855">Cambodia (+855)</option>
                        		<option data-countryCode="CM" value="237">Cameroon (+237)</option>
                        		<option data-countryCode="CA" value="1">Canada (+1)</option>
                        		<option data-countryCode="CV" value="238">Cape Verde Islands (+238)</option>
                        		<option data-countryCode="KY" value="1345">Cayman Islands (+1345)</option>
                        		<option data-countryCode="CF" value="236">Central African Republic (+236)</option>
                        		<option data-countryCode="CL" value="56">Chile (+56)</option>
                        		<option data-countryCode="CN" value="86">China (+86)</option>
                        		<option data-countryCode="CO" value="57">Colombia (+57)</option>
                        		<option data-countryCode="KM" value="269">Comoros (+269)</option>
                        		<option data-countryCode="CG" value="242">Congo (+242)</option>
                        		<option data-countryCode="CK" value="682">Cook Islands (+682)</option>
                        		<option data-countryCode="CR" value="506">Costa Rica (+506)</option>
                        		<option data-countryCode="HR" value="385">Croatia (+385)</option>
                        		<option data-countryCode="CU" value="53">Cuba (+53)</option>
                        		<option data-countryCode="CY" value="90392">Cyprus North (+90392)</option>
                        		<option data-countryCode="CY" value="357">Cyprus South (+357)</option>
                        		<option data-countryCode="CZ" value="42">Czech Republic (+42)</option>
                        		<option data-countryCode="DK" value="45">Denmark (+45)</option>
                        		<option data-countryCode="DJ" value="253">Djibouti (+253)</option>
                        		<option data-countryCode="DM" value="1809">Dominica (+1809)</option>
                        		<option data-countryCode="DO" value="1809">Dominican Republic (+1809)</option>
                        		<option data-countryCode="EC" value="593">Ecuador (+593)</option>
                        		<option data-countryCode="EG" value="20">Egypt (+20)</option>
                        		<option data-countryCode="SV" value="503">El Salvador (+503)</option>
                        		<option data-countryCode="GQ" value="240">Equatorial Guinea (+240)</option>
                        		<option data-countryCode="ER" value="291">Eritrea (+291)</option>
                        		<option data-countryCode="EE" value="372">Estonia (+372)</option>
                        		<option data-countryCode="ET" value="251">Ethiopia (+251)</option>
                        		<option data-countryCode="FK" value="500">Falkland Islands (+500)</option>
                        		<option data-countryCode="FO" value="298">Faroe Islands (+298)</option>
                        		<option data-countryCode="FJ" value="679">Fiji (+679)</option>
                        		<option data-countryCode="FI" value="358">Finland (+358)</option>
                        		<option data-countryCode="FR" value="33">France (+33)</option>
                        		<option data-countryCode="GF" value="594">French Guiana (+594)</option>
                        		<option data-countryCode="PF" value="689">French Polynesia (+689)</option>
                        		<option data-countryCode="GA" value="241">Gabon (+241)</option>
                        		<option data-countryCode="GM" value="220">Gambia (+220)</option>
                        		<option data-countryCode="GE" value="7880">Georgia (+7880)</option>
                        		<option data-countryCode="DE" value="49">Germany (+49)</option>
                        		<option data-countryCode="GH" value="233">Ghana (+233)</option>
                        		<option data-countryCode="GI" value="350">Gibraltar (+350)</option>
                        		<option data-countryCode="GR" value="30">Greece (+30)</option>
                        		<option data-countryCode="GL" value="299">Greenland (+299)</option>
                        		<option data-countryCode="GD" value="1473">Grenada (+1473)</option>
                        		<option data-countryCode="GP" value="590">Guadeloupe (+590)</option>
                        		<option data-countryCode="GU" value="671">Guam (+671)</option>
                        		<option data-countryCode="GT" value="502">Guatemala (+502)</option>
                        		<option data-countryCode="GN" value="224">Guinea (+224)</option>
                        		<option data-countryCode="GW" value="245">Guinea - Bissau (+245)</option>
                        		<option data-countryCode="GY" value="592">Guyana (+592)</option>
                        		<option data-countryCode="HT" value="509">Haiti (+509)</option>
                        		<option data-countryCode="HN" value="504">Honduras (+504)</option>
                        		<option data-countryCode="HK" value="852">Hong Kong (+852)</option>
                        		<option data-countryCode="HU" value="36">Hungary (+36)</option>
                        		<option data-countryCode="IS" value="354">Iceland (+354)</option>
                        		<option data-countryCode="IN" value="91">India (+91)</option>
                        		<option data-countryCode="ID" value="62">Indonesia (+62)</option>
                        		<option data-countryCode="IR" value="98">Iran (+98)</option>
                        		<option data-countryCode="IQ" value="964">Iraq (+964)</option>
                        		<option data-countryCode="IE" value="353">Ireland (+353)</option>
                        		<option data-countryCode="IL" value="972">Israel (+972)</option>
                        		<option data-countryCode="IT" value="39">Italy (+39)</option>
                        		<option data-countryCode="JM" value="1876">Jamaica (+1876)</option>
                        		<option data-countryCode="JP" value="81">Japan (+81)</option>
                        		<option data-countryCode="JO" value="962">Jordan (+962)</option>
                        		<option data-countryCode="KZ" value="7">Kazakhstan (+7)</option>
                        		<option data-countryCode="KE" value="254" selected>Kenya (+254)</option>
                        		<option data-countryCode="KI" value="686">Kiribati (+686)</option>
                        		<option data-countryCode="KP" value="850">Korea North (+850)</option>
                        		<option data-countryCode="KR" value="82">Korea South (+82)</option>
                        		<option data-countryCode="KW" value="965">Kuwait (+965)</option>
                        		<option data-countryCode="KG" value="996">Kyrgyzstan (+996)</option>
                        		<option data-countryCode="LA" value="856">Laos (+856)</option>
                        		<option data-countryCode="LV" value="371">Latvia (+371)</option>
                        		<option data-countryCode="LB" value="961">Lebanon (+961)</option>
                        		<option data-countryCode="LS" value="266">Lesotho (+266)</option>
                        		<option data-countryCode="LR" value="231">Liberia (+231)</option>
                        		<option data-countryCode="LY" value="218">Libya (+218)</option>
                        		<option data-countryCode="LI" value="417">Liechtenstein (+417)</option>
                        		<option data-countryCode="LT" value="370">Lithuania (+370)</option>
                        		<option data-countryCode="LU" value="352">Luxembourg (+352)</option>
                        		<option data-countryCode="MO" value="853">Macao (+853)</option>
                        		<option data-countryCode="MK" value="389">Macedonia (+389)</option>
                        		<option data-countryCode="MG" value="261">Madagascar (+261)</option>
                        		<option data-countryCode="MW" value="265">Malawi (+265)</option>
                        		<option data-countryCode="MY" value="60">Malaysia (+60)</option>
                        		<option data-countryCode="MV" value="960">Maldives (+960)</option>
                        		<option data-countryCode="ML" value="223">Mali (+223)</option>
                        		<option data-countryCode="MT" value="356">Malta (+356)</option>
                        		<option data-countryCode="MH" value="692">Marshall Islands (+692)</option>
                        		<option data-countryCode="MQ" value="596">Martinique (+596)</option>
                        		<option data-countryCode="MR" value="222">Mauritania (+222)</option>
                        		<option data-countryCode="YT" value="269">Mayotte (+269)</option>
                        		<option data-countryCode="MX" value="52">Mexico (+52)</option>
                        		<option data-countryCode="FM" value="691">Micronesia (+691)</option>
                        		<option data-countryCode="MD" value="373">Moldova (+373)</option>
                        		<option data-countryCode="MC" value="377">Monaco (+377)</option>
                        		<option data-countryCode="MN" value="976">Mongolia (+976)</option>
                        		<option data-countryCode="MS" value="1664">Montserrat (+1664)</option>
                        		<option data-countryCode="MA" value="212">Morocco (+212)</option>
                        		<option data-countryCode="MZ" value="258">Mozambique (+258)</option>
                        		<option data-countryCode="MN" value="95">Myanmar (+95)</option>
                        		<option data-countryCode="NA" value="264">Namibia (+264)</option>
                        		<option data-countryCode="NR" value="674">Nauru (+674)</option>
                        		<option data-countryCode="NP" value="977">Nepal (+977)</option>
                        		<option data-countryCode="NL" value="31">Netherlands (+31)</option>
                        		<option data-countryCode="NC" value="687">New Caledonia (+687)</option>
                        		<option data-countryCode="NZ" value="64">New Zealand (+64)</option>
                        		<option data-countryCode="NI" value="505">Nicaragua (+505)</option>
                        		<option data-countryCode="NE" value="227">Niger (+227)</option>
                        		<option data-countryCode="NG" value="234">Nigeria (+234)</option>
                        		<option data-countryCode="NU" value="683">Niue (+683)</option>
                        		<option data-countryCode="NF" value="672">Norfolk Islands (+672)</option>
                        		<option data-countryCode="NP" value="670">Northern Marianas (+670)</option>
                        		<option data-countryCode="NO" value="47">Norway (+47)</option>
                        		<option data-countryCode="OM" value="968">Oman (+968)</option>
                        		<option data-countryCode="PW" value="680">Palau (+680)</option>
                        		<option data-countryCode="PA" value="507">Panama (+507)</option>
                        		<option data-countryCode="PG" value="675">Papua New Guinea (+675)</option>
                        		<option data-countryCode="PY" value="595">Paraguay (+595)</option>
                        		<option data-countryCode="PE" value="51">Peru (+51)</option>
                        		<option data-countryCode="PH" value="63">Philippines (+63)</option>
                        		<option data-countryCode="PL" value="48">Poland (+48)</option>
                        		<option data-countryCode="PT" value="351">Portugal (+351)</option>
                        		<option data-countryCode="PR" value="1787">Puerto Rico (+1787)</option>
                        		<option data-countryCode="QA" value="974">Qatar (+974)</option>
                        		<option data-countryCode="RE" value="262">Reunion (+262)</option>
                        		<option data-countryCode="RO" value="40">Romania (+40)</option>
                        		<option data-countryCode="RU" value="7">Russia (+7)</option>
                        		<option data-countryCode="RW" value="250">Rwanda (+250)</option>
                        		<option data-countryCode="SM" value="378">San Marino (+378)</option>
                        		<option data-countryCode="ST" value="239">Sao Tome &amp; Principe (+239)</option>
                        		<option data-countryCode="SA" value="966">Saudi Arabia (+966)</option>
                        		<option data-countryCode="SN" value="221">Senegal (+221)</option>
                        		<option data-countryCode="CS" value="381">Serbia (+381)</option>
                        		<option data-countryCode="SC" value="248">Seychelles (+248)</option>
                        		<option data-countryCode="SL" value="232">Sierra Leone (+232)</option>
                        		<option data-countryCode="SG" value="65">Singapore (+65)</option>
                        		<option data-countryCode="SK" value="421">Slovak Republic (+421)</option>
                        		<option data-countryCode="SI" value="386">Slovenia (+386)</option>
                        		<option data-countryCode="SB" value="677">Solomon Islands (+677)</option>
                        		<option data-countryCode="SO" value="252">Somalia (+252)</option>
                        		<option data-countryCode="ZA" value="27">South Africa (+27)</option>
                        		<option data-countryCode="ES" value="34">Spain (+34)</option>
                        		<option data-countryCode="LK" value="94">Sri Lanka (+94)</option>
                        		<option data-countryCode="SH" value="290">St. Helena (+290)</option>
                        		<option data-countryCode="KN" value="1869">St. Kitts (+1869)</option>
                        		<option data-countryCode="SC" value="1758">St. Lucia (+1758)</option>
                        		<option data-countryCode="SD" value="249">Sudan (+249)</option>
                        		<option data-countryCode="SR" value="597">Suriname (+597)</option>
                        		<option data-countryCode="SZ" value="268">Swaziland (+268)</option>
                        		<option data-countryCode="SE" value="46">Sweden (+46)</option>
                        		<option data-countryCode="CH" value="41">Switzerland (+41)</option>
                        		<option data-countryCode="SI" value="963">Syria (+963)</option>
                        		<option data-countryCode="TW" value="886">Taiwan (+886)</option>
                        		<option data-countryCode="TJ" value="7">Tajikstan (+7)</option>
                        		<option data-countryCode="TH" value="66">Thailand (+66)</option>
                        		<option data-countryCode="TG" value="228">Togo (+228)</option>
                        		<option data-countryCode="TO" value="676">Tonga (+676)</option>
                        		<option data-countryCode="TT" value="1868">Trinidad &amp; Tobago (+1868)</option>
                        		<option data-countryCode="TN" value="216">Tunisia (+216)</option>
                        		<option data-countryCode="TR" value="90">Turkey (+90)</option>
                        		<option data-countryCode="TM" value="7">Turkmenistan (+7)</option>
                        		<option data-countryCode="TM" value="993">Turkmenistan (+993)</option>
                        		<option data-countryCode="TC" value="1649">Turks &amp; Caicos Islands (+1649)</option>
                        		<option data-countryCode="TV" value="688">Tuvalu (+688)</option>
                        		<option data-countryCode="UG" value="256">Uganda (+256)</option>
                        		<option data-countryCode="GB" value="44">UK (+44)</option>
                        		<option data-countryCode="UA" value="380">Ukraine (+380)</option>
                        		<option data-countryCode="AE" value="971">United Arab Emirates (+971)</option>
                        		<option data-countryCode="UY" value="598">Uruguay (+598)</option>
                        		<option data-countryCode="US" value="1">USA (+1)</option>
                        		<option data-countryCode="UZ" value="7">Uzbekistan (+7)</option>
                        		<option data-countryCode="VU" value="678">Vanuatu (+678)</option>
                        		<option data-countryCode="VA" value="379">Vatican City (+379)</option>
                        		<option data-countryCode="VE" value="58">Venezuela (+58)</option>
                        		<option data-countryCode="VN" value="84">Vietnam (+84)</option>
                        		<option data-countryCode="VG" value="84">Virgin Islands - British (+1284)</option>
                        		<option data-countryCode="VI" value="84">Virgin Islands - US (+1340)</option>
                        		<option data-countryCode="WF" value="681">Wallis &amp; Futuna (+681)</option>
                        		<option data-countryCode="YE" value="969">Yemen (North)(+969)</option>
                        		<option data-countryCode="YE" value="967">Yemen (South)(+967)</option>
                        		<option data-countryCode="ZM" value="260">Zambia (+260)</option>
                        		<option data-countryCode="ZW" value="263">Zimbabwe (+263)</option>
                          </select>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="parent_phone" name="parent_phone" value="<?=set_value('parent_phone')?>" placeholder="720000000">
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('parent_phone'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('parent_address'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="address" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_address")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="parent_address" name="parent_address" value="<?=set_value('parent_address')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('parent_address'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('parent_photo'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="photo" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_photo")?>
                        </label>
                        <div class="col-sm-6">
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename" disabled="disabled">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                        <span class="fa fa-remove"></span>
                                        <?=$this->lang->line('student_clear')?>
                                    </button>
                                    <div class="btn btn-success image-preview-input">
                                        <span class="fa fa-repeat"></span>
                                        <span class="image-preview-input-title">
                                        <?=$this->lang->line('student_file_browse')?></span>
                                        <input type="file" accept="image/png, image/jpeg, image/gif" name="parent_photo"/>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <span class="col-sm-4">
                            <?php echo form_error('parent_photo'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('parent_username'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="username" class="col-sm-2 control-label">
                            <?=$this->lang->line("parent_username")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="parent_username" name="parent_username" value="<?=set_value('parent_username')?>" >
                        </div>
                         <span class="col-sm-4 control-label">
                            <?php echo form_error('parent_username'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('parent_password'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="password" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_password")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="parent_password" name="parent_password" value="<?=set_value('parent_password')?>" >
                        </div>
                         <span class="col-sm-4 control-label">
                            <?php echo form_error('parent_password'); ?>
                        </span>
                    </div>

					</div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("add_student")?>" >
                        </div>
                    </div>
                </form>

                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="callout callout-danger">
                      <h4>These data not inserted</h4>
                      <p><?=$this->session->flashdata('msg'); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="callout callout-danger">
                        <p><?=$this->session->flashdata('error'); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($siteinfos->note==1) { ?>
                    <div class="callout callout-danger">
                        <p><b>Note:</b> Create teacher, class, section before create a new student.</p>
                    </div>
                <?php } ?>
            </div> <!-- col-sm-8 -->

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
$( ".select2" ).select2();
$('#dob').datepicker({ startView: 2 });
$('#admission_date').datepicker({ startView: 2 });

$('#username').keyup(function() {
    $(this).val($(this).val().replace(/\s/g, ''));
});

$('#guargianID').change(function(event) {
	var ID = $(this).val();
	if (ID === '0') {
		$("#parent").show();
    $('html, body').animate({
        scrollTop: $("#parent").offset().top
    }, 1000);
	} else
		$("#parent").hide();
});

$('#classesID').change(function(event) {
    var classesID = $(this).val();
    if(classesID === '0') {
        $('#sectionID').val(0);
    } else {
        $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/sectioncall')?>",
            data: "id=" + classesID,
            dataType: "html",
            success: function(data) {
               $('#sectionID').html(data);
            }
        });

        $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/optionalsubjectcall')?>",
            data: "id=" + classesID,
            dataType: "html",
            success: function(data2) {
                $('#optionalSubjectID').html(data2);
            }
        });
    }
});

$(document).on('click', '#close-preview', function(){
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
           $('.content').css('padding-bottom', '100px');
        },
         function () {
           $('.image-preview').popover('hide');
           $('.content').css('padding-bottom', '20px');
        }
    );
});

$(function() {
    // Create the close button
    var closebtn = $('<button/>', {
        type:"button",
        text: 'x',
        id: 'close-preview',
        style: 'font-size: initial;',
    });
    closebtn.attr("class","close pull-right");
    // Set the popover default content
    $('.image-preview').popover({
        trigger:'manual',
        html:true,
        title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
        content: "There's no image",
        placement:'bottom'
    });
    // Clear event
    $('.image-preview-clear').click(function(){
        $('.image-preview').attr("data-content","").popover('hide');
        $('.image-preview-filename').val("");
        $('.image-preview-clear').hide();
        $('.image-preview-input input:file').val("");
        $(".image-preview-input-title").text("<?=$this->lang->line('student_file_browse')?>");
    });
    // Create the preview image
    $(".image-preview-input input:file").change(function (){
        var img = $('<img/>', {
            id: 'dynamic',
            width:250,
            height:200,
            overflow:'hidden'
        });
        var file = this.files[0];
        var reader = new FileReader();
        // Set preview image into the popover data-content
        reader.onload = function (e) {
            $(".image-preview-input-title").text("<?=$this->lang->line('student_file_browse')?>");
            $(".image-preview-clear").show();
            $(".image-preview-filename").val(file.name);
            img.attr('src', e.target.result);
            $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
            $('.content').css('padding-bottom', '100px');
        }
        reader.readAsDataURL(file);
    });
});


</script>
