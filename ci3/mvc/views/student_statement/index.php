<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-balance-scale"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url('dashboard/index')?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_global_payment')?></li>
        </ol>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form id="student-statement-filter" class="mb-20" method="get">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="<?php echo form_error('classesID') ? 'form-group has-error' : 'form-group'; ?>">
                                <label for="classesID" class="control-label"><?=$this->lang->line('global_classes')?></label>
                                <?php
                                    $classArray = array('0' => $this->lang->line('global_select_classes'));
                                    foreach ($classes as $classa) {
                                        $classArray[$classa->classesID] = $classa->classes;
                                    }
                                    echo form_dropdown('classesID', $classArray, set_value('classesID', $set_classesID), "id='classesID' class='form-control select2'");
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="<?php echo form_error('sectionID') ? 'form-group has-error' : 'form-group'; ?>">
                                <label for="sectionID" class="control-label"><?=$this->lang->line('global_section')?></label>
                                <?php
                                    $sectionArray = array('0' => $this->lang->line('global_select_section'));
                                    if($sections != 0) {
                                        foreach ($sections as $section) {
                                            $sectionArray[$section->sectionID] = $section->section;
                                        }
                                    }
                                    echo form_dropdown('sectionID', $sectionArray, set_value('sectionID', $set_sectionID), "id='sectionID' class='form-control select2'");
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="<?php echo form_error('studentID') ? 'form-group has-error' : 'form-group'; ?>">
                                <label for="studentID" class="control-label"><?=$this->lang->line('global_student')?></label>
                                <?php
                                    $studentArray = array('0' => $this->lang->line('global_select_student'));
                                    if(customCompute($students)) {
                                        foreach ($students as $student) {
                                            $studentArray[$student->srstudentID] = $student->srname.' - '.$this->lang->line('global_register_no').' - '.$student->srstudentID;
                                        }
                                    }
                                    echo form_dropdown('studentID', $studentArray, set_value('studentID', $set_studentID), "id='studentID' class='form-control select2'");
                                ?>
                            </div>
                        </div>

                        <?php if($usertypeID != 4 && $usertypeID != 3) {?>
                        <div class="col-md-3">
                            <div class="<?php echo form_error('parentID') ? 'form-group has-error' : 'form-group'; ?>">
                                <label for="parentID" class="control-label"><?=$this->lang->line('global_parent')?></label>
                                <?php
                                    $parentArray = array('0' => $this->lang->line('global_select_parent'));
                                    if(customCompute($parents)) {
                                        foreach ($parents as $parent) {
                                            $parentArray[$parent->parentsID] = $parent->name;
                                        }
                                    }
                                    echo form_dropdown('parentID', $parentArray, set_value('parentID', $set_parentID), "id='parentID' class='form-control select2'");
                                ?>
                            </div>
                        </div>
                        <?php }?>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="schoolYearID" class="control-label"><?=$this->lang->line('global_schoolyear')?></label>
                                <?php
                                    $schoolYearArray = array('0' => $this->lang->line('global_select_schoolyear'));
                                    foreach($schoolYears as $schoolYear) {
                                        $schoolYearArray[$schoolYear->schoolyearID] = $schoolYear->schoolyear;
                                    }
                                    echo form_dropdown('schoolYearID', $schoolYearArray, set_value('schoolYearID', $set_schoolYearID), "id='schoolYearID' class='form-control select2'");
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="schooltermID" class="control-label"><?=$this->lang->line('global_schooltermID')?></label>
                                <?php
                                    $termsArray = array('0' => $this->lang->line('global_select_schoolterm'));
                                    if(customCompute($terms)) {
                                        foreach ($terms as $term) {
                                            $termsArray[$term->schooltermID] = $term->schooltermtitle;
                                        }
                                    }
                                    echo form_dropdown('schooltermID', $termsArray, set_value('schooltermID', $set_schooltermID), "id='schooltermID' class='form-control select2'");
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="month" class="control-label"><?=$this->lang->line('global_month')?></label>
                                <input type="month" id="month" name="month" class="form-control" value="<?=$set_month?>">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="specificDate" class="control-label"><?=$this->lang->line('global_day')?></label>
                                <input type="date" id="specificDate" name="specificDate" class="form-control" value="<?=$set_specificDate?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dateFrom" class="control-label"><?=$this->lang->line('global_from')?></label>
                                <input type="date" id="dateFrom" name="dateFrom" class="form-control" value="<?=$set_dateFrom?>">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dateTo" class="control-label"><?=$this->lang->line('global_to')?></label>
                                <input type="date" id="dateTo" name="dateTo" class="form-control" value="<?=$set_dateTo?>">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="control-label" style="visibility:hidden;display:block;">&nbsp;</label>
                                <button type="submit" class="btn btn-success btn-block global_payment_search"><?=$this->lang->line('global_payment_search')?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-sm-12">
                <div class="well well-sm" id="statement-toolbar">
                    <div class="row">
                        <div class="col-sm-8 col-xs-12">
                            <button type="button" id="statement-download-pdf" class="btn btn-default" disabled>
                                <span class="fa fa-file-pdf-o"></span> <?=$this->lang->line('global_print')?> PDF
                            </button>
                            <button type="button" id="statement-download-csv" class="btn btn-default" disabled>
                                <span class="fa fa-download"></span> CSV
                            </button>
                        </div>
                        <div class="col-sm-4 col-xs-12 text-right">
                            <span id="statement-row-count" class="text-muted"></span>
                        </div>
                    </div>
                </div>

                <div id="statement-status" class="alert alert-info" style="display:none;"></div>
                <div id="statement-table-container"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    'use strict';

    var apiUrl = "<?=$studentStatementApiUrl?>";
    var csvUrl = "<?=$studentStatementCsvUrl?>";
    var pdfUrl = "<?=$studentStatementPdfUrl?>";
    var form = document.getElementById('student-statement-filter');
    var statusBox = document.getElementById('statement-status');
    var tableContainer = document.getElementById('statement-table-container');
    var rowCountEl = document.getElementById('statement-row-count');
    var csvButton = document.getElementById('statement-download-csv');
    var pdfButton = document.getElementById('statement-download-pdf');
    var currentQuery = '';

    function setButtonsEnabled(enabled) {
        csvButton.disabled = !enabled;
        pdfButton.disabled = !enabled;
        if (!enabled) {
            currentQuery = '';
        }
    }

    function updateStatus(message, type) {
        statusBox.className = 'alert alert-' + type;
        statusBox.textContent = message;
        statusBox.style.display = message ? 'block' : 'none';
    }

    function renderStatements(payload) {
        tableContainer.innerHTML = '';
        var students = payload.students || [];
        if (!students.length) {
            rowCountEl.textContent = '<?=$this->lang->line('global_total')?>: 0';
            updateStatus('<?=$this->lang->line('global_not_found')?>', 'warning');
            setButtonsEnabled(false);
            return;
        }

        updateStatus('', 'info');
        var totalRows = payload.total_rows || 0;
        rowCountEl.textContent = '<?=$this->lang->line('global_total')?>: ' + totalRows;
        setButtonsEnabled(true);

        var fragment = document.createDocumentFragment();
        students.forEach(function(student) {
            var card = document.createElement('div');
            card.className = 'panel panel-default';

            var heading = document.createElement('div');
            heading.className = 'panel-heading';
            heading.innerHTML = '<strong>' + student.student.student_name + '</strong>' +
                ' <span class="text-muted">#' + student.student.studentID + '</span>' +
                (student.student.class ? ' &middot; ' + student.student.class : '') +
                (student.student.section ? ' &middot; ' + student.student.section : '');
            card.appendChild(heading);

            var body = document.createElement('div');
            body.className = 'panel-body';

            var meta = document.createElement('p');
            meta.className = 'text-muted';
            meta.textContent = '<?=$this->lang->line('global_balance')?>: ' + student.student.closing_balance.toFixed(2) +
                ' / <?=$this->lang->line('global_debit')?>: ' + student.student.total_debit.toFixed(2) +
                ' / <?=$this->lang->line('global_credit')?>: ' + student.student.total_credit.toFixed(2);
            body.appendChild(meta);

            var table = document.createElement('table');
            table.className = 'table table-striped table-bordered table-condensed';
            var thead = document.createElement('thead');
            thead.innerHTML = '<tr>' +
                '<th><?=$this->lang->line('global_date')?></th>' +
                '<th><?=$this->lang->line('global_description')?></th>' +
                '<th><?=$this->lang->line('global_debit')?></th>' +
                '<th><?=$this->lang->line('global_credit')?></th>' +
                '<th><?=$this->lang->line('global_balance')?></th>' +
                '<th><?=$this->lang->line('global_schooltermID')?></th>' +
                '<th><?=$this->lang->line('global_month')?></th>' +
                '</tr>';
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
            student.rows.forEach(function(row) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + (row.day || '') + '</td>' +
                    '<td>' + row.description + '</td>' +
                    '<td class="text-right">' + (row.debit ? row.debit.toFixed(2) : '') + '</td>' +
                    '<td class="text-right">' + (row.credit ? row.credit.toFixed(2) : '') + '</td>' +
                    '<td class="text-right">' + row.balance.toFixed(2) + '</td>' +
                    '<td>' + (row.term || '') + '</td>' +
                    '<td>' + (row.month || '') + '</td>';
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            body.appendChild(table);
            card.appendChild(body);
            fragment.appendChild(card);
        });

        tableContainer.appendChild(fragment);
    }

    function fetchStatements() {
        updateStatus('<?=$this->lang->line('global_loading')?>...', 'info');
        setButtonsEnabled(false);
        tableContainer.innerHTML = '';
        rowCountEl.textContent = '';

        var formData = new FormData(form);
        var params = new URLSearchParams(formData);
        currentQuery = params.toString();

        fetch(apiUrl + '?' + currentQuery, {
            credentials: 'same-origin'
        }).then(function(response) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then(function(json) {
            if (json && json.data) {
                renderStatements(json.data);
            } else {
                updateStatus('<?=$this->lang->line('global_not_found')?>', 'warning');
            }
        }).catch(function(error) {
            console.error(error);
            updateStatus('<?=$this->lang->line('global_error')?>', 'danger');
        });
    }

    csvButton.addEventListener('click', function() {
        if (!currentQuery) return;
        window.location = csvUrl + '?' + currentQuery;
    });

    pdfButton.addEventListener('click', function() {
        if (!currentQuery) return;
        window.location = pdfUrl + '?' + currentQuery;
    });

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        fetchStatements();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchStatements);
    } else {
        fetchStatements();
    }
})();

