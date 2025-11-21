<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class OpenApiContractTest extends TestCase
{
    private array $spec;

    protected function setUp(): void
    {
        $this->spec = Yaml::parseFile(__DIR__ . '/../../ci4/docs/openapi.yaml');
    }

    public function testSpecDefinesCorePaymentEndpoints(): void
    {
        $paths = $this->spec['paths'] ?? [];

        $this->assertArrayHasKey('/api/v10/payment', $paths);
        $this->assertArrayHasKey('/api/v10/payment/save_payment', $paths);
    }

    public function testEveryOperationDeclaresASuccessResponse(): void
    {
        foreach (($this->spec['paths'] ?? []) as $path => $definition) {
            foreach ($definition as $method => $operation) {
                if (!is_array($operation)) {
                    continue;
                }

                $responses = $operation['responses'] ?? [];
                $this->assertNotEmpty($responses, sprintf('Missing responses for %s %s', strtoupper($method), $path));
                $this->assertTrue(
                    array_key_exists('200', $responses) || array_key_exists('201', $responses),
                    sprintf('No success response defined for %s %s', strtoupper($method), $path)
                );
            }
        }
    }
}
