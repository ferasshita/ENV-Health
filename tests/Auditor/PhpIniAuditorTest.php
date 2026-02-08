<?php

declare(strict_types=1);

namespace EnvHealth\Tests\Auditor;

use EnvHealth\Auditor\PhpIniAuditor;
use EnvHealth\Contract\AuditResult;
use PHPUnit\Framework\TestCase;

class PhpIniAuditorTest extends TestCase
{
    public function testAuditReturnsAuditResult(): void
    {
        $auditor = new PhpIniAuditor();
        $result = $auditor->audit();

        $this->assertInstanceOf(AuditResult::class, $result);
        $this->assertSame('PHP Configuration Check', $result->getCheckName());
        $this->assertContains($result->getStatus(), [
            AuditResult::STATUS_PASS,
            AuditResult::STATUS_WARN,
            AuditResult::STATUS_FAIL
        ]);
    }

    public function testGetName(): void
    {
        $auditor = new PhpIniAuditor();
        $this->assertSame('PHP Configuration Check', $auditor->getName());
    }
}
