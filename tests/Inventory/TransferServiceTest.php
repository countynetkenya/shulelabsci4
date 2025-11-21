<?php

declare(strict_types=1);

namespace Tests\Ci4\Inventory;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Foundation\Services\QrService;
use Modules\Inventory\Domain\Transfer;
use Modules\Inventory\Services\TransferRepositoryInterface;
use Modules\Inventory\Services\TransferService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransferServiceTest extends TestCase
{
    private TransferRepositoryInterface&MockObject $repository;
    private QrService&MockObject $qrService;
    private AuditService&MockObject $auditService;
    private MakerCheckerService&MockObject $makerChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository   = $this->createMock(TransferRepositoryInterface::class);
        $this->qrService    = $this->createMock(QrService::class);
        $this->auditService = $this->createMock(AuditService::class);
        $this->makerChecker = $this->createMock(MakerCheckerService::class);
    }

    public function testInitiateTransferQueuesApprovalIssuesQrAndRecordsAudit(): void
    {
        $service = new TransferService($this->repository, $this->qrService, $this->auditService, $this->makerChecker);

        $this->makerChecker
            ->expects($this->once())
            ->method('submit')
            ->with('inventory.transfer', $this->arrayHasKey('items'), $this->arrayHasKey('tenant_id'))
            ->willReturn(42);

        $this->qrService
            ->expects($this->once())
            ->method('issueToken')
            ->with(
                'inventory_transfer',
                $this->isType('string'),
                ['tenant_id' => 'tenant-1', 'base_url' => 'https://inventory.example']
            )
            ->willReturn([
                'token' => 'qr-token',
                'url'   => 'https://inventory.example/verify/qr-token',
                'png'   => 'binary',
            ]);

        $this->repository
            ->expects($this->once())
            ->method('store')
            ->with($this->callback(function (Transfer $transfer): bool {
                $this->assertSame('WH-A', $transfer->getSourceWarehouseId());
                $this->assertSame('WH-B', $transfer->getTargetWarehouseId());
                $this->assertSame([
                    ['sku' => 'CHEM-001', 'quantity' => 5],
                ], $transfer->getItems());

                return true;
            }))
            ->willReturnCallback(static fn (Transfer $transfer): Transfer => $transfer);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                $this->stringStartsWith('inventory.transfer.'),
                'transfer_initiated',
                $this->arrayHasKey('tenant_id'),
                null,
                $this->isType('array'),
                $this->callback(function (array $metadata): bool {
                    $this->assertSame('https://inventory.example/verify/qr-token', $metadata['qr_url']);
                    $this->assertSame(42, $metadata['maker_request']);

                    return true;
                })
            );

        $context = [
            'tenant_id'      => 'tenant-1',
            'actor_id'       => 'user-22',
            'base_url'       => 'https://inventory.example',
            'request_origin' => '10.0.0.5',
        ];

        $transfer = $service->initiateTransfer([
            'source_warehouse_id' => 'WH-A',
            'target_warehouse_id' => 'WH-B',
            'items'               => [
                ['sku' => 'CHEM-001', 'quantity' => 5],
            ],
        ], $context);

        $this->assertSame(Transfer::STATUS_PENDING, $transfer->getStatus());
        $this->assertSame('qr-token', $transfer->getQrToken());
        $this->assertSame(42, $transfer->getApprovalRequestId());
    }

    public function testCompleteTransferApprovesMakerCheckerOnAcceptance(): void
    {
        $service = new TransferService($this->repository, $this->qrService, $this->auditService, $this->makerChecker);

        $transfer = new Transfer('WH-A', 'WH-B', [['sku' => 'LAB-1', 'quantity' => 3]], 'transfer-1');
        $transfer->setApprovalRequestId(77);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with('transfer-1')
            ->willReturn($transfer);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($transfer, [])
            ->willReturnCallback(static fn (Transfer $passed): Transfer => $passed);

        $this->makerChecker
            ->expects($this->once())
            ->method('approve')
            ->with(77, $this->arrayHasKey('actor_id'));

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'inventory.transfer.transfer-1',
                'transfer_completed',
                $this->arrayHasKey('actor_id'),
                $this->isType('array'),
                $this->callback(function (array $after): bool {
                    $this->assertSame('accepted', $after['decision']);

                    return true;
                }),
                $this->arrayHasKey('decision')
            );

        $result = $service->completeTransfer('transfer-1', 'accepted', [
            'tenant_id' => 'tenant-1',
            'actor_id'  => 'checker-1',
        ]);

        $this->assertSame(Transfer::STATUS_ACCEPTED, $result->getStatus());
        $this->assertNotNull($result->getCompletedAt());
    }

    public function testCompleteTransferThrowsExceptionOnAlreadyCompletedTransfer(): void
    {
        $service = new TransferService($this->repository, $this->qrService, $this->auditService, $this->makerChecker);

        $transfer = new Transfer('WH-A', 'WH-B', [['sku' => 'LAB-1', 'quantity' => 3]], 'transfer-1');
        $transfer->markAccepted();

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with('transfer-1')
            ->willReturn($transfer);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transfer has already been completed and cannot be processed again.');

        $service->completeTransfer('transfer-1', 'accepted', [
            'tenant_id' => 'tenant-1',
            'actor_id'  => 'checker-1',
        ]);
    }

    public function testCompleteTransferThrowsExceptionWhenTransferIsNotPending(): void
    {
        $service = new TransferService($this->repository, $this->qrService, $this->auditService, $this->makerChecker);

        $transfer = new Transfer('WH-A', 'WH-B', [['sku' => 'LAB-1', 'quantity' => 3]], 'transfer-2');

        $statusProperty = new \ReflectionProperty(Transfer::class, 'status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($transfer, 'under_review');

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with('transfer-2')
            ->willReturn($transfer);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transfer must be pending to be processed. Current status: under_review.');

        $service->completeTransfer('transfer-2', 'accepted', [
            'tenant_id' => 'tenant-1',
            'actor_id'  => 'checker-1',
        ]);
    }
}
