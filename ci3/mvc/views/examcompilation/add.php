
<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-percent"></i> <?=$this->lang->line('panel_title')?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form role="form" method="post" enctype="multipart/form-data" id="examcompilationDataForm">

                  <?php
                        if(form_error('examcompilation'))
                            echo "<div class='examcompilationDiv form-group has-error' >";
                        else
                            echo "<div class='examcompilationDiv form-group' >";
                    ?>
                        <label for="examcompilation" class="control-label">
                            <?=$this->lang->line('examcompilation_examcompilation')?> <span class="text-red">*</span>
                        </label>
                        <input type="text" class="form-control" id="examcompilation" name="examcompilation" value="<?=set_value('examcompilation')?>" >
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('examcompilation'); ?>
                        </span>
                    </div>

                    <?php
                          if(form_error('compare_examID'))
                              echo "<div class='compareexamDiv form-group has-error' >";
                          else
                              echo "<div class='compareexamDiv form-group' >";
                      ?>
                        <label><?=$this->lang->line("examcompilation_compare_exam")?></label>
                        <?php
                            $examsArray['0'] = $this->lang->line("examcompilation_select_exam");
                            $examsArray['-1'] = $this->lang->line("examcompilation_select_examcompilation");
                            if(customCompute($exams)) {
                                foreach ($exams as $exam) {
                                    $examsArray[$exam->examID] = $exam->exam;
                                }
                            }
                            echo form_dropdown("compare_examID", $examsArray, set_value("compare_examID"), "id='compare_examID' class='form-control select2'");
                         ?>
                    </div>

                    <?php
                          if(form_error('compare_examcompilationID'))
                              echo "<div class='compareexamcompilationDiv form-group has-error' >";
                          else
                              echo "<div class='compareexamcompilationDiv form-group' >";
                      ?>
                        <label><?=$this->lang->line("examcompilation_compare_examcompilation")?></label>
                        <?php
                            $examcompilationsArray['0'] = $this->lang->line("examcompilation_select_examcompilation");
                            if(customCompute($examcompilations)) {
                                foreach ($examcompilations as $examcompilation) {
                                    $examcompilationsArray[$examcompilation->examcompilationID] = $examcompilation->examcompilation;
                                }
                            }
                            echo form_dropdown("compare_examcompilationID", $examcompilationsArray, set_value("compare_examcompilationID"), "id='compare_examcompilationID' class='form-control select2'");
                         ?>
                    </div>

          					<div class="form-group">
          					    <label for="note">
          							<?=$this->lang->line('examcompilation_note')?>
          						</label>
          						<textarea id="note" name="note" class="form-control"></textarea>
          					</div>

                    <input id="addExamCompilationButton" type="button" class="btn btn-success" value="<?=$this->lang->line("add_examcompilation")?>" >
                </form>
            </div>
        </div>
    </div>


    <div class="col-sm-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-list"></i> <?=$this->lang->line('exam_list')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("examcompilation/index")?>"><?=$this->lang->line('menu_examcompilation')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('menu_examcompilation')?></li>
                </ol>
            </div><!-- /.box-header -->
            <div class="box-body">
                <form class="" role="form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group <?=form_error('examID') ? 'has-error' : '' ?>" >
                                <label for="examID" class="control-label">
                                    <?=$this->lang->line("examcompilation_exam")?> <span class="text-red">*</span>
                                </label>
                                <?php
                                    $examArray = array('0' => $this->lang->line("examcompilation_select_exam"));
                                    foreach ($exams as $exam) {
                                        $examArray[$exam->examID] = $exam->exam;
                                    }
                                    echo form_dropdown("examID", $examArray, set_value("examID"), "id='examID' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('examID'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered feetype-style" style="font-size: 16px;">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('examcompilation_exam')?></th>
                                <th class="col-sm-2" ><?=$this->lang->line('examcompilation_weight')?></th>
                                <th class="col-sm-1"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody id="examList">
                        </tbody>

                        <tfoot id="examListFooter">
                            <tr>
                                <td colspan="2" style="font-weight: bold"><?=$this->lang->line('examcompilation_total')?></td>
                                <td id="totalWeight" style="font-weight: bold">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function dd(data) {
        console.log(data);
    }

    $('.select2').select2();

    $(".compareexamcompilationDiv").hide('slow');
    $('#compare_examcompilationID').val('0');

    $(document).on('change',"#compare_examID", function() {
        var compare_examID = $(this).val();
        if(compare_examID == '-1') {
          $('.compareexamcompilationDiv').show('slow');
        } else {
          $('.compareexamcompilationDiv').hide('slow');
          $('#compare_examcompilationID').val('0');
        }
    });

    function getRandomInt() {
      return Math.floor(Math.random() * Math.floor(9999999999999999));
    }

    function productItemDesign(examID, productText) {
        var randID = getRandomInt();
        if($('#examList tr:last').text() == '') {
            var lastTdNumber = 0;
        } else {
            var lastTdNumber = $("#examList tr:last td:eq(0)").text();
        }

        lastTdNumber = parseInt(lastTdNumber);
        lastTdNumber++;

        var text = '<tr id="tr_'+randID+'" examID="'+examID+'">';
            text += '<td>';
                text += lastTdNumber;
            text += '</td>';

            text += '<td>';
                text += productText;
            text += '</td>';

            text += '<td>';
                text += ('<input type="text" class="form-control change-weight" id="td_weight_id_'+randID+'" data-weight-id="'+randID+'">');
            text += '</td>';

            text += '<td>';
                text += ('<a style="margin-top:3px" href="#" class="btn btn-danger btn-sm deleteBtn" id="exam_'+randID+'" data-exam-id="'+randID+'"><i class="fa fa-trash-o"></i></a>');
            text += '</td>';
        text += '</tr>';

        return text;
    }

    $('#examID').change(function(e) {
        var examID   = $(this).val();
        if(examID != 0) {
            var examText = $(this).find(":selected").text();
            var appendData  = productItemDesign(examID, examText);
            $('#examList').append(appendData);
        }
    });

    function toFixedVal(x) {
      if (Math.abs(x) < 1.0) {
        var e = parseFloat(x.toString().split('e-')[1]);
        if (e) {
            x *= Math.pow(10,e-1);
            x = '0.' + (new Array(e)).join('0') + x.toString().substring(2);
        }
      } else {
        var e = parseFloat(x.toString().split('+')[1]);
        if (e > 20) {
            e -= 20;
            x /= Math.pow(10,e);
            x += (new Array(e+1)).join('0');
        }
      }
      return x;
    }

    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function lenChecker(data, len) {
        var retdata = 0;
        var lencount = 0;
        data = toFixedVal(data);
        if(data.length > len) {
            lencount = (data.length - len);
            data = data.toString();
            data = data.slice(0, -lencount);
            retdata = data;
        } else {
            retdata = data;
        }

        return toFixedVal(retdata);
    }

    function parseSentenceForNumber(sentence) {
        var matches = sentence.replace(/,/g, '').match(/(\+|-)?((\d+(\.\d+)?)|(\.\d+))/);
        return matches && matches[0] || null;
    }

    var globaltotalweight = 0;
    function totalInfo() {
        var i = 1;
        var j = 1;

        var totalWeight = 0;

        $('#examList tr').each(function(index, value) {
            if($(this).children().eq(2).children().val() != '' && $(this).children().eq(2).children().val() != null && $(this).children().eq(2).children().val() != '.') {
                var weight = parseFloat($(this).children().eq(2).children().val());
                totalWeight += weight;
            }
        });
        globaltotalweight = totalWeight;
        $('#totalWeight').text(totalWeight);
    }

    $(document).on('keyup', '.change-weight', function() {
        var weight = toFixedVal($(this).val());
        var weightID = $(this).attr('data-weight-id');

        if(isNumeric(weight)) {
            if(weight.length > 2) {
                weight = lenChecker(weight, 2);
                $(this).val(weight);
            }

            if(weight != '' && weight != null) {
                $(this).val(weight);
                totalInfo();
            } else {
                totalInfo();
            }
        } else {
          var weight = parseSentenceForNumber(toFixedVal($(this).val()));
          $(this).val(weight);
        }
    });

    $(document).on('click', '.deleteBtn', function(er) {
        er.preventDefault();
        var examID = $(this).attr('data-exam-id');
        $('#tr_'+examID).remove();

        var i = 1;
        $('#examList tr').each(function(index, value) {
            $(this).children().eq(0).text(i);
            i++;
        });
        totalInfo();
    });

    $(document).on('click', '#addExamCompilationButton', function() {
        var error=0;
        var field = {
            'examcompilation'                : $('#examcompilation').val(),
            'compare_examID'                 : $('#compare_examID').val(),
            'compare_examcompilationID'      : $('#compare_examcompilationID').val(),
        };

        if(field['examcompilation'] === '') {
            $('.examcompilationDiv').addClass('has-error');
            error++;
        } else {
            $('.examcompilationDiv').removeClass('has-error');
        }

        var totalsubtotal = 0;
        var examitems = $('tr[id^=tr_]').map(function(){
            if($(this).children().eq(2).children().val() != '' && $(this).children().eq(2).children().val() != null) {
                totalsubtotal += parseInt($(this).children().eq(2).children().val());
            }

            return { examID : $(this).attr('examid'), weight: $(this).children().eq(2).children().val()};
        }).get();

        if (typeof examitems == 'undefined' || examitems.length <= 1) {
            error++;
            toastr["error"]('At least 2 exam items are required.')
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "500",
                "hideDuration": "500",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        }

        if (totalsubtotal != 100) {
            error++;
            toastr["error"]('Total weight should be equal to 100.')
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "500",
                "hideDuration": "500",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        }

        examitems = JSON.stringify(examitems);

        if(error === 0) {
            $(this).attr('disabled', 'disabled');
            var formData = new FormData($('#examcompilationDataForm')[0]);
            formData.append("examitems", examitems);
            formData.append("totalsubtotal", totalsubtotal);
            makingPostDataPreviousofAjaxCall(formData);
        }
    });

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('examcompilation/add')?>",
            data: passData,
            async: true,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                errrorLoader(response);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    function errrorLoader(response) {
        if(response.status) {
            window.location = "<?=base_url("examcompilation/index")?>";
        } else {
            $('#addExamCompilationButton').removeAttr('disabled');
            $.each(response.error, function(index, val) {
                toastr["error"](val)
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
            });
        }
    }

</script>
