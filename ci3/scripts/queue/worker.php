#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CodeIgniter\I18n\Time;
use Modules\Foundation\Services\AuditService;

$connection = \Config\Database::connect();
$audits = new AuditService($connection);

fwrite(STDOUT, sprintf("[%s] Queue worker booted.\n", Time::now('UTC')->toDateTimeString()));

while (true) {
    // In production this loop should dispatch queued jobs. For now we emit a heartbeat so operators
    // can confirm the worker container remains responsive.
    $audits->recordEvent(
        eventKey: 'worker:heartbeat',
        eventType: 'worker_heartbeat',
        context: ['tenant_id' => null, 'actor_id' => null],
        before: null,
        after: ['status' => 'ok', 'timestamp' => Time::now('UTC')->toDateTimeString()],
        metadata: ['source' => 'queue-worker']
    );

    fwrite(STDOUT, sprintf("[%s] heartbeat recorded\n", Time::now('UTC')->toDateTimeString()));
    sleep(300);
}
