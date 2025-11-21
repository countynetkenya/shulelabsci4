<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use CodeIgniter\HTTP\IncomingRequest;
use Modules\Foundation\Services\TenantResolver;
use RuntimeException;

class TenantResolverTest extends FoundationDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('tenant_catalog')->insertBatch([
            [
                'id'          => 'org-1',
                'tenant_type' => 'organisation',
                'name'        => 'Org One',
                'metadata'    => json_encode(['country' => 'KE'], JSON_THROW_ON_ERROR),
                'created_at'  => '2024-01-01 00:00:00',
            ],
            [
                'id'          => 'school-1',
                'tenant_type' => 'school',
                'name'        => 'School One',
                'metadata'    => json_encode(['curriculum' => 'CBC'], JSON_THROW_ON_ERROR),
                'created_at'  => '2024-01-01 00:00:00',
            ],
            [
                'id'          => 'warehouse-9',
                'tenant_type' => 'warehouse',
                'name'        => 'Central Store',
                'metadata'    => json_encode(['region' => 'Nairobi'], JSON_THROW_ON_ERROR),
                'created_at'  => '2024-01-01 00:00:00',
            ],
        ]);
    }

    public function testFromIdentifiersResolvesContext(): void
    {
        $resolver = new TenantResolver($this->db);

        $context = $resolver->fromIdentifiers([
            'organisation_id' => 'org-1',
            'school_id'       => 'school-1',
        ]);

        $this->assertSame('school-1', $context['tenant_id']);
        $this->assertSame('Org One', $context['organisation']['name']);
        $this->assertSame('School One', $context['school']['name']);
    }

    public function testFromRequestUsesJsonHeader(): void
    {
        $resolver = new TenantResolver($this->db);

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getHeaderLine')->willReturnCallback(static function (string $name): string {
            return match ($name) {
                'X-Tenant-Context' => json_encode([
                    'organisation_id' => 'org-1',
                    'warehouse_id'    => 'warehouse-9',
                ], JSON_THROW_ON_ERROR),
                default => '',
            };
        });
        $request->method('getGet')->willReturn(null);

        $context = $resolver->fromRequest($request);

        $this->assertSame('org-1', $context['tenant_id']);
        $this->assertSame('Central Store', $context['warehouse']['name']);
    }

    public function testFromIdentifiersThrowsWhenUnknownTenant(): void
    {
        $resolver = new TenantResolver($this->db);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown tenant school::missing-school');

        $resolver->fromIdentifiers(['school_id' => 'missing-school']);
    }
}
