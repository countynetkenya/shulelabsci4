<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    /** @var array<string, string> */
    protected $options = [];

    /** @var bool */
    protected $verbose = false;

    /** @var float */
    protected $commandStart;

    public function __construct()
    {
        parent::__construct();

        if (!is_cli()) {
            show_error('The migrate controller is only accessible from the CLI.', 403, 'CLI Only');
            exit(1);
        }

        $this->load->library('migration');
        $this->load->database();

        $this->options = $this->parseCliOptions($_SERVER['argv'] ?? []);
        $this->verbose = $this->getBoolOption(['verbose', 'v'], false);
        $this->commandStart = microtime(true);

        $this->logDebug('Migration CLI initialised.');
    }

    public function status()
    {
        $this->logInfo('=== Migration status ===');

        $path = $this->normalisePath(config_item('migration_path'));
        $this->logInfo('Configured migration path: ' . $path);

        $currentVersion = $this->getCurrentVersion();
        $this->logInfo('Current version: ' . $currentVersion);

        $migrations = $this->migration->find_migrations();
        if (empty($migrations)) {
            $this->logInfo('No migrations were discovered.');
            exit(0);
        }

        $versions = array_keys($migrations);
        sort($versions, SORT_STRING);

        $applied = 0;
        $pending = [];

        $this->logInfo('Discovered migrations:');
        foreach ($versions as $version) {
            $file = $migrations[$version];
            $class = $this->inferMigrationClass($file);
            $isApplied = $this->compareVersions($version, $currentVersion) <= 0;
            $flag = $isApplied ? '✔' : '…';
            $status = $isApplied ? 'applied' : 'pending';
            $message = sprintf(' [%s] %s %s (%s)', $flag, $version, $class, basename($file));

            $this->logInfo($message);

            if ($isApplied) {
                $applied++;
            } else {
                $pending[] = $version;
            }
        }

        $this->logInfo(sprintf('Applied: %d | Pending: %d', $applied, count($pending)));
        if (!empty($pending)) {
            $this->logInfo('Pending versions: ' . implode(', ', $pending));
        } else {
            $this->logInfo('All migrations are up to date.');
        }

        exit(0);
    }

    public function latest()
    {
        $plan = $this->buildMigrationPlan(null);
        if (!$plan['valid']) {
            $this->logError($plan['error']);
            exit(1);
        }

        if ($plan['direction'] === 'none') {
            $this->logInfo(sprintf('No pending migrations. Already at version %s.', $plan['current']));
            exit(0);
        }

        $this->logInfo(sprintf(
            'Applying %d migration(s) to reach version %s.',
            count($plan['steps']),
            $plan['target']
        ));
        $this->logInfo('Started at ' . date(DATE_ATOM));

        foreach ($plan['steps'] as $step) {
            $this->runPlanStep($step);
        }

        $finalVersion = $this->getCurrentVersion();
        $this->logInfo(sprintf('Completed migrate/latest at %s (current version: %s).', date(DATE_ATOM), $finalVersion));

        exit(0);
    }

    public function version($target = null)
    {
        if ($target === null) {
            $this->logError('Usage: php index.php migrate version <timestamp> [--dry-run=1] [--verbose=1]');
            exit(1);
        }

        $target = (string) $target;
        $dryRun = $this->getBoolOption(['dry-run', 'dry_run'], false);

        $plan = $this->buildMigrationPlan($target);
        if (!$plan['valid']) {
            $this->logError($plan['error']);
            exit(1);
        }

        if ($plan['direction'] === 'none') {
            $this->logInfo(sprintf('Already at requested version %s.', $plan['current']));
            exit(0);
        }

        $this->logInfo(sprintf(
            'Planning %s from %s to %s.',
            $plan['direction'] === 'down' ? 'rollback' : 'migration',
            $plan['current'],
            $plan['target']
        ));
        $this->renderPlan($plan);

        if ($dryRun) {
            $this->logInfo('Dry run requested; no changes were applied.');
            exit(0);
        }

        foreach ($plan['steps'] as $step) {
            $this->runPlanStep($step);
        }

        $finalVersion = $this->getCurrentVersion();
        $this->logInfo(sprintf('Completed migrate/version to %s (current version: %s).', $target, $finalVersion));

        exit(0);
    }

    public function seed($name = 'all')
    {
        $seeders = $this->availableSeeders();
        if (empty($seeders)) {
            $this->logError('No seeders are registered.');
            exit(1);
        }

        $name = $name ?: 'all';
        $key = strtolower($name);

        if ($key === 'all') {
            $queue = $seeders;
        } elseif (isset($seeders[$key])) {
            $queue = [$key => $seeders[$key]];
        } else {
            $this->logError(sprintf('Unknown seeder "%s". Available seeders: %s', $name, implode(', ', array_keys($seeders))));
            exit(1);
        }

        $this->logInfo(sprintf('Running %d seeder(s) starting at %s.', count($queue), date(DATE_ATOM)));

        foreach ($queue as $seederKey => $definition) {
            $this->runSeeder($seederKey, $definition);
        }

        $this->logInfo('Seed operation completed.');
        exit(0);
    }

    protected function runSeeder($key, array $definition)
    {
        $class = $definition['class'];
        $file = $definition['file'];
        $description = isset($definition['description']) ? $definition['description'] : '';

        if (!is_file($file)) {
            $this->logError(sprintf('Seeder file missing for %s (%s).', $key, $file));
            exit(1);
        }

        require_once $file;

        if (!class_exists($class)) {
            $this->logError(sprintf('Seeder class %s could not be loaded from %s.', $class, $file));
            exit(1);
        }

        $this->logInfo(sprintf('Running seeder %s (%s). %s', $key, $class, $description));

        $instance = new $class();
        if (!method_exists($instance, 'run')) {
            $this->logError(sprintf('Seeder %s must implement a run() method.', $class));
            exit(1);
        }

        $queryOffset = $this->snapshotQueryLog();
        $start = microtime(true);

        $result = $instance->run(['verbose' => $this->verbose]);

        $duration = microtime(true) - $start;
        $queries = $this->extractQueriesSince($queryOffset);

        if ($result === false) {
            $this->logError(sprintf('Seeder %s reported a failure.', $class));
            exit(1);
        }

        $this->logInfo(sprintf('Seeder %s finished in %s.', $class, $this->formatDuration($duration)));
        if ($this->verbose) {
            $this->logExecutedQueries($queries);
        }
    }

    /**
     * @param array{valid:bool,direction:string,current:string,target:string,steps:array<int,array>} $plan
     * @return void
     */
    protected function renderPlan(array $plan)
    {
        if (empty($plan['steps'])) {
            $this->logInfo('No migrations need to run.');
            return;
        }

        foreach ($plan['steps'] as $step) {
            $action = $step['direction'] === 'down' ? 'Rollback' : 'Apply';
            $this->logInfo(sprintf(
                ' - %s %s (%s) [%s] => target %s',
                $action,
                $step['class'],
                $step['version'],
                basename($step['file']),
                $step['target']
            ));
        }
    }

    /**
     * @param array{direction:string,version:string,target:string,file:string,class:string} $step
     * @return void
     */
    protected function runPlanStep(array $step)
    {
        $action = $step['direction'] === 'down' ? 'Rolling back' : 'Applying';
        $this->logInfo(sprintf('%s %s (%s).', $action, $step['class'], $step['version']));

        $queryOffset = $this->snapshotQueryLog();
        $start = microtime(true);

        $result = $this->migration->version($step['target']);
        if ($result === false) {
            $this->handleError($step['direction'] === 'down' ? 'rollback' : 'migrate', $step['version']);
        }

        $duration = microtime(true) - $start;
        $queries = $this->extractQueriesSince($queryOffset);

        $this->logInfo(sprintf(
            '%s complete in %s. Now at version %s.',
            ucfirst($step['direction'] === 'down' ? 'rollback' : 'migration'),
            $this->formatDuration($duration),
            $result
        ));

        if ($this->verbose) {
            $this->logDebug('File: ' . $step['file']);
            $this->logExecutedQueries($queries);
        }
    }

    /**
     * @param array<int,string> $argv
     * @return array<string,string>
     */
    protected function parseCliOptions(array $argv)
    {
        $options = [];
        foreach ($argv as $arg) {
            if (strpos($arg, '--') !== 0) {
                continue;
            }

            $option = substr($arg, 2);
            if ($option === '') {
                continue;
            }

            if (strpos($option, '=') !== false) {
                list($key, $value) = explode('=', $option, 2);
            } else {
                $key = $option;
                $value = '1';
            }

            $options[strtolower($key)] = $value;
        }

        return $options;
    }

    /**
     * @param array<int,string> $keys
     * @param bool $default
     * @return bool
     */
    protected function getBoolOption(array $keys, $default = false)
    {
        foreach ($keys as $key) {
            $key = strtolower($key);
            if (!array_key_exists($key, $this->options)) {
                continue;
            }

            $value = strtolower((string) $this->options[$key]);
            if ($value === '' && $default !== false) {
                return $default;
            }

            if ($value === '1' || $value === 'true' || $value === 'yes' || $value === 'on') {
                return true;
            }

            if ($value === '0' || $value === 'false' || $value === 'no' || $value === 'off') {
                return false;
            }
        }

        return $default;
    }

    /**
     * @return string
     */
    protected function normalisePath($path)
    {
        if (!$path) {
            return '[not configured]';
        }

        $resolved = realpath($path);
        if ($resolved === false) {
            return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        return rtrim($resolved, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    protected function getCurrentVersion()
    {
        $table = config_item('migration_table');
        if (!$table || !$this->db->table_exists($table)) {
            return '0';
        }

        $row = $this->db->select('version')->get($table)->row();
        if (!$row || !isset($row->version)) {
            return '0';
        }

        return (string) $row->version;
    }

    /**
     * @param string|null $target
     * @return array{valid:bool,direction:string,current:string,target:string,steps:array<int,array>,error?:string}
     */
    protected function buildMigrationPlan($target)
    {
        $migrations = $this->migration->find_migrations();
        $versions = array_keys($migrations);
        sort($versions, SORT_STRING);

        $currentVersion = $this->getCurrentVersion();

        if (empty($versions)) {
            return [
                'valid' => $target === null || $target === '0' || $target === $currentVersion,
                'direction' => 'none',
                'current' => $currentVersion,
                'target' => $target === null ? $currentVersion : $target,
                'steps' => [],
                'error' => empty($versions) && $target !== null && $target !== '0'
                    ? 'No migrations are available to satisfy the requested version.'
                    : null,
            ];
        }

        if ($target === null) {
            $target = end($versions);
            reset($versions);
        }

        if ($target === false || $target === null) {
            $target = $currentVersion;
        }

        $target = (string) $target;

        if ($target !== '0' && $target !== $currentVersion && !isset($migrations[$target])) {
            return [
                'valid' => false,
                'direction' => 'none',
                'current' => $currentVersion,
                'target' => $target,
                'steps' => [],
                'error' => sprintf(
                    'Migration version %s was not found in %s',
                    $target,
                    $this->normalisePath(config_item('migration_path'))
                ),
            ];
        }

        $comparison = $this->compareVersions($target, $currentVersion);
        if ($comparison === 0) {
            return [
                'valid' => true,
                'direction' => 'none',
                'current' => $currentVersion,
                'target' => $target,
                'steps' => [],
            ];
        }

        $steps = [];
        if ($comparison > 0) {
            foreach ($versions as $version) {
                if ($this->compareVersions($version, $currentVersion) <= 0 || $this->compareVersions($version, $target) > 0) {
                    continue;
                }

                $steps[] = [
                    'direction' => 'up',
                    'version' => $version,
                    'target' => $version,
                    'file' => $migrations[$version],
                    'class' => $this->inferMigrationClass($migrations[$version]),
                ];
            }

            return [
                'valid' => true,
                'direction' => 'up',
                'current' => $currentVersion,
                'target' => $target,
                'steps' => $steps,
            ];
        }

        $versionsToRollback = [];
        foreach ($versions as $version) {
            if ($this->compareVersions($version, $target) > 0 && $this->compareVersions($version, $currentVersion) <= 0) {
                $versionsToRollback[] = $version;
            }
        }

        rsort($versionsToRollback, SORT_STRING);

        foreach ($versionsToRollback as $version) {
            $previous = $this->findPreviousVersion($version, $versions);
            if ($this->compareVersions($previous, $target) < 0) {
                $previous = $target;
            }

            $steps[] = [
                'direction' => 'down',
                'version' => $version,
                'target' => $previous,
                'file' => $migrations[$version],
                'class' => $this->inferMigrationClass($migrations[$version]),
            ];
        }

        return [
            'valid' => true,
            'direction' => 'down',
            'current' => $currentVersion,
            'target' => $target,
            'steps' => $steps,
        ];
    }

    /**
     * @param string $file
     * @return string
     */
    protected function inferMigrationClass($file)
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        if (strpos($name, '_') !== false) {
            list(, $suffix) = explode('_', $name, 2);
            return 'Migration_' . ucfirst($suffix);
        }

        return 'Migration_' . ucfirst($name);
    }

    /**
     * @param string $version
     * @param array<int,string> $versions
     * @return string
     */
    protected function findPreviousVersion($version, array $versions)
    {
        $index = array_search($version, $versions, true);
        if ($index === false || $index === 0) {
            return '0';
        }

        return (string) $versions[$index - 1];
    }

    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function compareVersions($a, $b)
    {
        if ($a === $b) {
            return 0;
        }

        if ($a === '0') {
            return -1;
        }

        if ($b === '0') {
            return 1;
        }

        return strcmp($a, $b);
    }

    /**
     * @return array<string,array{class:string,file:string,description:string}>
     */
    protected function availableSeeders()
    {
        $base = APPPATH . 'migrations' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR;

        return [
            'menu_overrides' => [
                'class' => 'MenuOverridesSeeder',
                'file' => $base . 'MenuOverridesSeeder.php',
                'description' => 'Ensures admin sidebar menu overrides exist for new tenants.',
            ],
            'audit_events' => [
                'class' => 'AuditEventsSeeder',
                'file' => $base . 'AuditEventsSeeder.php',
                'description' => 'Bootstraps the audit_events table with a system heartbeat entry.',
            ],
        ];
    }

    /**
     * @return int
     */
    protected function snapshotQueryLog()
    {
        if (!isset($this->db->queries) || !is_array($this->db->queries)) {
            return 0;
        }

        return count($this->db->queries);
    }

    /**
     * @param int $offset
     * @return array<int,string>
     */
    protected function extractQueriesSince($offset)
    {
        if (!isset($this->db->queries) || !is_array($this->db->queries)) {
            return [];
        }

        if ($offset <= 0) {
            return $this->db->queries;
        }

        return array_slice($this->db->queries, $offset);
    }

    /**
     * @param array<int,string> $queries
     * @return void
     */
    protected function logExecutedQueries(array $queries)
    {
        if (empty($queries)) {
            $this->logDebug('No SQL statements captured for this step.');
            return;
        }

        $this->logDebug(sprintf('Captured %d SQL statement(s):', count($queries)));
        foreach ($queries as $query) {
            $this->logDebug('  • ' . $this->truncateSql($query));
        }
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function truncateSql($sql)
    {
        $sql = preg_replace('/\s+/', ' ', trim($sql));
        if (strlen($sql) <= 300) {
            return $sql;
        }

        return substr($sql, 0, 297) . '...';
    }

    /**
     * @param string $message
     * @return void
     */
    protected function logInfo($message)
    {
        log_message('info', '[migrate] ' . $message);
        $this->writeLine($message, STDOUT);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function logError($message)
    {
        log_message('error', '[migrate] ' . $message);
        $this->writeLine($message, STDERR);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function logDebug($message)
    {
        log_message('debug', '[migrate] ' . $message);
        if ($this->verbose) {
            $this->writeLine('[debug] ' . $message, STDOUT);
        }
    }

    /**
     * @param string $message
     * @param resource $stream
     * @return void
     */
    protected function writeLine($message, $stream)
    {
        fwrite($stream, $message . PHP_EOL);
    }

    /**
     * @param float $seconds
     * @return string
     */
    protected function formatDuration($seconds)
    {
        return number_format($seconds, 3) . 's';
    }

    /**
     * @param string $command
     * @param string|null $target
     * @return void
     */
    protected function handleError($command, $target = null)
    {
        $message = $this->migration->error_string();
        if (!$message) {
            $message = 'Migration failed for an unknown reason.';
        }

        $context = $target !== null ? sprintf(' (target: %s)', $target) : '';
        $this->logError(sprintf('Migration %s%s failed: %s', $command, $context, $message));

        if (stripos($message, 'log_bin_trust_function_creators') !== false) {
            $this->logError('MySQL denied routine creation. Enable log_bin_trust_function_creators=1 or run the migration with a privileged account.');
        }

        exit(1);
    }
}

/* End of file Migrate.php */
/* Location: ./mvc/controllers/Migrate.php */
