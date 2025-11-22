<?php

declare(strict_types=1);

namespace Modules\Reports\Domain;

use DateTimeImmutable;

/**
 * Represents a report entity
 */
class Report
{
    public const TYPE_TABLE   = 'table';
    public const TYPE_CHART   = 'chart';
    public const TYPE_SUMMARY = 'summary';
    public const TYPE_CUSTOM  = 'custom';

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly ?int $id,
        private string $name,
        private ?string $description,
        private string $type,
        private array $config,
        private readonly string $ownerId,
        private readonly string $tenantId,
        private ?int $templateRef,
        private bool $isPublic,
        private bool $isActive,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getTemplateRef(): ?int
    {
        return $this->templateRef;
    }

    public function setTemplateRef(?int $templateRef): void
    {
        $this->templateRef = $templateRef;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable('now');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'description'  => $this->description,
            'type'         => $this->type,
            'config'       => $this->config,
            'owner_id'     => $this->ownerId,
            'tenant_id'    => $this->tenantId,
            'template_ref' => $this->templateRef,
            'is_public'    => $this->isPublic,
            'is_active'    => $this->isActive,
            'created_at'   => $this->createdAt->format(DATE_ATOM),
            'updated_at'   => $this->updatedAt->format(DATE_ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            type: $data['type'],
            config: $data['config'] ?? $data['config_json'] ?? [],
            ownerId: $data['owner_id'],
            tenantId: $data['tenant_id'],
            templateRef: $data['template_ref'] ?? null,
            isPublic: (bool) ($data['is_public'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) 
                ? new DateTimeImmutable($data['created_at']) 
                : new DateTimeImmutable(),
            updatedAt: isset($data['updated_at']) 
                ? new DateTimeImmutable($data['updated_at']) 
                : new DateTimeImmutable(),
        );
    }
}
