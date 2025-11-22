<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Viewer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 10px; }
        .report-meta { color: #666; margin-bottom: 20px; font-size: 14px; }
        .actions { margin-bottom: 20px; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #45a049; }
        .export-btn { background: #2196F3; }
        .export-btn:hover { background: #0b7dda; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        tr:hover { background-color: #f5f5f5; }
        .filters { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .filters h3 { margin-bottom: 10px; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= esc($report['name'] ?? 'Report') ?></h1>
        <div class="report-meta">
            <p><?= esc($report['description'] ?? '') ?></p>
            <p>Type: <?= esc($report['type'] ?? 'table') ?> | Generated: <?= date('Y-m-d H:i:s') ?></p>
        </div>

        <div class="actions">
            <button onclick="refreshReport()">Refresh</button>
            <button class="export-btn" onclick="exportReport('pdf')">Export PDF</button>
            <button class="export-btn" onclick="exportReport('excel')">Export Excel</button>
            <button class="export-btn" onclick="exportReport('csv')">Export CSV</button>
        </div>

        <div class="filters">
            <h3>Filters</h3>
            <p>Filter controls will be implemented here based on report configuration</p>
        </div>

        <div id="report-content">
            <!-- Report data will be rendered here -->
            <p>Loading report data...</p>
        </div>
    </div>

    <script>
        function refreshReport() {
            location.reload();
        }

        function exportReport(format) {
            const reportId = <?= $report['id'] ?? 0 ?>;
            const tenantId = '<?= $tenant_id ?? '' ?>';
            window.location.href = `/api/reports/${reportId}/export?format=${format}&tenant_id=${tenantId}`;
        }
    </script>
</body>
</html>
