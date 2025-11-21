<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-mailandsms"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_mailandsms')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php if(permissionChecker('mailandsms_add')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('mailandsms/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?=$this->lang->line('add_title')?>
                        </a>
                    </h5>
                <?php } ?>

                <div class="row margin-bottom">
                  <div class="col-sm-9"></div>
                  <div class="col-sm-1">
                    <input id="discardButton" type="button" class="btn btn-danger" value="<?=$this->lang->line("discard")?>" data-url="<?=base_url('mailandsms/discard')?>" disabled />
                  </div>
                  <div class="col-sm-1">
                    <input id="sendButton" type="button" class="btn btn-warning" value="<?=$this->lang->line("send")?>" data-url="<?=base_url('mailandsms/send')?>" disabled />
                  </div>
                  <div class="col-sm-1">
                    <input id="testButton" type="button" class="btn btn-success" value="<?=$this->lang->line("test")?>" data-url="<?=base_url('mailandsms/test')?>" disabled />
                  </div>
                </div>

                <div id="hide-table">
                    <table id="example5" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll" title="Select All"/></th>
                                <th><?=$this->lang->line('slno')?></th>
                                <th><?=$this->lang->line('mailandsms_usertype')?></th>
                                <th><?=$this->lang->line('mailandsms_users')?></th>
                                <th><?=$this->lang->line('mailandsms_recipient')?></th>
                                <th><?=$this->lang->line('mailandsms_type')?></th>
                                <th><?=$this->lang->line('mailandsms_dateandtime')?></th>
                                <th><?=$this->lang->line('mailandsms_message')?></th>
                                <?php if(permissionChecker('mailandsms_view')) { ?>
                                <th><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($mailandsmss)) {$i = 1; foreach($mailandsmss as $mailandsms) { ?>
                                <tr data-id="<?=$mailandsms->mailandsmsID?>">
                                    <td></td>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_usertype')?>">
                                        <?=($mailandsms->usertypeID !== NULL) ? $mailandsms->usertype : $this->lang->line('mailandsms_guest_user')?>
                                    </td>

                                    <td data-title="<?=$this->lang->line('mailandsms_users')?>">
                                        <?php
                                            if(strlen($mailandsms->users) > 36) {
                                                echo substr($mailandsms->users, 0, 36). "..";
                                            } else {
                                                echo $mailandsms->users;
                                            }
                                        ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_recipient')?>">
                                        <?php echo $mailandsms->recipient; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_type')?>">
                                        <?php echo $mailandsms->type; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_dateandtime')?>">
                                        <?php echo date("d M Y h:i:s a", strtotime($mailandsms->create_date));?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('mailandsms_message')?>">
                                        <?php echo substr(strip_tags($mailandsms->message), 0, 36). ".."; ?>
                                    </td>
                                    <?php if(permissionChecker('mailandsms_view')) { ?>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                        <?php echo btn_view('mailandsms/view/'.$mailandsms->mailandsmsID, $this->lang->line('view')) ?>
                                    </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>

            </div> <!-- col-sm-12 -->

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
$(document).ready(function () {
  const table = $('#example5').DataTable({
    dom : 'Bfrtip',
    pageLength: 100,
    buttons : [
      'copyHtml5',
      'excelHtml5',
      'csvHtml5',
      'pdfHtml5'
    ],
    "order": [],
    "columnDefs": [ {
      "targets"  : 0,
      "orderable": false,
    }],
    search : false,
    "autoWidth": false
  });

  $("#checkAll").on('click', function (e) {
    table.rows((idx,data,node)=>{
      if ($("#checkAll").prop('checked'))
        $(node).addClass('active');
      else
        $(node).removeClass('active');
    });
    checkButton();
  });

  table.on('click', 'tbody tr', function () {
    $(this).toggleClass('active');
    $("#checkAll").prop('checked', false);
    checkButton();
  });

  function checkButton() {
    if (table.rows('.active').data().length > 0) {
      $("input[type='button']").prop('disabled', false);
    } else {
      $("input[type='button']").prop('disabled', true);
    }
  }

  $("input[type='button']").on('click', function (e) {
    var ids = [];
    var button = $(this);
    button.attr('disabled', 'disabled');
    table.rows((idx,data,node)=>{
      if ($(node).hasClass("active")) {
        ids.push( $(node).attr("data-id") );
      }
    });
    ajaxCall(button, JSON.stringify(ids));
  });

  function ajaxCall(button, passData) {
    var url = button.attr("data-url");
    $.ajax({
      type: 'POST',
      url: url,
      data: {ids : passData},
      dataType: "html",
      success: function(data) {
        var response = JSON.parse(data);
        errrorLoader(button, response);
      },
      cache: false
    });
  }

  function errrorLoader(button, response) {
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
    if(response.status) {
        window.location = "<?=base_url("mailandsms/index")?>";
    } else {
      button.removeAttr('disabled');
      const remove = [];
      for (let i = 0; i < response.length; i++) {
        if(response[i].status) {
          remove.push(response[i].id);
          toastr["success"]("Message sent successfully")
        } else {
          toastr["error"](response[i].error)
        }
      }

      table.rows((idx,data,node)=>{
        $("#checkAll").prop('checked', false);
        $(node).removeClass('active');
        if(remove.includes($(node).attr('data-id')))
          $(node).remove();
      });
    }
  }
});
</script>
