<?php

declare(strict_types=1);

namespace EnvHealth\Tests\Auditor;

use EnvHealth\Auditor\AuthMethodAuditor;
use EnvHealth\Contract\AuditResult;
use PHPUnit\Framework\TestCase;

class AuthMethodAuditorTest extends TestCase
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

    public function testNoAuthConfigReturnsWarning(): void
    {
        $auditor = new AuthMethodAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame('Database Authentication Method Check', $result->getCheckName());
        $this->assertSame(AuditResult::STATUS_WARN, $result->getStatus());
        $this->assertSame(80, $result->getScore());
    }

    public function testPasswordAuthReturnsWarning(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, "DB_PASSWORD=secret123\n");

        $auditor = new AuthMethodAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_WARN, $result->getStatus());
        $this->assertSame(60, $result->getScore());
        $this->assertStringContainsString('password authentication', $result->getSuggestion());
    }

    public function testSslKeyAuthReturnsPass(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, "DB_SSL_KEY=/path/to/key.pem\n");

        $auditor = new AuthMethodAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_PASS, $result->getStatus());
        $this->assertSame(100, $result->getScore());
        $this->assertStringContainsString('SSL keys', $result->getSuggestion());
    }

    public function testBothPasswordAndSslKeyPrefersSsl(): void
    {
        $envFile = $this->testDir . '/.env';
        file_put_contents($envFile, "DB_PASSWORD=secret123\nDB_SSL_KEY=/path/to/key.pem\n");

        $auditor = new AuthMethodAuditor($this->testDir);
        $result = $auditor->audit();

        $this->assertSame(AuditResult::STATUS_PASS, $result->getStatus());
        $this->assertSame(100, $result->getScore());
    }
}
