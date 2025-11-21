<?php

declare(strict_types=1);

/**
 * Migration command shim that allows invoking CodeIgniter 4's
 * Spark tooling from lightweight PHP entry points (e.g. `php migrate/latest`).
 *
 * The script works whether the CI4 runtime lives inside the monorepo
 * (`ci4/bin/migrate/...`) or has been copied into its own repository
 * (`bin/migrate/...`).
 */

if (!isset($segments) || !is_array($segments) || $segments === []) {
    fwrite(STDERR, "Migration CLI bootstrap requires predefined segments.\n");
    exit(1);
}

$extraArguments = array_slice($_SERVER['argv'] ?? [], 1);

$ci4Root = realpath(__DIR__ . '/../..');
if ($ci4Root === false || !is_dir($ci4Root)) {
    $ci4Root = realpath(__DIR__ . '/..');
}

if ($ci4Root === false || !is_dir($ci4Root)) {
    fwrite(STDERR, "Unable to resolve the CI4 project root for migrations.\n");
    exit(1);
}

$sparkCandidates = [
    $ci4Root . DIRECTORY_SEPARATOR . 'spark',
    dirname($ci4Root) . DIRECTORY_SEPARATOR . 'spark',
];

$spark = null;
foreach ($sparkCandidates as $candidate) {
    if (is_file($candidate)) {
        $spark = $candidate;
        break;
    }
}

if ($spark === null) {
    fwrite(STDERR, "Unable to locate the Spark CLI entry point.\n");
    exit(1);
}

$phpBinary = PHP_BINARY ?: 'php';

$command = array_merge([$phpBinary, $spark], $segments, $extraArguments);

$descriptorSpec = [
    0 => STDIN,
    1 => STDOUT,
    2 => STDERR,
];

$process = proc_open($command, $descriptorSpec, $pipes, $ci4Root);
if (!is_resource($process)) {
    fwrite(STDERR, "Failed to launch Spark CLI process.\n");
    exit(1);
}

$status = proc_close($process);
exit($status);
