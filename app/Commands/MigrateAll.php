<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Migrate All Command
 * 
 * Runs migrations for all namespaces including modules that may not be
 * auto-discovered by the default migrate --all command.
 * 
 * This command explicitly runs migrations for:
 * - App namespace (main application migrations)
 * - All known module namespaces (e.g., Modules\Foundation)
 */
class MigrateAll extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Database';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'migrate:all-modules';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Runs all migrations including module migrations.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'migrate:all-modules [options]';

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '-g' => 'Set database group',
    ];

    /**
     * List of module namespaces that contain migrations
     *
     * @var array
     */
    protected $moduleNamespaces = [
        'Modules\\Foundation',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $group = $params['g'] ?? CLI::getOption('g');

        CLI::write('Running all migrations (App + Modules)...', 'yellow');
        CLI::newLine();

        // Run App migrations
        CLI::write('Running App migrations...', 'blue');
        $this->call('migrate', array_filter([
            'g' => $group,
        ]));
        CLI::newLine();

        // Run each module's migrations
        foreach ($this->moduleNamespaces as $namespace) {
            CLI::write("Running {$namespace} migrations...", 'blue');
            $this->call('migrate', array_filter([
                'n' => $namespace,
                'g' => $group,
            ]));
            CLI::newLine();
        }

        CLI::write('All migrations complete!', 'green');
        
        return 0;
    }
}