$('.select2').select2();

$("#classesID").change(function() {
    var id = $(this).val();
    if(parseInt(id)) {
        if(id === '0') {
            $('#sectionID').val(0);
            $('#studentID').val(0);
            $('#parentID').val(0);
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('student_statement/sectioncall')?>",
                data: {"id" : id},
                dataType: "html",
                success: function(data) {
                    $('#sectionID').html(data);
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?=base_url('student_statement/studentcall')?>",
                data: {"classesID" : id},
                dataType: "html",
                success: function(data) {
                    $('#studentID').html(data);
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?=base_url('student_statement/parentcall')?>",
                data: {"classesID" : id},
                dataType: "html",
                success: function(data) {
                    $('#parentID').html(data);
                }
            });
        }
    }
});

$("#sectionID").change(function() {
    var id = $(this).val();
    var classesID = $('#classesID').val();
    if(parseInt(id)) {
        if(id === '0') {
            $('#sectionID').val(0);
        } else {
            if(classesID === '0') {
                $('#classesID').val(0);
            } else {
                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('student_statement/studentcall')?>",
                    data: {"classesID" : classesID, "sectionID" : id},
                    dataType: "html",
                    success: function(data) {
                        $('#studentID').html(data);
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('student_statement/parentcall')?>",
                    data: {"classesID" : classesID, "sectionID" : id},
                    dataType: "html",
                    success: function(data) {
                        $('#parentID').html(data);
                    }
                });
            }
        }
    }
});

$("#schoolYearID").change(function() {
    var id = $(this).val();
    if(parseInt(id)) {
        if(id === '0') {
            $('#schooltermID').val(0);
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('student_statement/termcall')?>",
                data: {"schoolYearID" : id},
                dataType: "html",
                success: function(data) {
                    $('#schooltermID').html(data);
                }
            });
        }
    }
});

$("#schooltermID").change(function() {
    var id = $(this).val();
    if(parseInt(id)) {
        if(id === '0') {
            $('#dateFrom').val('');
            $('#dateTo').val('');
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('student_statement/datescall')?>",
                data: {"schooltermID" : id},
                dataType: "json",
                success: function(data) {
                    $('#dateFrom').val(data.startingdate);
                    $('#dateTo').val(data.endingdate);
                }
            });
        }
    }
});
</script>
