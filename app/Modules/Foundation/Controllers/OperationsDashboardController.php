<?php

declare(strict_types=1);

namespace Modules\Foundation\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Mobile\Services\SnapshotTelemetryService;

class OperationsDashboardController extends BaseController
{
    private SnapshotTelemetryService $telemetry;

    public function __construct(?SnapshotTelemetryService $telemetry = null)
    {
        $this->telemetry = $telemetry ?? service('snapshotTelemetry');
    }

    public function index(): string
    {
        $hours = $this->resolveWindow();
        $telemetry = $this->telemetry->getTelemetry($hours);

        return view('Modules\\Foundation\\Views\\operations_dashboard', [
            'snapshotTelemetry' => $telemetry,
        ]);
    }

    public function mobileSnapshots(): ResponseInterface
    {
        $hours = $this->resolveWindow();
        $telemetry = $this->telemetry->getTelemetry($hours);

        return $this->response->setJSON($telemetry);
    }

    private function resolveWindow(): int
    {
        $hours = (int) ($this->request->getGet('hours') ?? 24);
        if ($hours <= 0) {
            $hours = 24;
        }

        return min($hours, 168);
    }
}
