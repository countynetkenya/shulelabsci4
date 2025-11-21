<?php /* mvc/views/inventory/transfers_incoming.php */ ?>
<h3>Incoming Transfers</h3>
<div class="table-responsive">
<table class="table table-striped">
  <thead><tr><th>Date</th><th>Ref</th><th>From</th><th>To</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($transfers as $t): ?>
    <tr>
      <td><?=htmlspecialchars($t->mainstockcreate_date)?></td>
      <td><?=htmlspecialchars($t->transfer_ref)?></td>
      <td><?=intval($t->stockfromwarehouseID)?></td>
      <td><?=intval($t->stocktowarehouseID)?></td>
      <td><span class="badge bg-info"><?=htmlspecialchars($t->transfer_status)?></span></td>
      <td>
        <form method="post" action="/inventory/transfer/<?=intval($t->mainstockID)?>/accept" style="display:inline"><button class="btn btn-success btn-sm">Accept</button></form>
        <form method="post" action="/inventory/transfer/<?=intval($t->mainstockID)?>/reject" style="display:inline">
          <input name="reason" placeholder="Reason" class="form-control form-control-sm" style="display:inline;width:160px"/>
          <button class="btn btn-danger btn-sm">Reject</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>