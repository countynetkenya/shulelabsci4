<?php /* mvc/views/inventory/transfers_incoming.php */ ?>
<h3>Incoming Transfers</h3>
<div class="table-responsive">
<table class="table table-striped">
  <thead><tr><th>Date</th><th>Ref</th><th>From</th><th>To</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($transfers as $t): ?>
    <tr data-mainstock-id="<?=intval($t->mainstockID)?>">
      <td><?=htmlspecialchars($t->mainstockcreate_date)?></td>
      <td><?=htmlspecialchars($t->transfer_ref)?></td>
      <td><?=intval($t->stockfromwarehouseID)?></td>
      <td><?=intval($t->stocktowarehouseID)?></td>
      <td><span class="badge bg-info"><?=htmlspecialchars($t->transfer_status)?></span></td>
      <td>
        <button class="btn btn-success btn-sm accept-btn" data-url="/inventory/transfer/<?=intval($t->mainstockID)?>/accept">Accept</button>
        <button class="btn btn-danger btn-sm reject-btn" data-url="/inventory/transfer/<?=intval($t->mainstockID)?>/reject" data-toggle="modal" data-target="#rejectModal">Reject</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectModalLabel">Reject Transfer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="rejectForm">
          <div class="form-group">
            <label for="rejectionReason" class="col-form-label">Reason:</label>
            <textarea class="form-control" id="rejectionReason" name="reason"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmRejectBtn">Confirm Reject</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // --- Accept button ---
    $('.accept-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');
        var $row = $btn.closest('tr');

        $btn.prop('disabled', true).siblings().prop('disabled', true);
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Accepting...');

        $.post(url)
            .done(function(response) {
                toastr.success('Transfer accepted successfully.');
                $row.fadeOut(500, function() { $(this).remove(); });
            })
            .fail(function() {
                toastr.error('An error occurred while accepting the transfer.');
                $btn.prop('disabled', false).siblings().prop('disabled', false);
                $btn.html('Accept');
            });
    });

    // --- Reject button ---
    var rejectUrl;
    var $rejectingRow;

    // Store context when modal is opened
    $('#rejectModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        rejectUrl = button.data('url');
        $rejectingRow = button.closest('tr');
        $('#rejectionReason').val('');
    });

    // Handle the final rejection
    $('#confirmRejectBtn').on('click', function() {
        var $modalBtn = $(this);
        var reason = $('#rejectionReason').val();

        $modalBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');

        $.post(rejectUrl, { reason: reason })
            .done(function(response) {
                $('#rejectModal').modal('hide');
                toastr.success('Transfer rejected successfully.');
                $rejectingRow.fadeOut(500, function() { $(this).remove(); });
            })
            .fail(function() {
                $('#rejectModal').modal('hide');
                toastr.error('An error occurred while rejecting the transfer.');
            })
            .always(function() {
                $modalBtn.prop('disabled', false).html('Confirm Reject');
                // Re-enable original buttons in the row
                if ($rejectingRow) {
                    $rejectingRow.find('.accept-btn, .reject-btn').prop('disabled', false);
                }
            });
    });
});
</script>