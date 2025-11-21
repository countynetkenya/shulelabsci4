<?php

declare(strict_types=1);

use App\Services\Integrations\IntegrationHealth;
use PHPUnit\Framework\TestCase;

final class IntegrationHealthTest extends TestCase
{
    public function testSummarizeTracksStatusAndLatency(): void
    {
        $service = new IntegrationHealth();
        $summary = $service->summarize([
            ['status' => 'active', 'latency_ms' => 150],
            ['status' => 'active', 'latency_ms' => 220],
            ['status' => 'failing', 'latency_ms' => 980],
            ['status' => 'disabled'],
        ]);

        $this->assertSame(4, $summary['total']);
        $this->assertSame(2, $summary['active']);
        $this->assertSame(1, $summary['failing']);
        $this->assertSame(1, $summary['disabled']);
        $this->assertSame(50.0, $summary['uptime_percentage']);
        $this->assertSame(220.0, $summary['latency_ms']['p50']);
        $this->assertSame(980.0, $summary['latency_ms']['p95']);
    }
}
