<?php

namespace Modules\Finance\Domain;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Represents an append-only finance invoice tracked through the ledger.
 */
class Invoice
{
    public const STATUS_ISSUED = 'issued';
    public const STATUS_SETTLED = 'settled';

    private string $invoiceNumber;

    private string $studentId;

    private string $currencyCode;

    /**
     * @var list<array{description: string, amount: string, revenue_account: string, metadata?: array<string, mixed>}>
     */
    private array $lineItems;

    private string $totalAmount;

    private string $status;

    private DateTimeImmutable $issuedAt;

    private ?DateTimeImmutable $settledAt = null;

    private ?string $paymentMethod = null;

    private ?int $ledgerTransactionId = null;

    private ?int $settlementTransactionId = null;

    private string $receivableAccount;

    /**
     * @param array<int, array{description: string, amount: string, revenue_account: string, metadata?: array<string, mixed>}> $lineItems
     */
    public function __construct(
        string $invoiceNumber,
        string $studentId,
        string $currencyCode,
        array $lineItems,
        string $totalAmount,
        string $receivableAccount,
        ?DateTimeImmutable $issuedAt = null
    ) {
        $this->invoiceNumber = $invoiceNumber;
        $this->studentId = $studentId;
        $this->currencyCode = $currencyCode;
        if (!array_is_list($lineItems)) {
            $lineItems = array_values($lineItems);
        }

        /** @var list<array{description: string, amount: string, revenue_account: string, metadata?: array<string, mixed>}> $lineItems */
        $this->lineItems = $lineItems;
        $this->totalAmount = $totalAmount;
        $this->receivableAccount = $receivableAccount;
        $this->status = self::STATUS_ISSUED;
        $this->issuedAt = $issuedAt ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getStudentId(): string
    {
        return $this->studentId;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @return list<array{description: string, amount: string, revenue_account: string, metadata?: array<string, mixed>}>
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function getReceivableAccount(): string
    {
        return $this->receivableAccount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getSettledAt(): ?DateTimeImmutable
    {
        return $this->settledAt;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function getLedgerTransactionId(): ?int
    {
        return $this->ledgerTransactionId;
    }

    public function getSettlementTransactionId(): ?int
    {
        return $this->settlementTransactionId;
    }

    public function setLedgerTransactionId(int $transactionId): void
    {
        $this->ledgerTransactionId = $transactionId;
    }

    public function markSettled(int $transactionId, string $paymentMethod, ?DateTimeImmutable $settledAt = null): void
    {
        $this->settlementTransactionId = $transactionId;
        $this->paymentMethod = $paymentMethod;
        $this->settledAt = $settledAt ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->status = self::STATUS_SETTLED;
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_SETTLED;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'invoiceNumber'            => $this->invoiceNumber,
            'studentId'                => $this->studentId,
            'currencyCode'             => $this->currencyCode,
            'lineItems'                => $this->lineItems,
            'totalAmount'              => $this->totalAmount,
            'status'                   => $this->status,
            'issuedAt'                 => $this->issuedAt->format(DATE_ATOM),
            'settledAt'                => $this->settledAt?->format(DATE_ATOM),
            'paymentMethod'            => $this->paymentMethod,
            'ledgerTransactionId'      => $this->ledgerTransactionId,
            'settlementTransactionId'  => $this->settlementTransactionId,
            'receivableAccount'        => $this->receivableAccount,
        ];
    }
}
