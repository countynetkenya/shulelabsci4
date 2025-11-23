<?php

namespace Tests\Ci4\Integrations;

use CodeIgniter\Test\CIUnitTestCase;
use Modules\Integrations\Services\Adapters\Storage\LocalStorageAdapter;

/**
 * Tests for the LocalStorageAdapter.
 */
class LocalStorageAdapterTest extends CIUnitTestCase
{
    private LocalStorageAdapter $adapter;

    private string $testBasePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBasePath = WRITEPATH . 'tests/storage';

        if (!is_dir($this->testBasePath)) {
            mkdir($this->testBasePath, 0755, true);
        }

        $this->adapter = new LocalStorageAdapter(['base_path' => $this->testBasePath]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test files
        if (is_dir($this->testBasePath)) {
            $this->recursiveDelete($this->testBasePath);
        }
    }

    public function testGetNameReturnsCorrectValue(): void
    {
        $this->assertEquals('local_storage', $this->adapter->getName());
    }

    public function testCheckStatusReturnsOkForWritablePath(): void
    {
        $status = $this->adapter->checkStatus();

        $this->assertIsArray($status);
        $this->assertEquals('ok', $status['status']);
    }

    public function testListReturnsEmptyArrayForEmptyDirectory(): void
    {
        $result = $this->adapter->list('/', []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('files', $result);
        $this->assertIsArray($result['files']);
    }

    public function testUploadCreatesFileInStorage(): void
    {
        // Create a test file
        $sourceFile = $this->testBasePath . '/source.txt';
        file_put_contents($sourceFile, 'test content');

        $result = $this->adapter->upload([
            'file_path'   => $sourceFile,
            'destination' => 'uploaded/test.txt',
        ], []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_id', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('size', $result);

        // Verify file was copied
        $this->assertFileExists($this->testBasePath . '/uploaded/test.txt');
    }

    public function testListReturnsFilesInDirectory(): void
    {
        // Create test files
        $testDir = $this->testBasePath . '/testdir';
        mkdir($testDir, 0755, true);
        file_put_contents($testDir . '/file1.txt', 'content1');
        file_put_contents($testDir . '/file2.txt', 'content2');

        $result = $this->adapter->list('testdir/', []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('files', $result);
        $this->assertCount(2, $result['files']);
    }

    /**
     * Recursively delete a directory and its contents.
     */
    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }

        rmdir($dir);
    }
}
