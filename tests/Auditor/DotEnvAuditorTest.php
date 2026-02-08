<?php

declare(strict_types=1);

namespace EnvHealth\Tests\Auditor;

use EnvHealth\Auditor\DotEnvAuditor;
use EnvHealth\Contract\AuditResult;
use PHPUnit\Framework\TestCase;

class DotEnvAuditorTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/env-health-test-' . uniqid();
        mkdir($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testDir . '/.env')) {
            unlink($this->testDir . '/.env');
        }
        if (is_dir($this->testDir)) {
            rmdir($this->testDir);
        }
    }

    public function testNoEnvFileReturnsWarning(): void
    {
        $auditor = new DotEnvAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame('DotEnv Security Check', $result->getCheckName());
        $this->assertSame(AuditResult::STATUS_WARN, $result->getStatus());
        $this->assertSame(80, $result->getScore());
    }

    public function testSecurePermissions0600ReturnsPass(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, 'TEST=value');
        chmod($envFile, 0600);

        $auditor = new DotEnvAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_PASS, $result->getStatus());
        $this->assertSame(100, $result->getScore());
    }

    public function testSecurePermissions0640ReturnsPass(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, 'TEST=value');
        chmod($envFile, 0640);

        $auditor = new DotEnvAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_PASS, $result->getStatus());
        $this->assertSame(100, $result->getScore());
    }

    public function testGloballyReadableReturnsFail(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, 'TEST=value');
        chmod($envFile, 0644);

        $auditor = new DotEnvAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_FAIL, $result->getStatus());
        $this->assertSame(0, $result->getScore());
        $this->assertStringContainsString('globally readable', $result->getSuggestion());
    }

    public function testNonOptimalPermissionsReturnsWarning(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, 'TEST=value');
        chmod($envFile, 0660);

        $auditor = new DotEnvAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_WARN, $result->getStatus());
        $this->assertSame(50, $result->getScore());
    }
}
