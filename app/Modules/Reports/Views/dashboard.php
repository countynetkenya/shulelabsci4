<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .dashboard { max-width: 1400px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-value { font-size: 32px; font-weight: bold; color: #4CAF50; margin-bottom: 5px; }
        .stat-label { color: #666; font-size: 14px; }
        .widgets { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .widget { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .widget h2 { color: #333; font-size: 18px; margin-bottom: 15px; }
        .widget-content { min-height: 200px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #f9f9f9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Reports Dashboard</h1>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value" id="total-reports">0</div>
                <div class="stat-label">Total Reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="public-reports">0</div>
                <div class="stat-label">Public Reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="active-users">0</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="last-updated">N/A</div>
                <div class="stat-label">Last Updated</div>
            </div>
        </div>

        <div class="widgets" id="dashboard-widgets">
            <!-- Widgets will be dynamically loaded here -->
            <div class="widget">
                <h2>Recent Reports</h2>
                <div class="widget-content">
                    <p>Loading...</p>
                </div>
            </div>
            <div class="widget">
                <h2>Popular Reports</h2>
                <div class="widget-content">
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Placeholder for dashboard JavaScript
        async function loadDashboard() {
            const tenantId = 'demo-tenant'; // Replace with actual tenant ID
            try {
                const response = await fetch(`/api/reports/dashboard?tenant_id=${tenantId}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    updateDashboard(data.data);
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function updateDashboard(data) {
            // Update stats
            document.getElementById('total-reports').textContent = data.widgets.length;
            
            // Render widgets
            const widgetsContainer = document.getElementById('dashboard-widgets');
            widgetsContainer.innerHTML = '';
            
            data.widgets.forEach(widget => {
                const widgetEl = createWidget(widget);
                widgetsContainer.appendChild(widgetEl);
            });
        }

        function createWidget(widget) {
            const div = document.createElement('div');
            div.className = 'widget';
            div.innerHTML = `
                <h2>${widget.name}</h2>
                <div class="widget-content">
                    <p>Type: ${widget.type}</p>
                    <p>Data points: ${widget.metadata.count || 0}</p>
                </div>
            `;
            return div;
        }

        // Auto-refresh every 5 minutes
        setInterval(loadDashboard, 300000);
    </script>
</body>
</html>
