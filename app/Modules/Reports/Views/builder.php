<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Builder</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .builder-section { margin-bottom: 30px; }
        .builder-section h2 { color: #666; font-size: 18px; margin-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #45a049; }
        .field-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .field-item { padding: 10px; background: #f9f9f9; border-radius: 4px; cursor: pointer; }
        .field-item:hover { background: #e9e9e9; }
        .selected-fields { min-height: 100px; border: 2px dashed #ddd; border-radius: 4px; padding: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Report Builder</h1>
        
        <div class="builder-section">
            <h2>Report Information</h2>
            <div class="form-group">
                <label>Report Name</label>
                <input type="text" id="report-name" placeholder="Enter report name">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="report-description" rows="3" placeholder="Enter report description"></textarea>
            </div>
            <div class="form-group">
                <label>Report Type</label>
                <select id="report-type">
                    <option value="table">Table</option>
                    <option value="chart">Chart</option>
                    <option value="summary">Summary</option>
                </select>
            </div>
        </div>

        <div class="builder-section">
            <h2>Data Source</h2>
            <div class="form-group">
                <label>Select Module</label>
                <select id="data-source">
                    <option value="">Choose a module...</option>
                    <option value="finance">Finance</option>
                    <option value="hr">HR & Payroll</option>
                    <option value="inventory">Inventory</option>
                    <option value="learning">Learning</option>
                    <option value="library">Library</option>
                </select>
            </div>
        </div>

        <div class="builder-section">
            <h2>Available Fields</h2>
            <div class="field-list" id="available-fields">
                <p>Select a data source to see available fields</p>
            </div>
        </div>

        <div class="builder-section">
            <h2>Selected Fields</h2>
            <div class="selected-fields" id="selected-fields">
                <p>Drag and drop fields here or click to add</p>
            </div>
        </div>

        <div class="builder-section">
            <button onclick="saveReport()">Save Report</button>
            <button onclick="previewReport()">Preview</button>
            <button onclick="cancelBuilder()">Cancel</button>
        </div>
    </div>

    <script>
        // Placeholder for builder JavaScript
        function saveReport() {
            alert('Save functionality to be implemented');
        }
        
        function previewReport() {
            alert('Preview functionality to be implemented');
        }
        
        function cancelBuilder() {
            if (confirm('Are you sure you want to cancel? Unsaved changes will be lost.')) {
                window.location.href = '/';
            }
        }
    </script>
</body>
</html>
