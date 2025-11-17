<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 360px;
        }
        .login-card h1 {
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
        }
        .alert {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        .alert-error {
            background: #fdecea;
            color: #b03a2e;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.65rem;
            margin-bottom: 1rem;
            border: 1px solid #cfd8dc;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            background: #1976d2;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #0f5aa5;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Welcome Back</h1>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('message')) ?>
            </div>
        <?php endif; ?>
        <form action="<?= site_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= old('username') ?>" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
