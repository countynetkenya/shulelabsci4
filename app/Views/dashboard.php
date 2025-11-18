<?php
$escape = static function ($value): string {
    if (function_exists('esc')) {
        return esc($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$csrfField = static function (): string {
    if (function_exists('csrf_field')) {
        return csrf_field();
    }

    return '';
};

$siteUrl = static function (string $path = ''): string {
    if (function_exists('site_url')) {
        return site_url($path);
    }

    $path = ltrim($path, '/');

    return '/' . $path;
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f7fb;
            margin: 0;
            padding: 2rem;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .card-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.1);
            text-decoration: none;
            color: #111827;
            transition: transform 120ms ease, box-shadow 120ms ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.15);
        }
        .card span {
            display: block;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .logout-link {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
        header form {
            margin: 0;
        }
    </style>
</head>
<body>
<header>
    <div>
        <h1 style="margin-bottom: .25rem;">Hi, <?= $escape($username ?: 'User') ?> 👋</h1>
        <p style="margin: 0; color: #6b7280;">Role: <?= $escape($role ?: 'member') ?></p>
    </div>
    <form method="post" action="<?= $siteUrl('logout') ?>">
        <?= $csrfField() ?>
        <button type="submit" class="logout-link" style="background:none;border:none;padding:0;">Log out</button>
    </form>
</header>
<section>
    <h2 style="margin-bottom: 1rem;">Explore modules</h2>
    <div class="card-grid">
        <a class="card" href="<?= $siteUrl('operations/dashboard') ?>">
            <span>Foundation</span>
            Operations dashboard
        </a>
        <a class="card" href="<?= $siteUrl('finance/ping') ?>">
            <span>Finance</span>
            Invoice health probe
        </a>
        <a class="card" href="<?= $siteUrl('hr/payroll/approvals') ?>">
            <span>HR</span>
            Payroll approvals
        </a>
        <a class="card" href="<?= $siteUrl('inventory/transfers') ?>">
            <span>Inventory</span>
            Transfer workflow
        </a>
        <a class="card" href="<?= $siteUrl('learning/moodle/grades') ?>">
            <span>Learning</span>
            Moodle sync endpoint
        </a>
        <a class="card" href="<?= $siteUrl('library/documents') ?>">
            <span>Library</span>
            Document ingestion
        </a>
        <a class="card" href="<?= $siteUrl('mobile/telemetry/snapshots') ?>">
            <span>Mobile</span>
            Snapshot telemetry
        </a>
        <a class="card" href="<?= $siteUrl('threads') ?>">
            <span>Threads</span>
            Collaboration hub
        </a>
    </div>
</section>
</body>
</html>
