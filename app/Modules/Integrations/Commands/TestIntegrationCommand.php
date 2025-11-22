<?php

namespace Modules\Integrations\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Integrations\Services\IntegrationService;

/**
 * CLI command to test integration adapters.
 */
class TestIntegrationCommand extends BaseCommand
{
    protected $group       = 'Integrations';
    protected $name        = 'integrations:test';
    protected $description = 'Test an integration adapter';
    protected $usage       = 'integrations:test <adapter_name>';
    protected $arguments   = [
        'adapter_name' => 'The name of the adapter to test',
    ];

    public function run(array $params)
    {
        $adapterName = $params[0] ?? CLI::prompt('Enter adapter name');

        if (empty($adapterName)) {
            CLI::error('Adapter name is required');

            return EXIT_ERROR;
        }

        CLI::write("Testing integration: {$adapterName}", 'yellow');

        try {
            $service = service('integrations');

            if (! $service->hasAdapter($adapterName)) {
                CLI::error("Adapter '{$adapterName}' is not registered");

                return EXIT_ERROR;
            }

            CLI::write('Checking adapter health...', 'blue');
            $health = $service->checkHealth($adapterName);

            CLI::write('Health Status:', 'green');
            CLI::print(json_encode($health, JSON_PRETTY_PRINT));

            if ($health['status'] === 'ok') {
                CLI::write("\n✓ Integration '{$adapterName}' is working correctly", 'green');

                return EXIT_SUCCESS;
            }

            CLI::error("\n✗ Integration '{$adapterName}' has issues");

            return EXIT_ERROR;
        } catch (\Throwable $e) {
            CLI::error("\n✗ Error testing integration: " . $e->getMessage());

            return EXIT_ERROR;
        }
    }
}
