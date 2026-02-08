<?php

declare(strict_types=1);

namespace EnvHealth\Contract;

class AuditResult
{
    public const STATUS_PASS = 'PASS';
    public const STATUS_FAIL = 'FAIL';
    public const STATUS_WARN = 'WARN';

    public function __construct(
        private readonly string $checkName,
        private readonly string $status,
        private readonly int $score,
        private readonly string $suggestion
    ) {
    }

    public function getCheckName(): string
    {
        return $this->checkName;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getSuggestion(): string
    {
        return $this->suggestion;
    }
}
