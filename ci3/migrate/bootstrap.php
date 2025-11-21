<?php
/**
 * CLI bootstrapper that maps shortcut scripts like `php migrate/latest`
 * to the canonical CodeIgniter front controller invocation
 * `php index.php migrate latest`.
 */

$ci4Bootstrap = __DIR__ . '/../ci4/bin/migrate/bootstrap.php';
if (is_file($ci4Bootstrap)) {
    require $ci4Bootstrap;
    return;
}

// Segments must be provided by the stub that includes this file.
if (!isset($segments) || !is_array($segments) || $segments === []) {
    fwrite(STDERR, "Migration CLI bootstrap requires predefined segments.\n");
    exit(1);
}

// Preserve any additional arguments passed to the shortcut script.
$extraArguments = array_slice($_SERVER['argv'], 1);

// Rebuild the argv array so CodeIgniter receives the expected segments.
$_SERVER['argv'] = array_merge(['index.php'], $segments, $extraArguments);
$_SERVER['argc'] = count($_SERVER['argv']);

require __DIR__ . '/../index.php';
