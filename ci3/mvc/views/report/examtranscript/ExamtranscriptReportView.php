<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-examtranscriptreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_examtranscriptreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group col-sm-4" id="schoolyearDiv">
                    <label><?=$this->lang->line("examtranscriptreport_academic_year")?><span class="text-red"> * </span></label>
                    <?php
                        $schoolyearArray = array('0' => $this->lang->line("examtranscriptreport_please_select"));
                        if(customCompute($schoolyears)) {
                            foreach ($schoolyears as $schoolyear) {
                                $schoolyearArray[$schoolyear->schoolyearID] = $schoolyear->schoolyear;
                            }
                        }
                        $selectedSchoolyear = set_value('schoolyearID', isset($currentSchoolyearID) ? $currentSchoolyearID : 0);
                        echo form_dropdown("schoolyearID", $schoolyearArray, $selectedSchoolyear, "id='schoolyearID' class='form-control select2'");
                    ?>
                </div>
                <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("examtranscriptreport_class")?><span class="text-red"> * </span></label>
                    <?php
                        $classesArray['0'] = $this->lang->line("examtranscriptreport_please_select");
                        if(customCompute($classes)) {
                            foreach ($classes as $classaKey => $classa) {
                                $classesArray[$classa->classesID] = $classa->classes;
                            }
                        }
                        echo form_dropdown("classesID", $classesArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>
                <div class="form-group col-sm-4" id="datasetModeDiv">
                    <label><?=$this->lang->line("examtranscriptreport_dataset_mode")?><span class="text-red"> * </span></label>
                    <div class="radio">
                        <label>
                            <input type="radio" name="datasetMode" id="datasetModeExam" value="exam" checked>
                            <?=$this->lang->line('examtranscriptreport_dataset_mode_exam')?>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="datasetMode" id="datasetModeCompilation" value="exam_compilation">
                            <?=$this->lang->line('examtranscriptreport_dataset_mode_exam_compilation')?>
                        </label>
                    </div>
                </div>
                <div class="form-group col-sm-4" id="examDiv">
                    <label><?=$this->lang->line("examtranscriptreport_exam")?><span class="text-red"> * </span></label>
                    <?php
                        $examsArray = array('0' => $this->lang->line('examtranscriptreport_none'));
                        if(customCompute($exams)) {
                            foreach ($exams as $exam) {
                                $examsArray[$exam->examID] = $exam->exam;
                            }
                        }
                        echo form_multiselect("examID[]", $examsArray, set_value("examID"), "id='examID' class='form-control select2'");
                     ?>
                </div>
                <div class="form-group col-sm-4" id="examComparisonToggleContainer">
                    <label><?=$this->lang->line('examtranscriptreport_compare_with_exams')?></label>
                    <div class="checkbox" style="margin-top:5px;">
                        <label>
                            <input type="checkbox" id="enableExamComparison" name="enableExamComparison" value="1">
                            <?=$this->lang->line('examtranscriptreport_compare_with_exams_help')?>
                        </label>
                    </div>
                </div>
                <div class="form-group col-sm-4" id="examCompilationToggleContainer">
                    <label><?=$this->lang->line('examtranscriptreport_compare_with_compilations')?></label>
                    <div class="checkbox" style="margin-top:5px;">
                        <label>
                            <input type="checkbox" id="enableExamCompilationComparison" name="enableExamCompilationComparison" value="1">
                            <?=$this->lang->line('examtranscriptreport_compare_with_compilations_help')?>
                        </label>
                    </div>
                </div>
                <div class="form-group col-sm-4" id="examCompilationDiv">
                    <label><?=$this->lang->line("examtranscriptreport_examcompilation")?></label>
                    <?php
                        $examcompilationArray = array('0' => $this->lang->line('examtranscriptreport_none'));
                        if(customCompute($examcompilations)) {
                            foreach ($examcompilations as $examcompilation) {
                                $examcompilationArray[$examcompilation->examcompilationID] = $examcompilation->examcompilation;
                            }
                        }
                        $selectedExamCompilations = isset($examCompilationIDs) ? $examCompilationIDs : array();
                        echo form_multiselect("examcompilationIDs[]", $examcompilationArray, set_value("examcompilationIDs", $selectedExamCompilations), "id='examcompilationIDs' class='form-control select2'");
                    ?>
                </div>
                <div class="form-group col-sm-4" id="examRankingDiv">
                    <label><?=$this->lang->line("examtranscriptreport_examranking")?></label>
                    <?php
                        $examrankingArray = array('0' => $this->lang->line("examtranscriptreport_please_select"));
                        if(customCompute($examrankings)) {
                            foreach ($examrankings as $examranking) {
                                $examrankingArray[$examranking->examrankingID] = $examranking->name;
                            }
                        }
                        echo form_dropdown("examrankingID", $examrankingArray, set_value("examrankingID"), "id='examrankingID' class='form-control select2'");
                    ?>
                </div>
                <div class="form-group col-sm-4" id="sectionDiv">
                    <label><?=$this->lang->line("examtranscriptreport_section")?></label>
                    <?php
                        $sectionArray[0] = $this->lang->line("examtranscriptreport_please_select");
                        echo form_dropdown("sectionID", $sectionArray, set_value("sectionID"), "id='sectionID' class='form-control select2'");
                     ?>
                </div>
                <div class="form-group col-sm-4" id="studentDiv">
                    <label><?=$this->lang->line("examtranscriptreport_student")?></label>
                    <?php
                        $studentArray[0] = $this->lang->line("examtranscriptreport_please_select");
                        echo form_dropdown("studentID", $studentArray, set_value("studentID"), "id='studentID' class='form-control select2'");
                     ?>
                </div>
                <div class="col-sm-4">
                    <div class="checkbox" style="margin-top:10px;">
                        <label>
                            <input type="checkbox" id="include_guardian" name="include_guardian" value="1" checked>
                            <?=$this->lang->line('examtranscriptreport_include_guardian')?>
                        </label>
                    </div>
                    <div class="checkbox" style="margin-top:5px;">
                        <label>
                            <input type="checkbox" id="include_address" name="include_address" value="1" checked>
                            <?=$this->lang->line('examtranscriptreport_include_address')?>
                        </label>
                    </div>
                    <div class="checkbox" style="margin-top:5px;">
                        <label>
                            <input type="checkbox" id="include_student_address" name="include_student_address" value="1" checked>
                            <?=$this->lang->line('examtranscriptreport_include_student_address')?>
                        </label>
                    </div>
                    <div class="checkbox" style="margin-top:5px;">
                        <label>
                            <input type="checkbox" id="show_subject_position" name="show_subject_position" value="1" checked>
                            <?=$this->lang->line('examtranscriptreport_show_subject_position')?>
                        </label>
                    </div>
                    <div class="checkbox" style="margin-top:5px;">
                        <label>
                            <input type="checkbox" id="show_class_position" name="show_class_position" value="1" checked>
                            <?=$this->lang->line('examtranscriptreport_show_class_position')?>
                        </label>
                    </div>
                    <button id="get_examtranscriptreport" class="btn btn-success" style="margin-top:15px;"> <?=$this->lang->line("examtranscriptreport_submit")?></button>
                </div>
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_examtranscriptreport"></div>

<?php $maxDatasets = isset($datasetLimit) ? (int) $datasetLimit : 6; ?>

<script type="text/javascript">
    var datasetLimit = <?=$maxDatasets;?>;
    var datasetLimitMessage = <?=json_encode(sprintf($this->lang->line('examtranscriptreport_dataset_limit'), $maxDatasets));?>;

    function normalizeFieldId(fieldName) {
        return (fieldName || '').replace(/\[\]/g, '');
    }

    function getDatasetMode() {
        return $('input[name="datasetMode"]:checked').val() || 'exam';
    }

    function isExamComparisonEnabled() {
        return $('#enableExamComparison').is(':checked');
    }

    function isExamCompilationComparisonEnabled() {
        return $('#enableExamCompilationComparison').is(':checked');
    }

    function isExamSelectorEnabled() {
        if($('#classesID').val() === '0') {
            return false;
        }
        return getDatasetMode() === 'exam' || isExamComparisonEnabled();
    }

    function isCompilationSelectorEnabled() {
        if($('#classesID').val() === '0') {
            return false;
        }
        return getDatasetMode() === 'exam_compilation' || isExamCompilationComparisonEnabled();
    }

    function getSanitizedSelection($select) {
        var values = $select.val() || [];
        if(!Array.isArray(values)) {
            values = [values];
        }
        return values.filter(function(value) {
            return value !== null && value !== '' && value !== '0';
        });
    }

    function getDatasetSelectionCount() {
        var count = 0;
        if(isExamSelectorEnabled()) {
            count += getSanitizedSelection($('#examID')).length;
        }
        if(isCompilationSelectorEnabled()) {
            count += getSanitizedSelection($('#examcompilationIDs')).length;
        }
        return count;
    }

    function enforceDatasetLimit(e) {
        var $select = $(this);
        if(($select.is('#examID') && !isExamSelectorEnabled()) || ($select.is('#examcompilationIDs') && !isCompilationSelectorEnabled())) {
            return;
        }

        if(getDatasetSelectionCount() > datasetLimit) {
            var selectedId = e.params && e.params.data ? e.params.data.id : null;
            if(selectedId !== null) {
                var values = $select.val() || [];
                values = values.filter(function(value) { return value !== selectedId; });
                $select.val(values).trigger('change.select2');
            }

            if(typeof toastr !== 'undefined') {
                toastr['warning'](datasetLimitMessage);
            } else {
                alert(datasetLimitMessage);
            }
        }
        updateDatasetErrorState();
    }

    function sanitizeNoneOnSelect($select, selectedId) {
        var values = $select.val() || [];
        if(!Array.isArray(values)) {
            values = [values];
        }
        var sanitizedValues = values.slice();

        if(selectedId === '0') {
            sanitizedValues = ['0'];
        } else if(values.indexOf('0') !== -1) {
            sanitizedValues = values.filter(function(value) { return value !== '0'; });
        }

        var changed = (sanitizedValues.length !== values.length) || sanitizedValues.some(function(value, index) { return value !== values[index]; });
        if(changed) {
            $select.val(sanitizedValues).trigger('change.select2');
        }
        updateDatasetErrorState();
    }

    function clearSelect($select) {
        var currentValues = $select.val();
        if(currentValues !== null && (Array.isArray(currentValues) ? currentValues.length : currentValues !== '')) {
            $select.val(null).trigger('change.select2');
        }
    }

    function toggleExamSelector(visible) {
        if(visible) {
            if($('#classesID').val() !== '0') {
                $('#examDiv').show();
            }
        } else {
            clearSelect($('#examID'));
            $('#examDiv').hide();
            $('#examDiv').removeClass('has-error');
        }
    }

    function toggleCompilationSelector(visible) {
        if(visible) {
            if($('#classesID').val() !== '0') {
                $('#examCompilationDiv').show();
            }
        } else {
            clearSelect($('#examcompilationIDs'));
            $('#examCompilationDiv').hide();
            $('#examCompilationDiv').removeClass('has-error');
        }
    }

    function applyDatasetModeState() {
        var mode = getDatasetMode();
        var classSelected = $('#classesID').val() !== '0';

        if(!classSelected) {
            toggleExamSelector(false);
            toggleCompilationSelector(false);
            $('#examComparisonToggleContainer').hide();
            $('#examCompilationToggleContainer').hide();
            $('#enableExamComparison').prop('checked', false);
            $('#enableExamCompilationComparison').prop('checked', false);
            updateDatasetErrorState();
            return;
        }

        if(mode === 'exam') {
            $('#examDiv').show();
            $('#examCompilationToggleContainer').show();
            $('#examComparisonToggleContainer').hide();
            if($('#enableExamComparison').is(':checked')) {
                $('#enableExamComparison').prop('checked', false);
                toggleExamSelector(true);
            }

            if($('#enableExamCompilationComparison').is(':checked')) {
                $('#examCompilationDiv').show();
            } else {
                toggleCompilationSelector(false);
            }
        } else {
            $('#examCompilationDiv').show();
            $('#examComparisonToggleContainer').show();
            $('#examCompilationToggleContainer').hide();
            if($('#enableExamCompilationComparison').is(':checked')) {
                $('#enableExamCompilationComparison').prop('checked', false);
                toggleCompilationSelector(true);
            }

            if($('#enableExamComparison').is(':checked')) {
                $('#examDiv').show();
            } else {
                toggleExamSelector(false);
            }
        }

        updateDatasetErrorState();
    }

    function updateDatasetErrorState() {
        var mode = getDatasetMode();
        var examCount = getSanitizedSelection($('#examID')).length;
        var compilationCount = getSanitizedSelection($('#examcompilationIDs')).length;

        if(mode === 'exam') {
            if(examCount > 0) {
                $('#examDiv').removeClass('has-error');
            }
            if(!isCompilationSelectorEnabled() || compilationCount > 0) {
                $('#examCompilationDiv').removeClass('has-error');
            }
        } else {
            if(compilationCount > 0) {
                $('#examCompilationDiv').removeClass('has-error');
            }
            if(!isExamSelectorEnabled() || examCount > 0) {
                $('#examDiv').removeClass('has-error');
            }
        }
    }

    $(function(){
        $('#schoolyearID').select2();
        $('#classesID').select2();
        $('#sectionID').select2();
        $('#studentID').select2();
        $('#examrankingID').select2();

        $('#examID').select2({
            placeholder: "<?=$this->lang->line('examtranscriptreport_please_select')?>",
            closeOnSelect: false,
            width: '100%',
            maximumSelectionLength: datasetLimit
        }).on('select2:select', function(e) {
            sanitizeNoneOnSelect($(this), e.params && e.params.data ? e.params.data.id : null);
            enforceDatasetLimit.call(this, e);
        }).on('select2:unselect', function(e) {
            enforceDatasetLimit.call(this, e);
        }).on('change', function() {
            updateDatasetErrorState();
        });

        $('#examcompilationIDs').select2({
            placeholder: "<?=$this->lang->line('examtranscriptreport_please_select')?>",
            closeOnSelect: false,
            width: '100%',
            allowClear: true,
            maximumSelectionLength: datasetLimit
        }).on('select2:select', function(e) {
            sanitizeNoneOnSelect($(this), e.params && e.params.data ? e.params.data.id : null);
            enforceDatasetLimit.call(this, e);
        }).on('select2:unselect', function(e) {
            enforceDatasetLimit.call(this, e);
        }).on('change', function() {
            updateDatasetErrorState();
        });

        $('#classesID').val('0');
        $('#examID').val(null).trigger('change');
        $('#sectionID').val('0');
        $('#studentID').val('0');
        $('#examcompilationIDs').val(null).trigger('change');
        resetExamRanking();

        applyDatasetModeState();

        $('#sectionDiv').hide('slow');
        $('#studentDiv').hide('slow');
    });

    $('input[name="datasetMode"]').on('change', function() {
        applyDatasetModeState();
        updateDatasetErrorState();
    });

    $('#enableExamComparison').on('change', function() {
        if($(this).is(':checked')) {
            if($('#classesID').val() !== '0') {
                $('#examDiv').show();
            }
        } else {
            toggleExamSelector(false);
        }
        updateDatasetModeStateIfNeeded();
        updateDatasetErrorState();
    });

    $('#enableExamCompilationComparison').on('change', function() {
        if($(this).is(':checked')) {
            if($('#classesID').val() !== '0') {
                $('#examCompilationDiv').show();
            }
        } else {
            toggleCompilationSelector(false);
        }
        updateDatasetModeStateIfNeeded();
        updateDatasetErrorState();
    });

    function updateDatasetModeStateIfNeeded() {
        applyDatasetModeState();
    }

    $('#include_guardian').on('change', function() {
        if($(this).is(':checked')) {
            $('#include_address').prop('disabled', false);
        } else {
            $('#include_address').prop('checked', false).prop('disabled', true);
        }
    }).trigger('change');

    $(document).on('change',"#classesID", function() {
        $('#load_examtranscriptreport').html("");
        var classesID = $(this).val();

        if(classesID == '0') {
            $('#sectionDiv').hide('slow');
            $('#studentDiv').hide('slow');
            $('#examID').html('').trigger('change');
            $('#examcompilationIDs').val(null).trigger('change');
            $('#sectionID').html('<option value="0">'+"<?=$this->lang->line("examtranscriptreport_please_select")?>"+'</option>');
            $('#sectionID').val('0');
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("examtranscriptreport_please_select")?>"+'</option>');
            $('#studentID').val('0');
            resetExamRanking();
            applyDatasetModeState();
        } else {
            $('#sectionDiv').show('slow');
            $('#studentDiv').show('slow');

            $.ajax({
                type: 'POST',
                url: "<?=base_url('examtranscriptreport/getExam')?>",
                data: {"classesID" : classesID},
                dataType: "html",
                success: function(data) {
                   $('#examID').html(data).trigger('change');
                   applyDatasetModeState();
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?=base_url('examtranscriptreport/getSection')?>",
                data: {"classesID" : classesID},
                dataType: "html",
                success: function(data) {
                   $('#sectionID').html(data);
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?=base_url('examtranscriptreport/getStudent')?>",
                data: {"classesID" : classesID, "schoolyearID" : $('#schoolyearID').val()},
                dataType: "html",
                success: function(data) {
                   $('#studentID').html(data);
                }
            });

            loadExamRanking(classesID);
            applyDatasetModeState();
        }
    });

    $(document).on('change',"#sectionID", function() {
        $('#load_examtranscriptreport').html("");
        var classesID = $('#classesID').val();
        var sectionID = $('#sectionID').val();

        if(sectionID == '0') {
            $('#studentDiv').hide('slow');
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("examtranscriptreport_please_select")?>"+'</option>');
            $('#studentID').val('0');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('examtranscriptreport/getStudent')?>",
                data: {"classesID" : classesID,"sectionID" : sectionID, "schoolyearID" : $('#schoolyearID').val()},
                dataType: "html",
                success: function(data) {
                   $('#studentID').html(data);
                }
            });
        }
    });

    $(document).on('change', '#schoolyearID', function() {
        $('#studentID').html('<option value="0">'+"<?=$this->lang->line("examtranscriptreport_please_select")?>"+'</option>');
        $('#studentID').val('0');
        resetExamRanking();

        if($('#classesID').val() !== '0') {
            $('#classesID').trigger('change');
        }
    });

    $(document).on('click','#get_examtranscriptreport', function() {
        $('#load_examtranscriptreport').html("");
        var error = 0;
        var datasetMode = getDatasetMode();
        var examComparisonEnabled = isExamComparisonEnabled();
        var examCompilationComparisonEnabled = isExamCompilationComparisonEnabled();

        var sanitizedExamSelection = getSanitizedSelection($('#examID'));
        var sanitizedCompilationSelection = getSanitizedSelection($('#examcompilationIDs'));

        if(!isExamSelectorEnabled()) {
            sanitizedExamSelection = [];
        }

        if(!isCompilationSelectorEnabled()) {
            sanitizedCompilationSelection = [];
        }

        var field = {
            'examID'                   : sanitizedExamSelection,
            'classesID'                : $('#classesID').val(),
            'sectionID'                : $('#sectionID').val(),
            'studentID'                : $('#studentID').val(),
            'examcompilationIDs'       : sanitizedCompilationSelection,
            'examrankingID'            : $('#examrankingID').val(),
            'schoolyearID'             : $('#schoolyearID').val(),
            'include_guardian'         : $('#include_guardian').is(':checked') ? 1 : 0,
            'include_address'          : $('#include_address').is(':checked') ? 1 : 0,
            'include_student_address'  : $('#include_student_address').is(':checked') ? 1 : 0,
            'show_subject_position'    : $('#show_subject_position').is(':checked') ? 1 : 0,
            'show_class_position'      : $('#show_class_position').is(':checked') ? 1 : 0,
            'datasetMode'              : datasetMode,
            'enableExamComparison'     : examComparisonEnabled ? 1 : 0,
            'enableExamCompilationComparison' : examCompilationComparisonEnabled ? 1 : 0
        };

        if(datasetMode === 'exam') {
            if(sanitizedExamSelection.length === 0) {
                $('#examDiv').addClass('has-error');
                error++;
            } else {
                $('#examDiv').removeClass('has-error');
            }
            if(examCompilationComparisonEnabled && sanitizedCompilationSelection.length === 0) {
                $('#examCompilationDiv').addClass('has-error');
            } else {
                $('#examCompilationDiv').removeClass('has-error');
            }
        } else {
            if(sanitizedCompilationSelection.length === 0) {
                $('#examCompilationDiv').addClass('has-error');
                error++;
            } else {
                $('#examCompilationDiv').removeClass('has-error');
            }
            if(examComparisonEnabled && sanitizedExamSelection.length === 0) {
                $('#examDiv').addClass('has-error');
            } else if(!examComparisonEnabled) {
                $('#examDiv').removeClass('has-error');
            }
        }

        if (field['classesID'] == 0) {
            $('#classesDiv').addClass('has-error');
            error++;
        } else {
            $('#classesDiv').removeClass('has-error');
        }

        if (field['schoolyearID'] == 0) {
            $('#schoolyearDiv').addClass('has-error');
            error++;
        } else {
            $('#schoolyearDiv').removeClass('has-error');
        }

        if (error == 0) {
            makingPostDataPreviousofAjaxCall(field);
        }
    });

    function loadExamRanking(classesID) {
        if(classesID && classesID !== '0') {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('examtranscriptreport/getExamranking')?>",
                data: {"classesID" : classesID},
                dataType: "html",
                success: function(data) {
                    $('#examrankingID').html(data);
                    $('#examrankingID').val('0').trigger('change');
                }
            });
        } else {
            resetExamRanking();
        }
    }

    function resetExamRanking() {
        var defaultOption = '<option value="0">'+"<?=$this->lang->line("examtranscriptreport_please_select")?>"+'</option>';
        $('#examrankingID').html(defaultOption);
        $('#examrankingID').val('0').trigger('change');
    }

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('examtranscriptreport/getExamtranscript')?>",
            data: passData,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        $('#examDiv, #examCompilationDiv').removeClass('has-error');
        var datasetMode = passData && passData.datasetMode ? passData.datasetMode : getDatasetMode();
        var examComparisonEnabled = passData && passData.enableExamComparison ? parseInt(passData.enableExamComparison, 10) === 1 : isExamComparisonEnabled();
        var examCompilationComparisonEnabled = passData && passData.enableExamCompilationComparison ? parseInt(passData.enableExamCompilationComparison, 10) === 1 : isExamCompilationComparisonEnabled();

        if(response.status) {
            $('#load_examtranscriptreport').html(response.render);
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    var fieldId = normalizeFieldId(key);
                    $('#'+fieldId).parent().removeClass('has-error');
                }
            }
            updateDatasetErrorState();
        } else {
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    var fieldId = normalizeFieldId(key);
                    $('#'+fieldId).parent().removeClass('has-error');
                }
            }

            for (var key in response) {
                if (response.hasOwnProperty(key)) {
                    var fieldId = normalizeFieldId(key);
                    $('#'+fieldId).parent().addClass('has-error');
                }
            }

            if(response['examID[]'] || response['examcompilationIDs[]']) {
                if(datasetMode === 'exam' || examComparisonEnabled) {
                    $('#examDiv').addClass('has-error');
                }
                if(datasetMode === 'exam_compilation' || examCompilationComparisonEnabled) {
                    $('#examCompilationDiv').addClass('has-error');
                }
            }

            if(response.message) {
                if(datasetMode === 'exam' || examComparisonEnabled) {
                    $('#examDiv').addClass('has-error');
                }
                if(datasetMode === 'exam_compilation' || examCompilationComparisonEnabled) {
                    $('#examCompilationDiv').addClass('has-error');
                }
                if(typeof toastr !== 'undefined') {
                    toastr['error'](response.message);
                } else {
                    alert(response.message);
                }
            }
        }
    }
</script>
