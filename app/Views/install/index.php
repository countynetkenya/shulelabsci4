<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'ShuleLabs Installation') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
            transition: all 0.3s;
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
            font-size: 12px;
            color: #999;
        }
        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }
        .content {
            padding: 20px 0;
        }
        .check-item {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check-item.success {
            background: #d1fae5;
            color: #065f46;
        }
        .check-item.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 ShuleLabs Installation</h1>
        <p class="subtitle">Welcome! Let's set up your school management system.</p>

        <div class="steps">
            <div class="step active" id="step-1">
                <div class="step-circle">1</div>
                <div class="step-label">Environment</div>
            </div>
            <div class="step" id="step-2">
                <div class="step-circle">2</div>
                <div class="step-label">Organization</div>
            </div>
            <div class="step" id="step-3">
                <div class="step-circle">3</div>
                <div class="step-label">Admin</div>
            </div>
            <div class="step" id="step-4">
                <div class="step-circle">✓</div>
                <div class="step-label">Complete</div>
            </div>
        </div>

        <div class="content">
            <!-- Step 1: Environment Check -->
            <div id="content-1" class="step-content">
                <h2 style="margin-bottom: 20px;">Environment Check</h2>
                <div id="checks"></div>
                <div class="text-center mt-20">
                    <button class="btn btn-primary" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 2: Organization Setup -->
            <div id="content-2" class="step-content hidden">
                <h2 style="margin-bottom: 20px;">Organization & School Setup</h2>
                <form id="org-form">
                    <div class="form-group">
                        <label>Organization Name</label>
                        <input type="text" name="org_name" placeholder="e.g., County Education Board" required>
                    </div>
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" name="school_name" placeholder="e.g., Central Primary School" required>
                    </div>
                    <div class="form-group">
                        <label>School Code</label>
                        <input type="text" name="school_code" placeholder="e.g., CPS001" required>
                    </div>
                    <div class="text-center mt-20">
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
            </div>

            <!-- Step 3: Admin User -->
            <div id="content-3" class="step-content hidden">
                <h2 style="margin-bottom: 20px;">Create Admin Account</h2>
                <form id="admin-form">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirm" required>
                    </div>
                    <div class="text-center mt-20">
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>

            <!-- Step 4: Complete -->
            <div id="content-4" class="step-content hidden">
                <div class="text-center">
                    <div style="font-size: 64px; margin-bottom: 20px;">🎉</div>
                    <h2 style="margin-bottom: 10px;">Installation Complete!</h2>
                    <p style="color: #666; margin-bottom: 30px;">
                        ShuleLabs has been successfully installed. You can now sign in with your admin account.
                    </p>
                    <a href="/auth/signin" class="btn btn-success">Go to Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;

        // Check environment on load
        window.onload = function() {
            fetch('/install/check')
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('checks');
                    Object.entries(data.checks).forEach(([key, passed]) => {
                        const div = document.createElement('div');
                        div.className = `check-item ${passed ? 'success' : 'error'}`;
                        div.innerHTML = `<span>${passed ? '✓' : '✗'}</span> ${formatCheckName(key)}`;
                        container.appendChild(div);
                    });
                });
        };

        function formatCheckName(key) {
            return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function nextStep() {
            if (currentStep < 4) {
                currentStep++;
                updateSteps();
            }
        }

        function updateSteps() {
            // Hide all content
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            // Show current content
            document.getElementById(`content-${currentStep}`).classList.remove('hidden');
            
            // Update step indicators
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById(`step-${i}`);
                step.classList.remove('active', 'completed');
                if (i < currentStep) step.classList.add('completed');
                if (i === currentStep) step.classList.add('active');
            }
        }

        // Handle org form
        document.getElementById('org-form').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/install/organization', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    nextStep();
                }
            });
        };

        // Handle admin form
        document.getElementById('admin-form').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/install/admin', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    fetch('/install/complete', { method: 'POST' })
                        .then(() => nextStep());
                }
            });
        };
    </script>
</body>
</html>
