<?php

declare(strict_types=1);

/**
 * Custom bootstrap that ensures the CodeIgniter 4 test runner resolves
 * the application whether it is embedded in a monorepo under `ci4/`
 * or checked out as a standalone project.
 */

$ci4Root = realpath(__DIR__ . '/..');
if ($ci4Root === false) {
    throw new RuntimeException('Unable to resolve CI4 root directory');
}

$previousCwd = getcwd();
if ($previousCwd === false) {
    $previousCwd = $ci4Root;
}

if (!@chdir($ci4Root)) {
    throw new RuntimeException('Failed to change working directory to CI4 root: ' . $ci4Root);
}

try {
    $vendorRoots = [
        $ci4Root . '/vendor',
        dirname($ci4Root) . '/vendor',
    ];

    $bootstrap = null;

    foreach ($vendorRoots as $vendorRoot) {
        $candidate = $vendorRoot . '/codeigniter4/framework/system/Test/bootstrap.php';
        if (is_file($candidate)) {
            $bootstrap = $candidate;
            break;
        }
    }

    if ($bootstrap === null) {
        throw new RuntimeException('Unable to locate the CodeIgniter 4 test bootstrap.');
    }

    require $bootstrap;
} finally {
    @chdir($previousCwd);
}
