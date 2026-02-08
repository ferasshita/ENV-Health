<?php

declare(strict_types=1);

namespace EnvHealth\Auditor;

use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;

class PhpIniAuditor implements AuditorInterface
{
    public function getName(): string
    {
        return 'PHP Configuration Check';
    }

    public function audit(): AuditResult
    {
        $issues = [];
        $warnings = [];

        // Check display_errors
        $displayErrors = ini_get('display_errors');
        if ($displayErrors === '1' || strtolower($displayErrors) === 'on') {
            $issues[] = 'display_errors is ON (should be OFF in production)';
        }

        // Check allow_url_fopen
        $allowUrlFopen = ini_get('allow_url_fopen');
        if ($allowUrlFopen === '1' || strtolower($allowUrlFopen) === 'on') {
            $warnings[] = 'allow_url_fopen is ON (potential security risk)';
        }

        if (!empty($issues)) {
            $message = 'Critical issues: ' . implode('; ', $issues);
            if (!empty($warnings)) {
                $message .= '. ' . implode('; ', $warnings);
            }
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_FAIL,
                30,
                $message
            );
        }

        if (!empty($warnings)) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_WARN,
                70,
                'Warnings: ' . implode('; ', $warnings)
            );
        }

        return new AuditResult(
            $this->getName(),
            AuditResult::STATUS_PASS,
            100,
            'PHP configuration is secure.'
        );
    }
}
