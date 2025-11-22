<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShuleLabs Installer - <?= esc($title ?? 'Setup') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .installer-container {
            max-width: 600px;
            width: 100%;
            margin: 2rem;
        }
        .installer-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .installer-header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }
        .installer-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        .installer-body {
            padding: 2rem;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .step.active .step-circle {
            background: #667eea;
            color: white;
        }
        .step.completed .step-circle {
            background: #10b981;
            color: white;
        }
        .step-label {
            font-size: 0.75rem;
            color: #666;
        }
        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
        }
        .alert {
            border-radius: 0.5rem;
        }
        .form-label {
            font-weight: 500;
            color: #374151;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-card">
            <div class="installer-header">
                <h1>🎓 ShuleLabs Installer</h1>
                <p><?= esc($headerText ?? 'Initial Setup Wizard') ?></p>
            </div>
            <div class="installer-body">
                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success" role="alert">
                        <?= esc(session('success')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= esc(session('error')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->has('info')): ?>
                    <div class="alert alert-info" role="alert">
                        <?= esc(session('info')) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-white">ShuleLabs CI4 - Open Source School Management System</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
