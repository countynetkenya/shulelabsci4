<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-productsalereport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_stockreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group col-sm-3" id="schootermDiv">
                    <label for="schooltermID"><?=$this->lang->line("stockreport_schoolterm")?></label>
                    <?php
                    $schooltermArray = array("" => $this->lang->line("stockreport_please_select"));
                    if(customCompute($schoolterms)) {
                        foreach ($schoolterms as $schoolterm) {
                            $schooltermArray[$schoolterm->schooltermID] = $schoolterm->schooltermtitle;
                        }
                    }
                    echo form_dropdown("schooltermID", $schooltermArray, set_value("schooltermID"), "id='schooltermID' class='form-control select2'");
                    ?>
                </div>

                <div class="form-group col-sm-3" id="yearDiv">
                    <label for="year"><?=$this->lang->line("stockreport_year")?></label>
                    <?php
                    $yearArray = array(0 => $this->lang->line("stockreport_please_select"));
                    $thisYear = (int)date('Y');
                    for ($i = $thisYear; $i >= $thisYear-10; $i--) {
                        $yearArray[$i] = $i;
                    }
                    echo form_dropdown("year", $yearArray, set_value("year"), "id='year' class='form-control select2'");
                    ?>
                </div>

                <div class="col-sm-3">
                    <button id="get_stockreport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("stockreport_submit")?></button>
                </div>
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_stockreport"></div>

<script type="text/javascript">

    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('productsaleitemreport_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    $('.select2').select2();

    $('#get_stockreport').click(function() {
        var schooltermID = $('#schooltermID').val();
        var year = $('#year').val();
        var error = 0;

        var field = {
            'schooltermID': schooltermID,
            'year': year
        };

        if(error == 0 ) {
            makingPostDataPreviousofAjaxCall(field);
        }
    });

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('stockreport/getStockReport')?>",
            data: passData,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        if(response.status) {
            $('#load_stockreport').html(response.render);
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }
        } else {
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }

            for (var key in response) {
                if (response.hasOwnProperty(key)) {
                    $('#'+key).parent().addClass('has-error');
                }
            }
        }
    }

</script>
