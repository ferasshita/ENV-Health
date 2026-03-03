# Contributing to ENV-Health

Thank you for your interest in contributing to ENV-Health! Contributions of all kinds are welcome — bug reports, feature requests, documentation improvements, and code changes.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
  - [Reporting Bugs](#reporting-bugs)
  - [Suggesting Features](#suggesting-features)
  - [Submitting Pull Requests](#submitting-pull-requests)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Creating Custom Auditors](#creating-custom-auditors)

## Code of Conduct

This project adheres to a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## Getting Started

1. **Fork** the repository on GitHub.
2. **Clone** your fork locally:
   ```bash
   git clone https://github.com/your-username/ENV-Health.git
   cd ENV-Health
   ```
3. **Install** dependencies:
   ```bash
   composer install
   ```
4. **Create a branch** for your changes:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## How to Contribute

### Reporting Bugs

Before opening a bug report, please search existing issues to avoid duplicates.

When filing a bug report, use the **Bug Report** issue template and include:
- A clear description of the problem
- Steps to reproduce the issue
- Expected vs. actual behavior
- Your PHP version and operating system
- Relevant error output or stack traces

### Suggesting Features

Feature requests are welcome! Use the **Feature Request** issue template and describe:
- The problem you are trying to solve
- Your proposed solution
- Any alternatives you have considered

### Submitting Pull Requests

1. Ensure your branch is up to date with `main`.
2. Write or update tests to cover your changes.
3. Run the full test suite and confirm it passes:
   ```bash
   composer test
   ```
4. Follow the [Coding Standards](#coding-standards) described below.
5. Open a pull request against the `main` branch using the provided PR template.
6. Respond to any review feedback promptly.

## Development Setup

**Requirements:**
- PHP 8.3 or higher
- Composer

**Install dependencies:**
```bash
composer install
```

**Run the CLI tool:**
```bash
./bin/env-health
# or with a custom path:
./bin/env-health --path=/path/to/project
```

## Coding Standards

- Use **strict types** (`declare(strict_types=1);`) in every PHP file.
- Follow **PSR-4** autoloading conventions; classes live under the `EnvHealth\` namespace in `src/`.
- Keep methods focused and testable — avoid side effects in constructors.
- Add return types and parameter types to all functions.

## Testing

Tests are written with **PHPUnit 11** and live in the `tests/` directory.

```bash
# Run all tests
composer test

# Or directly
./vendor/bin/phpunit
```

When adding a new auditor or changing existing logic, please add or update the corresponding test file in `tests/Auditor/`.

## Creating Custom Auditors

You can extend ENV-Health by implementing `AuditorInterface`:

```php
<?php

namespace YourNamespace;

use EnvHealth\Contract\AuditorInterface;
use EnvHealth\Contract\AuditResult;

class CustomAuditor implements AuditorInterface
{
    public function getName(): string
    {
        return 'Custom Security Check';
    }

    public function audit(): AuditResult
    {
        $isSecure = true; // Your check logic

        return new AuditResult(
            $this->getName(),
            $isSecure ? AuditResult::STATUS_PASS : AuditResult::STATUS_FAIL,
            $isSecure ? 100 : 0,
            $isSecure ? 'Everything is secure!' : 'Security issue found!'
        );
    }
}
```

See [README.md](README.md) for more details.

---

Thank you for helping make ENV-Health better! 🎉
