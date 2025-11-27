<!DOCTYPE html>
<html>
<head>
    <title>Finance Dashboard</title>
</head>
<body>
    <h1>Finance Dashboard</h1>
    <div>
        <p>Total Invoiced: <?= esc($total_invoiced) ?></p>
        <p>Total Collected: <?= esc($total_collected) ?></p>
        <p>Pending Invoices: <?= esc($pending_invoices) ?></p>
    </div>
</body>
</html>
