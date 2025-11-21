#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CodeIgniter\I18n\Time;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Modules\Foundation\Services\AuditService;

$options = getopt('', [
    'task:',
    'status:',
    'message::',
    'duration::',
]);

if (! isset($options['task'], $options['status'])) {
    fwrite(STDERR, "Usage: report_scheduler.php --task=<name> --status=<ok|error> [--message=msg] [--duration=seconds]\n");
    exit(1);
}

$task = (string) $options['task'];
$status = strtolower((string) $options['status']);

if ($task === '') {
    fwrite(STDERR, "Task name is required.\n");
    exit(1);
}

if (! in_array($status, ['ok', 'error'], true)) {
    fwrite(STDERR, "Status must be 'ok' or 'error'.\n");
    exit(1);
}

$metadata = ['source' => 'scheduler'];

if (isset($options['message']) && $options['message'] !== false) {
    $metadata['message'] = (string) $options['message'];
}

if (isset($options['duration']) && $options['duration'] !== false) {
    $duration = (string) $options['duration'];
    if (is_numeric($duration)) {
        $metadata['duration_seconds'] = (float) $duration;
    }
}

$connection = \Config\Database::connect();
$audits     = new AuditService($connection);

$timestamp = Time::now('UTC')->toDateTimeString();

$audits->recordEvent(
    eventKey: sprintf('scheduler:%s', $task),
    eventType: 'scheduler_task',
    context: ['tenant_id' => null, 'actor_id' => null],
    before: null,
    after: [
        'task'      => $task,
        'status'    => $status,
        'timestamp' => $timestamp,
    ],
    metadata: $metadata
);

$alertWebhook = getenv('OPERATIONS_ALERT_WEBHOOK');

if ($alertWebhook !== false && $alertWebhook !== '') {
    $shouldAlert = $status === 'error';

    if (! $shouldAlert && isset($metadata['duration_seconds'])) {
        $threshold = getenv('SCHEDULER_ALERT_THRESHOLD_SECONDS');
        if ($threshold !== false && $threshold !== '' && is_numeric($threshold)) {
            $shouldAlert = (float) $metadata['duration_seconds'] >= (float) $threshold;
        }
    }

    if ($shouldAlert) {
        $client      = new Client(['timeout' => 5]);
        $description = $metadata['message'] ?? 'Scheduler task reported an event.';
        $duration    = isset($metadata['duration_seconds'])
            ? sprintf('%.2fs', (float) $metadata['duration_seconds'])
            : 'n/a';

        $payload = [
            'text' => sprintf(
                ':rotating_light: Scheduler task `%s` reported `%s` (%s).',
                $task,
                $status,
                $description
            ),
            'attachments' => [[
                'fields' => [
                    ['title' => 'Task', 'value' => $task, 'short' => true],
                    ['title' => 'Status', 'value' => $status, 'short' => true],
                    ['title' => 'Duration', 'value' => $duration, 'short' => true],
                    ['title' => 'Timestamp', 'value' => $timestamp, 'short' => true],
                ],
            ]],
        ];

        try {
            $client->post($alertWebhook, ['json' => $payload]);
        } catch (GuzzleException $exception) {
            fwrite(
                STDERR,
                sprintf(
                    "Failed to send scheduler alert for %s: %s\n",
                    $task,
                    $exception->getMessage()
                )
            );
        }
    }
} elseif ($status === 'error') {
    fwrite(STDERR, "OPERATIONS_ALERT_WEBHOOK is not configured; scheduler failures will not alert operations.\n");
}
