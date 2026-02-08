<?php

declare(strict_types=1);

namespace EnvHealth\Contract;

interface AuditorInterface
{
    public function audit(): AuditResult;

    public function getName(): string;
}
