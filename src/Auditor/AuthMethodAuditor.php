<?php

declare(strict_types=1);

namespace EnvHealth\Auditor;

use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;

class AuthMethodAuditor implements AuditorInterface
{
    public function __construct(
        private readonly string $projectRoot
    ) {
    }

    public function getName(): string
    {
        return 'Database Authentication Method Check';
    }

    public function audit(): AuditResult
    {
        $envVars = $this->loadEnvVariables();

        $hasDbPassword = isset($envVars['DB_PASSWORD']) && !empty($envVars['DB_PASSWORD']);
        $hasSslKey = isset($envVars['DB_SSL_KEY']) && !empty($envVars['DB_SSL_KEY']);

        if ($hasSslKey) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_PASS,
                100,
                'Using SSL keys for database authentication. Excellent security practice!'
            );
        }

        if ($hasDbPassword) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_WARN,
                60,
                'Using password authentication. Consider upgrading to SSL key-based authentication for better security.'
            );
        }

        return new AuditResult(
            $this->getName(),
            AuditResult::STATUS_WARN,
            80,
            'No database authentication configured in environment.'
        );
    }

    private function loadEnvVariables(): array
    {
        $envPath = $this->projectRoot . '/.env';
        $envVars = [];

        if (!file_exists($envPath)) {
            return $envVars;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $envVars;
        }

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse key=value
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                // Remove quotes
                $value = trim($value, '"\'');
                $envVars[$key] = $value;
            }
        }

        return $envVars;
    }
}
