<?php

declare(strict_types=1);

namespace EnvHealth\Command;

use EnvHealth\AuditRunner;
use EnvHealth\Auditor\AuthMethodAuditor;
use EnvHealth\Auditor\DotEnvAuditor;
use EnvHealth\Auditor\PhpIniAuditor;
use EnvHealth\Auditor\PrivateKeyAuditor;
use EnvHealth\Contract\AuditResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuditCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('audit')
            ->setDescription('Audit your project environment for security vulnerabilities')
            ->setHelp('This command scans your project and returns a Security Health Score based on common vulnerabilities.')
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Project path to audit',
                getcwd()
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectRoot = $input->getOption('path');

        $io->title('ENV-Health Security Audit');

        // Initialize audit runner
        $runner = new AuditRunner();
        $runner->addAuditor(new DotEnvAuditor($projectRoot))
               ->addAuditor(new PrivateKeyAuditor($projectRoot))
               ->addAuditor(new PhpIniAuditor())
               ->addAuditor(new AuthMethodAuditor($projectRoot));

        // Run all audits
        $results = $runner->runAllAudits();

        // Display results in table
        $this->displayResults($output, $results);

        // Calculate and display health score
        $healthScore = $runner->calculateHealthScore($results);
        $this->displayHealthScore($io, $healthScore);

        return Command::SUCCESS;
    }

    private function displayResults(OutputInterface $output, array $results): void
    {
        $table = new Table($output);
        $table->setHeaders(['Check Name', 'Status', 'Suggestion']);

        foreach ($results as $result) {
            $status = $this->colorizeStatus($result->getStatus());
            $table->addRow([
                $result->getCheckName(),
                $status,
                $result->getSuggestion()
            ]);
        }

        $table->render();
        $output->writeln('');
    }

    private function colorizeStatus(string $status): string
    {
        return match ($status) {
            AuditResult::STATUS_PASS => "<fg=green>✓ $status</>",
            AuditResult::STATUS_FAIL => "<fg=red>✗ $status</>",
            AuditResult::STATUS_WARN => "<fg=yellow>⚠ $status</>",
            default => $status,
        };
    }

    private function displayHealthScore(SymfonyStyle $io, int $score): void
    {
        $io->newLine(2);

        if ($score >= 80) {
            $io->success(sprintf(
                "══════════════════════════════════\n" .
                "   SECURITY HEALTH SCORE: %d/100   \n" .
                "══════════════════════════════════\n" .
                "   Status: EXCELLENT ✓",
                $score
            ));
        } elseif ($score >= 50) {
            $io->warning(sprintf(
                "══════════════════════════════════\n" .
                "   SECURITY HEALTH SCORE: %d/100   \n" .
                "══════════════════════════════════\n" .
                "   Status: NEEDS IMPROVEMENT ⚠",
                $score
            ));
        } else {
            $io->error(sprintf(
                "══════════════════════════════════\n" .
                "   SECURITY HEALTH SCORE: %d/100   \n" .
                "══════════════════════════════════\n" .
                "   Status: CRITICAL ✗",
                $score
            ));
        }
    }
}
