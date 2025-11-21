<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use CodeIgniter\I18n\Time;
use Modules\Foundation\Services\QrService;
use RuntimeException;

class QrServiceTest extends FoundationDatabaseTestCase
{
    public function testIssueTokenPersistsRecordAndReturnsArtifacts(): void
    {
        $service = new QrService($this->db);

        $result = $service->issueToken(
            resourceType: 'report_card',
            resourceId: 'RC-100',
            context: ['tenant_id' => 'tenant-40', 'base_url' => 'https://verify.shulelabs.test'],
            ttlSeconds: 120
        );

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('png', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertStringStartsWith('https://verify.shulelabs.test/verify/', $result['url']);
        $this->assertNotEmpty($result['png']);

        $row = $this->db->table('qr_tokens')->where('token', $result['token'])->get()->getFirstRow('array');
        $this->assertSame('report_card', $row['resource_type']);
        $this->assertNotNull($row['expires_at']);
    }

    public function testVerifyRecordsScan(): void
    {
        $service = new QrService($this->db);
        $tokenData = $service->issueToken('inventory_transfer', 'TR-55', ['tenant_id' => 'tenant-41']);

        $record = $service->verify($tokenData['token'], [
            'ip'        => '10.10.10.10',
            'user_agent'=> 'phpunit',
            'metadata'  => ['location' => 'Nairobi'],
        ]);

        $this->assertSame('inventory_transfer', $record['resource_type']);

        $scans = $this->db->table('qr_scans')->where('token_id', $record['id'])->get()->getResultArray();
        $this->assertCount(1, $scans);
        $this->assertSame('10.10.10.10', $scans[0]['ip_address']);
    }

    public function testVerifyThrowsForExpiredToken(): void
    {
        $service = new QrService($this->db);
        $tokenData = $service->issueToken('document', 'DOC-1', ['tenant_id' => 'tenant-42']);

        $row = $this->db->table('qr_tokens')->where('token', $tokenData['token'])->get()->getFirstRow('array');
        $this->db->table('qr_tokens')->where('id', $row['id'])->update([
            'expires_at' => Time::now('UTC')->subMinutes(5)->toDateTimeString(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('QR token expired.');

        $service->verify($tokenData['token'], []);
    }
}
