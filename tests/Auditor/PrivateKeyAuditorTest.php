<?php

declare(strict_types=1);

namespace EnvHealth\Tests\Auditor;

use EnvHealth\Auditor\PrivateKeyAuditor;
use EnvHealth\Contract\AuditResult;
use PHPUnit\Framework\TestCase;

class PrivateKeyAuditorTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/env-health-test-' . uniqid();
        mkdir($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testNoKeyFilesReturnsPass(): void
    {
        $auditor = new PrivateKeyAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame('Private Key Security Check', $result->getCheckName());
        $this->assertSame(AuditResult::STATUS_PASS, $result->getStatus());
        $this->assertSame(100, $result->getScore());
    }

    public function testKeyFileWithCorrectPermissionsReturnsPass(): void
    {
        $keyFile = $this->testDir . '/private.key';
        file_put_contents($keyFile, 'PRIVATE KEY DATA');
        chmod($keyFile, 0600);

        $auditor = new PrivateKeyAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_PASS, $result->getStatus());
        $this->assertSame(100, $result->getScore());
    }

    public function testKeyFileInPublicDirectoryReturnsFail(): void
    {
        mkdir($this->testDir . '/public', 0755, true);
        $keyFile = $this->testDir . '/public/private.pem';
        file_put_contents($keyFile, 'PRIVATE KEY DATA');
        chmod($keyFile, 0600);

        $auditor = new PrivateKeyAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_FAIL, $result->getStatus());
        $this->assertSame(0, $result->getScore());
        $this->assertStringContainsString('public directory', $result->getSuggestion());
    }

    public function testKeyFileWithWrongPermissionsReturnsFail(): void
    {
        $keyFile = $this->testDir . '/private.key';
        file_put_contents($keyFile, 'PRIVATE KEY DATA');
        chmod($keyFile, 0644);

        $auditor = new PrivateKeyAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_FAIL, $result->getStatus());
        $this->assertSame(0, $result->getScore());
        $this->assertStringContainsString('0600 permissions', $result->getSuggestion());
    }
}
