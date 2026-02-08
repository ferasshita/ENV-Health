<?php

declare(strict_types=1);

namespace EnvHealth;

use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;

class AuditRunner
{
    /**
     * @var AuditorInterface[]
     */
    private array $auditors = [];

    public function addAuditor(AuditorInterface $auditor): self
    {
        $this->auditors[] = $auditor;
        return $this;
    }

    /**
     * @return AuditResult[]
     */
    public function runAllAudits(): array
    {
        $results = [];
        foreach ($this->auditors as $auditor) {
            $results[] = $auditor->audit();
        }
        return $results;
    }

    public function calculateHealthScore(array $results): int
    {
        if (empty($results)) {
            return 0;
        }

        $totalScore = 0;
        foreach ($results as $result) {
            $totalScore += $result->getScore();
        }

        return (int) round($totalScore / count($results));
    }
}
