<?php

declare(strict_types=1);

namespace EnvHealth\Auditor;

use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class PrivateKeyAuditor implements AuditorInterface
{
    public function __construct(
        private readonly string $projectRoot
    ) {
    }

    public function getName(): string
    {
        return 'Private Key Security Check';
    }

    public function audit(): AuditResult
    {
        $issues = [];
        $keyFiles = $this->findKeyFiles();

        if (empty($keyFiles)) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_PASS,
                100,
                'No private key files found in project.'
            );
        }

        foreach ($keyFiles as $keyFile) {
            // Check if in public directory
            if (str_contains($keyFile, '/public/') || str_contains($keyFile, '/public\\')) {
                $issues[] = basename($keyFile) . ' is in a public directory';
                continue;
            }

            // Check permissions
            $permissions = fileperms($keyFile);
            $octPerms = decoct($permissions & 0777);

            if ($octPerms !== '600') {
                $issues[] = basename($keyFile) . ' does not have 0600 permissions';
            }
        }

        if (empty($issues)) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_PASS,
                100,
                'All private key files are secure.'
            );
        }

        return new AuditResult(
            $this->getName(),
            AuditResult::STATUS_FAIL,
            0,
            'Issues found: ' . implode('; ', $issues) . '. Ensure keys are not in /public and have 0600 permissions.'
        );
    }

    private function findKeyFiles(): array
    {
        $keyFiles = [];
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $regex = new RegexIterator($iterator, '/^.+\.(pem|key)$/i', RegexIterator::GET_MATCH);

            foreach ($regex as $file) {
                $keyFiles[] = $file[0];
            }
        } catch (\Exception $e) {
            // If we can't scan, return empty array
        }

        return $keyFiles;
    }
}
