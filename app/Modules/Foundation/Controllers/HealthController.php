<?php

namespace Modules\Foundation\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\IntegrationRegistry;
use Modules\Foundation\Services\LedgerService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Foundation\Services\QrService;
use Modules\Foundation\Services\TenantResolver;

class HealthController extends ResourceController
{
    protected $format = 'json';

    public function index(): ResponseInterface
    {
        $resolver = new TenantResolver();
        $audit = new AuditService();
        $ledger = new LedgerService();
        $registry = new IntegrationRegistry();
        $qr = new QrService();
        $maker = new MakerCheckerService();

        return $this->respond([
            'status'        => 'ok',
            'services'      => [
                'tenantResolver'   => get_class($resolver),
                'auditService'     => get_class($audit),
                'ledgerService'    => get_class($ledger),
                'integration'      => get_class($registry),
                'qrService'        => get_class($qr),
                'makerChecker'     => get_class($maker),
            ],
            'timestamp'     => time(),
        ]);
    }
}
