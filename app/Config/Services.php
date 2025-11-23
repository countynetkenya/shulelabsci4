<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Modules\Mobile\Config\Snapshot as SnapshotConfig;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function audit(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('audit');
        }

        return new \Modules\Foundation\Services\AuditService();
    }

    public static function softDelete(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('softDelete');
        }

        return new \Modules\Foundation\Services\SoftDeleteManager();
    }

    public static function ledger(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('ledger');
        }

        return new \Modules\Foundation\Services\LedgerService();
    }

    public static function integrationRegistry(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('integrationRegistry');
        }

        return new \Modules\Foundation\Services\IntegrationRegistry();
    }

    public static function tenantResolver(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('tenantResolver');
        }

        return new \Modules\Foundation\Services\TenantResolver();
    }

    public static function tenant(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('tenant');
        }

        return new \App\Services\TenantService(static::request());
    }

    public static function qr(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('qr');
        }

        return new \Modules\Foundation\Services\QrService();
    }

    public static function makerChecker(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('makerChecker');
        }

        return new \Modules\Foundation\Services\MakerCheckerService();
    }

    public static function moodleClient(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('moodleClient');
        }

        return new \Modules\Learning\Services\NullMoodleClient();
    }

    public static function moodleSync(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('moodleSync');
        }

        return new \Modules\Learning\Services\MoodleSyncService(
            static::moodleClient(),
            static::integrationRegistry(),
            static::audit()
        );
    }

    public static function moodleDispatchRunner(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('moodleDispatchRunner');
        }

        return new \Modules\Learning\Services\MoodleDispatchRunner(
            static::integrationRegistry(),
            static::moodleClient()
        );
    }

    public static function payrollApproval(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('payrollApproval');
        }

        return new \Modules\Hr\Services\PayrollApprovalService();
    }

    public static function snapshotTelemetry(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('snapshotTelemetry');
        }

        return new \Modules\Mobile\Services\SnapshotTelemetryService();
    }

    public static function offlineSnapshots(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('offlineSnapshots');
        }

        $config = config(SnapshotConfig::class);
        if (! $config instanceof SnapshotConfig) {
            $config = new SnapshotConfig();
        }

        return new \Modules\Mobile\Services\OfflineSnapshotService(
            $config->signingKey,
            $config->keyId,
            static::audit(),
            $config->defaultTtlSeconds,
            $config->fallbackKeys
        );
    }

    /**
     * Returns the IntegrationService for managing third-party integrations.
     *
     * @param bool $getShared Whether to return a shared instance
     * @return \Modules\Integrations\Services\IntegrationService
     */
    public static function integrations(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('integrations');
        }

        return new \Modules\Integrations\Services\IntegrationService(
            null,
            static::audit(),
            static::integrationRegistry()
        );
    }
}
