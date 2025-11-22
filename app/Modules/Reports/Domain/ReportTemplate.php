<?php

declare(strict_types=1);

namespace Modules\Reports\Domain;

use DateTimeImmutable;

/**
 * Represents a report template entity
 */
class ReportTemplate
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly ?int $id,
        private string $name,
        private ?string $description,
        private string $category,
        private string $module,
        private array $config,
        private bool $isSystem,
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

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): void
    {
        $this->module = $module;
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

    public function isSystem(): bool
    {
        return $this->isSystem;
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
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category,
            'module'      => $this->module,
            'config'      => $this->config,
            'is_system'   => $this->isSystem,
            'is_active'   => $this->isActive,
            'created_at'  => $this->createdAt->format(DATE_ATOM),
            'updated_at'  => $this->updatedAt->format(DATE_ATOM),
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
            category: $data['category'],
            module: $data['module'],
            config: $data['config'] ?? $data['config_json'] ?? [],
            isSystem: (bool) ($data['is_system'] ?? false),
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
