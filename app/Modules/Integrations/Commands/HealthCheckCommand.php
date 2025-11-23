<?php

namespace Modules\Integrations\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI command to check health of all integrations.
 */
class HealthCheckCommand extends BaseCommand
{
    protected $group = 'Integrations';

    protected $name = 'integrations:health';

    protected $description = 'Check health of all registered integrations';

    public function run(array $params)
    {
        CLI::write('Checking health of all integrations...', 'yellow');
        CLI::newLine();

        try {
            $service = service('integrations');
            $adapters = $service->getRegisteredAdapters();

            if (empty($adapters)) {
                CLI::write('No integrations are currently registered.', 'blue');

                return EXIT_SUCCESS;
            }

            $healthStatuses = [];
            $totalOk = 0;
            $totalError = 0;

            foreach ($adapters as $adapterName) {
                CLI::write("Checking {$adapterName}...", 'blue');

                try {
                    $health = $service->checkHealth($adapterName);
                    $healthStatuses[] = [
                        'adapter' => $adapterName,
                        'status'  => $health['status'] ?? 'unknown',
                        'message' => $health['message'] ?? '',
                    ];

                    if (($health['status'] ?? '') === 'ok') {
                        $totalOk++;
                        CLI::write("  ✓ {$adapterName}: OK", 'green');
                    } else {
                        $totalError++;
                        CLI::write("  ✗ {$adapterName}: ERROR - " . ($health['message'] ?? ''), 'red');
                    }
                } catch (\Throwable $e) {
                    $totalError++;
                    $healthStatuses[] = [
                        'adapter' => $adapterName,
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                    ];
                    CLI::write("  ✗ {$adapterName}: ERROR - " . $e->getMessage(), 'red');
                }
            }

            CLI::newLine();
            CLI::write('Summary:', 'yellow');
            CLI::write('Total integrations: ' . count($adapters), 'blue');
            CLI::write("Healthy: {$totalOk}", 'green');
            CLI::write("Issues: {$totalError}", $totalError > 0 ? 'red' : 'blue');

            return $totalError === 0 ? EXIT_SUCCESS : EXIT_ERROR;
        } catch (\Throwable $e) {
            CLI::error('Error checking integrations: ' . $e->getMessage());

            return EXIT_ERROR;
        }
    }
}
