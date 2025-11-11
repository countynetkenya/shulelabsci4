<?php
/** @var array<string, mixed> $snapshotTelemetry */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Operations Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 2rem;
            color: #0f172a;
        }
        header {
            margin-bottom: 2rem;
        }
        h1 {
            margin: 0 0 0.25rem;
        }
        .lead {
            margin: 0;
            color: #475569;
        }
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
        }
        .metric-card h2 {
            margin: 0;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.08em;
        }
        .metric-card p {
            margin: 0.5rem 0 0;
            font-size: 2rem;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }
        th, td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.05em;
        }
        tbody tr:hover {
            background-color: #f1f5f9;
        }
        .pill {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 0.75rem;
        }
        .failures {
            margin-top: 2rem;
        }
        .failures h2 {
            margin-bottom: 1rem;
        }
        .failure-item {
            background-color: #fff7ed;
            border-left: 4px solid #fb923c;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            border-radius: 8px;
        }
        .failure-item strong {
            display: block;
            margin-bottom: 0.3rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Operations Dashboard</h1>
        <p class="lead">Mobile snapshot telemetry from <?= esc($snapshotTelemetry['window']['start'] ?? 'n/a') ?> to <?= esc($snapshotTelemetry['window']['end'] ?? 'n/a') ?> (<?= esc($snapshotTelemetry['window']['hours'] ?? 0) ?>h window).</p>
    </header>

    <section class="metrics">
        <article class="metric-card">
            <h2>Snapshots Issued</h2>
            <p><?= esc($snapshotTelemetry['totals']['issued'] ?? 0) ?></p>
        </article>
        <article class="metric-card">
            <h2>Snapshots Verified</h2>
            <p><?= esc($snapshotTelemetry['totals']['verified'] ?? 0) ?></p>
        </article>
        <article class="metric-card">
            <h2>Verification Failures</h2>
            <p><?= esc($snapshotTelemetry['totals']['failed'] ?? 0) ?></p>
        </article>
    </section>

    <section>
        <h2>Tenant Activity</h2>
        <?php if (empty($snapshotTelemetry['tenants'])) : ?>
            <p>No telemetry captured for the selected window.</p>
        <?php else : ?>
            <table aria-label="Mobile snapshot activity by tenant">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Issued</th>
                        <th>Verified</th>
                        <th>Failed</th>
                        <th>Verification Rate</th>
                        <th>Failure Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($snapshotTelemetry['tenants'] as $tenant) : ?>
                        <tr>
                            <td><span class="pill"><?= esc($tenant['tenant_id'] ?? 'unknown') ?></span></td>
                            <td><?= esc($tenant['issued'] ?? 0) ?></td>
                            <td><?= esc($tenant['verified'] ?? 0) ?></td>
                            <td><?= esc($tenant['failed'] ?? 0) ?></td>
                            <td><?= esc(number_format((float) ($tenant['verification_rate'] ?? 0), 2)) ?></td>
                            <td><?= esc(number_format((float) ($tenant['failure_rate'] ?? 0), 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="failures">
        <h2>Recent Verification Failures</h2>
        <?php if (empty($snapshotTelemetry['recent_failures'])) : ?>
            <p>All verification checks are currently passing.</p>
        <?php else : ?>
            <?php foreach ($snapshotTelemetry['recent_failures'] as $failure) : ?>
                <div class="failure-item">
                    <strong>Snapshot <?= esc($failure['snapshot_id'] ?? 'n/a') ?> · Tenant <?= esc($failure['tenant_id'] ?? 'unknown') ?></strong>
                    <span>Reason: <?= esc($failure['reason'] ?? 'unknown') ?></span><br>
                    <span>Occurred: <?= esc($failure['occurred_at'] ?? '') ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</body>
</html>
