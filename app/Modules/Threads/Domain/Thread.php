<?php

namespace Modules\Threads\Domain;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class Thread
{
    private string $id;
    private string $subject;
    private string $contextType;
    private string $contextId;
    private bool $isCfr;
    private DateTimeImmutable $createdAt;

    /**
     * @var list<array{author: string, message: string, createdAt: string}>
     */
    private array $messages = [];

    public function __construct(
        string $subject,
        string $contextType,
        string $contextId,
        bool $isCfr = false,
        ?string $id = null,
        ?DateTimeImmutable $createdAt = null
    ) {
        $this->id          = $id ?? Uuid::uuid4()->toString();
        $this->subject     = $subject;
        $this->contextType = $contextType;
        $this->contextId   = $contextId;
        $this->isCfr       = $isCfr;
        $this->createdAt   = $createdAt ?? new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContextType(): string
    {
        return $this->contextType;
    }

    public function getContextId(): string
    {
        return $this->contextId;
    }

    public function isCfr(): bool
    {
        return $this->isCfr;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function addMessage(string $author, string $message): void
    {
        $this->messages[] = [
            'author'    => $author,
            'message'   => $message,
            'createdAt' => (new DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM),
        ];
    }

    /**
     * @return list<array{author: string, message: string, createdAt: string}>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'subject'     => $this->subject,
            'contextType' => $this->contextType,
            'contextId'   => $this->contextId,
            'isCfr'       => $this->isCfr,
            'createdAt'   => $this->createdAt->format(DATE_ATOM),
            'messages'    => $this->messages,
        ];
    }
}
