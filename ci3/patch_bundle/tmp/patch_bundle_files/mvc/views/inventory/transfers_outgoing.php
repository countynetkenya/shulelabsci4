<?php /* mvc/views/inventory/transfers_outgoing.php */ ?>
<h3>Outgoing Transfers</h3>
<div class="table-responsive">
<table class="table table-striped">
  <thead><tr><th>Date</th><th>Ref</th><th>From</th><th>To</th><th>Status</th></tr></thead>
  <tbody>
  <?php foreach($transfers as $t): ?>
    <tr>
      <td><?=htmlspecialchars($t->mainstockcreate_date)?></td>
      <td><?=htmlspecialchars($t->transfer_ref)?></td>
      <td><?=intval($t->stockfromwarehouseID)?></td>
      <td><?=intval($t->stocktowarehouseID)?></td>
      <td><span class="badge bg-info"><?=htmlspecialchars($t->transfer_status)?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>