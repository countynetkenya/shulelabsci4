<?php /* mvc/views/partials/date_filter.php */ ?>
<form method="get" class="date-filter">
  <div class="row">
    <div class="col-sm-12 col-md-3">
      <select name="preset" class="form-control" onchange="this.form.submit()">
        <option value="">Custom</option>
        <option value="today"  <?= (isset($_GET['preset'])&&$_GET['preset']=='today')?'selected':''; ?>>Today</option>
        <option value="week"   <?= (isset($_GET['preset'])&&$_GET['preset']=='week')?'selected':''; ?>>This Week</option>
        <option value="month"  <?= (isset($_GET['preset'])&&$_GET['preset']=='month')?'selected':''; ?>>This Month</option>
      </select>
    </div>
    <div class="col-sm-6 col-md-3"><input type="date" name="fromdate" value="<?=htmlspecialchars($_GET['fromdate'] ?? '')?>" class="form-control"/></div>
    <div class="col-sm-6 col-md-3"><input type="date" name="todate" value="<?=htmlspecialchars($_GET['todate'] ?? '')?>" class="form-control"/></div>
    <div class="col-sm-12 col-md-3"><button type="submit" class="btn btn-primary btn-block" style="margin-top:4px;">Apply</button></div>
  </div>
</form>
<style>@media (max-width:576px){.date-filter .row>[class*="col-"]{margin-bottom:8px}}</style>