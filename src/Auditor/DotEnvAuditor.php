<?php

declare(strict_types=1);

namespace EnvHealth\Auditor;

use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;

class DotEnvAuditor implements AuditorInterface
{
    public function __construct(
        private readonly string $projectRoot
    ) {
    }

    public function getName(): string
    {
        return 'DotEnv Security Check';
    }

    public function audit(): AuditResult
    {
        $envPath = $this->projectRoot . '/.env';

        if (!file_exists($envPath)) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_WARN,
                80,
                'No .env file found. If you use environment variables, ensure they are not exposed.'
            );
        }

        $permissions = fileperms($envPath);
        $octPerms = decoct($permissions & 0777);

        // Check if permissions are 0600 or 0640
        if ($octPerms === '600' || $octPerms === '640') {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_PASS,
                100,
                '.env file has secure permissions.'
            );
        }

        // Check if globally readable
        if ($permissions & 0004) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_FAIL,
                0,
                '.env file is globally readable! Set permissions to 0600 or 0640.'
            );
        }

        return new AuditResult(
            $this->getName(),
            AuditResult::STATUS_WARN,
            50,
            '.env file permissions are not optimal. Recommended: 0600 or 0640.'
        );
    }
}
