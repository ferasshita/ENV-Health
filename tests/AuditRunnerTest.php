<?php

declare(strict_types=1);

namespace EnvHealth\Tests;

use EnvHealth\AuditRunner;
use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;
use PHPUnit\Framework\TestCase;

class AuditRunnerTest extends TestCase
{
    public function testAddAuditorReturnsInstance(): void
    {
        $runner = new AuditRunner();
        $auditor = $this->createMock(AuditorInterface::class);

        $result = $runner->addAuditor($auditor);

        $this->assertInstanceOf(AuditRunner::class, $result);
    }

    public function testRunAllAuditsReturnsResults(): void
    {
        $runner = new AuditRunner();

        $auditor1 = $this->createMock(AuditorInterface::class);
        $auditor1->method('audit')->willReturn(
            new AuditResult('Test 1', AuditResult::STATUS_PASS, 100, 'Good')
        );

        $auditor2 = $this->createMock(AuditorInterface::class);
        $auditor2->method('audit')->willReturn(
            new AuditResult('Test 2', AuditResult::STATUS_FAIL, 0, 'Bad')
        );

        $runner->addAuditor($auditor1)->addAuditor($auditor2);
        $results = $runner->runAllAudits();

        $this->assertCount(2, $results);
        $this->assertInstanceOf(AuditResult::class, $results[0]);
        $this->assertInstanceOf(AuditResult::class, $results[1]);
    }

    public function testCalculateHealthScoreWithEmptyResults(): void
    {
        $runner = new AuditRunner();
        $score = $runner->calculateHealthScore([]);

        $this->assertSame(0, $score);
    }

    public function testCalculateHealthScoreAveragesScores(): void
    {
        $runner = new AuditRunner();
        $results = [
            new AuditResult('Test 1', AuditResult::STATUS_PASS, 100, 'Good'),
            new AuditResult('Test 2', AuditResult::STATUS_WARN, 50, 'Warning'),
            new AuditResult('Test 3', AuditResult::STATUS_FAIL, 0, 'Bad'),
        ];

        $score = $runner->calculateHealthScore($results);

        // Average of 100, 50, 0 = 50
        $this->assertSame(50, $score);
    }

    public function testCalculateHealthScoreRoundsCorrectly(): void
    {
        $runner = new AuditRunner();
        $results = [
            new AuditResult('Test 1', AuditResult::STATUS_PASS, 100, 'Good'),
            new AuditResult('Test 2', AuditResult::STATUS_WARN, 80, 'Warning'),
            new AuditResult('Test 3', AuditResult::STATUS_WARN, 70, 'Warning'),
        ];

        $score = $runner->calculateHealthScore($results);

        // Average of 100, 80, 70 = 83.33... rounds to 83
        $this->assertSame(83, $score);
    }
}
