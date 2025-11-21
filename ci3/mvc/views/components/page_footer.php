        </div>
        <script type="text/javascript" src="<?php echo base_url('assets/bootstrap/bootstrap.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/inilabs/style.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jquery.dataTables.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/dataTables.buttons.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jszip.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/pdfmake.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/vfs_fonts.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/buttons.html5.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/dataTables.bootstrap.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/inilabs/inilabs.js'); ?>"></script>
        <script type="text/javascript">
          $(document).ready(function () {
            $(document).ajaxStart(function () {
              $("#loading").show();
            }).ajaxStop(function () {
              $("#loading").hide();
            });
          });

          $(document).ready(function () {
            let table = $('#example3, #example1, #example2, #example4').DataTable({
              dom : 'Bfrtip',
			        pageLength: 100,
              buttons : [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
              ],
              search : false,
              "autoWidth": false
            });

            let table6 = $('#example6').DataTable({
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
            }).on('click', 'tbody tr', function () {
              $(this).toggleClass('active');
              $(".checkAll").prop('checked', false);
              if (table6.rows('.active').data().length > 0) {
                $("input[type='button']").prop('disabled', false);
              } else {
                $("input[type='button']").prop('disabled', true);
              }
            });

            let table7 = $('#example7').DataTable({
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
            }).on('click', 'tbody tr', function () {
              $(this).toggleClass('active');
              $(".checkAll").prop('checked', false);
              if (table7.rows('.active').data().length > 0) {
                $("input[type='button']").prop('disabled', false);
              } else {
                $("input[type='button']").prop('disabled', true);
              }
            });

            let table8 = $('#example8').DataTable({
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
            }).on('click', 'tbody tr', function () {
              $(this).toggleClass('active');
              $(".checkAll").prop('checked', false);
              if (table8.rows('.active').data().length > 0) {
                $("input[type='button']").prop('disabled', false);
              } else {
                $("input[type='button']").prop('disabled', true);
              }
            });

            // Custom filtering function which will search data in column four between two values
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                let date = new Date(data[4]);
                let min = $("#dateFrom").val();
                let max = $("#dateTo").val();
                let searchDate = $("#date").val();
                let searchStatus = $("#status").val();

                // Create a formatter using the "en-CA" locale
                let dateFormatter = Intl.DateTimeFormat('en-CA');
                let status = data[3];
                try {
                  date = dateFormatter.format(date);
                }
                catch(err) {
                  date = null;
                }

                if(settings.nTable.id === "example1" || settings.nTable.id === "example6" || settings.nTable.id === "example7" || settings.nTable.id === "example8") {
                  if (
                    (min === "" && max === "") ||
                    (max !== "" && min === "" && date <= max) ||
                    (min !== "" && min <= date && max === "") ||
                    (min !== "" && max !== "" && min <= date && date <= max)
                  ) {
                    return true;
                  }
                  return false;
                }
                else if(settings.nTable.id === "example4") {
                  if ((searchDate === "" && searchStatus === "") || (searchDate === date && searchStatus === status) || (searchStatus === "" && searchDate === date) || (searchStatus === status && searchDate == "")
                  ) {
                    return true;
                  }
                  return false;
                }
                else {
                  return true;
                }
            });

            // Refilter the table
            document.querySelectorAll('#dateFrom, #dateTo, #date').forEach((el) => {
                el.addEventListener('change', () => table.draw(), false);
            });

            document.querySelectorAll('#dateFrom, #dateTo').forEach((el) => {
              el.addEventListener('change', () => table6.draw(), false);
              el.addEventListener('change', () => table7.draw(), false);
              el.addEventListener('change', () => table8.draw(), false);
            });

            $('#status').on("change", function() {
              table.draw();
            });

            $(".checkAll").on('click', function (e) {
              var checkbox = $(this);
              var tableID = $(this).closest('table').attr('id');
              if(tableID == "example6") {
                table6.rows((idx,data,node)=>{
                  if (checkbox.prop('checked'))
                    $(node).addClass('active');
                  else
                    $(node).removeClass('active');
                });

                if (table6.rows('.active').data().length > 0) {
                  $("input[id='syncButton']").prop('disabled', false);
                } else {
                  $("input[id='syncButton']").prop('disabled', true);
                }
              }
              else if(tableID == "example7") {
                table7.rows((idx,data,node)=>{
                  if (checkbox.prop('checked'))
                    $(node).addClass('active');
                  else
                    $(node).removeClass('active');
                });

                if (table7.rows('.active').data().length > 0) {
                  $("input[id='syncButton']").prop('disabled', false);
                } else {
                  $("input[id='syncButton']").prop('disabled', true);
                }
              }
              else if(tableID == "example8") {
                table8.rows((idx,data,node)=>{
                  if (checkbox.prop('checked'))
                    $(node).addClass('active');
                  else
                    $(node).removeClass('active');
                });

                if (table8.rows('.active').data().length > 0) {
                  $("input[id='syncButton']").prop('disabled', false);
                } else {
                  $("input[id='syncButton']").prop('disabled', true);
                }
              }
            });

            $("input[id='syncButton']").on('click', function (e) {
              var ids = [];
              var button = $(this);
              button.attr('disabled', 'disabled');

              var tableID = $('#sync').find(".tab-pane.active").find('.table').attr('id');
              var type;

              if(tableID == "example6") {
                table6.rows((idx,data,node)=>{
                  if ($(node).hasClass("active")) {
                    ids.push( $(node).attr("data-id") );
                  }
                });
                type = "invoice";
              }
              else if(tableID == "example7") {
                table7.rows((idx,data,node)=>{
                  if ($(node).hasClass("active")) {
                    ids.push( $(node).attr("data-id") );
                  }
                });
                type = "creditmemo";
              }
              else if(tableID == "example8") {
                table8.rows((idx,data,node)=>{
                  if ($(node).hasClass("active")) {
                    ids.push( $(node).attr("data-id") );
                  }
                });
                type = "payment";
              }
              ajaxCall(type, JSON.stringify(ids));
            });

            function ajaxCall(type, passData) {
              $.ajax({
                type: 'POST',
                url: "quickbooks/sync",
                data: {type: type, ids : passData},
                dataType: "html",
                success: function(data) {
                  var response = JSON.parse(data);
                  errrorLoader(type, response);
                },
                cache: false
              });
            }

            function errrorLoader(type, response) {
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

              $("input[id='syncButton']").removeAttr('disabled');
              const check = [];
              for (let i = 0; i < response.length; i++) {
                if(response[i].status) {
                  check.push(response[i].id);
                  toastr["success"]("Item posted successfully")
                } else {
                  toastr["error"](response[i].error)
                }
              }

              if(type == "invoice") {
                table6.rows((idx,data,node)=>{
                  $("#example6").find(".checkAll").prop('checked', false);
                  $(node).removeClass('active');
                  if(check.includes($(node).attr('data-id')))
                    table6.cell({ row: idx, column: 5 }).data('<i class="fa fa-check" aria-hidden="true" style="color:green"></i>').draw(false);
                });
              }
              else if(type == "creditmemo") {
                table7.rows((idx,data,node)=>{
                  $("#example7").find(".checkAll").prop('checked', false);
                  $(node).removeClass('active');
                  if(check.includes($(node).attr('data-id')))
                    table7.cell({ row: idx, column: 5 }).data('<i class="fa fa-check" aria-hidden="true" style="color:green"></i>').draw(false);
                });
              }
              else if(type == "payment") {
                table8.rows((idx,data,node)=>{
                  $("#example8").find(".checkAll").prop('checked', false);
                  $(node).removeClass('active');
                  if(check.includes($(node).attr('data-id')))
                    table8.cell({ row: idx, column: 5 }).data('<i class="fa fa-check" aria-hidden="true" style="color:green"></i>').draw(false);
                });
              }
            }
          });
        </script>

        <script type="text/javascript">
          $(function () {
            $("#withoutBtn").dataTable();
          });
        </script>

        <?php if ($this->session->flashdata('success')): ?>
            <script type="text/javascript">
              toastr[ "success" ]("<?=$this->session->flashdata('success');?>");
              toastr.options = {
                "closeButton" : true,
                "debug" : false,
                "newestOnTop" : false,
                "progressBar" : false,
                "positionClass" : "toast-top-right",
                "preventDuplicates" : false,
                "onclick" : null,
                "showDuration" : "500",
                "hideDuration" : "500",
                "timeOut" : "5000",
                "extendedTimeOut" : "1000",
                "showEasing" : "swing",
                "hideEasing" : "linear",
                "showMethod" : "fadeIn",
                "hideMethod" : "fadeOut"
              }
            </script>
        <?php endif ?>
        <?php if ($this->session->flashdata('error')): ?>
            <script type="text/javascript">
              toastr[ "error" ]("<?=$this->session->flashdata('error');?>");
              toastr.options = {
                "closeButton" : true,
                "debug" : false,
                "newestOnTop" : false,
                "progressBar" : false,
                "positionClass" : "toast-top-right",
                "preventDuplicates" : false,
                "onclick" : null,
                "showDuration" : "500",
                "hideDuration" : "500",
                "timeOut" : "5000",
                "extendedTimeOut" : "1000",
                "showEasing" : "swing",
                "hideEasing" : "linear",
                "showMethod" : "fadeIn",
                "hideMethod" : "fadeOut"
              }
            </script>
        <?php endif ?>

        <?php
            if ( isset($footerassets) ) {
                foreach ( $footerassets as $assetstype => $footerasset ) {
                    if ( $assetstype == 'css' ) {
                        if ( customCompute($footerasset) ) {
                            foreach ( $footerasset as $keycss => $css ) {
                                echo '<link rel="stylesheet" href="' . base_url($css) . '">' . "\n";
                            }
                        }
                    } elseif ( $assetstype == 'js' ) {
                        if ( customCompute($footerasset) ) {
                            foreach ( $footerasset as $keyjs => $js ) {
                                echo '<script type="text/javascript" src="' . base_url($js) . '"></script>' . "\n";
                            }
                        }
                    }
                }
            }
        ?>

        <script type="text/javascript">
            $("ul.sidebar-menu li").each(function() {
                if($(this).attr('class') === 'active') {
                    $(this).parents('li').addClass('active');
                }
            });

            $(document).ready(function () {
              setTimeout(function () {
                $.ajax({
                  type : 'GET',
                  dataType : "html",
                  async : false,
                  url : "<?=base_url('alert/alert')?>",
                  success : function (data) {
                    $(".my-push-message-list").html(data);
                    var alertNumber = 0;
                    $('.my-push-message-list li').each(function () {
                      alertNumber++;
                    });
                    if (alertNumber > 0) {
                      $('.my-push-message-ul').removeAttr('style');
                      $('.my-push-message-a').append('<span class="label label-danger"><lable class="alert-image">' + alertNumber + '</lable> </span>');
                      $('.my-push-message-number').html('<?=$this->lang->line("la_fs") . " "?>' + alertNumber + '<?=" " . $this->lang->line("la_ls")?>');
                    } else {
                      $('.my-push-message-ul').remove();
                    }
                  }
                });
              }, 5000);
            });
        </script>
    </body>
</html>
