# ENV-Health 🔐

A PHP 8.3+ CLI security auditor that scans your project environment and returns a comprehensive Security Health Score based on common vulnerabilities.

## Features

- 🔍 **Comprehensive Security Audits**: Four specialized auditors to check different aspects of your project's security
- 📊 **Health Score**: Get an overall security score out of 100 with color-coded feedback
- 🎨 **Beautiful CLI Output**: Uses Symfony Console with tables and colors for easy-to-read results
- ✅ **Fully Tested**: Complete PHPUnit test suite with mocked file permissions
- 🔌 **Extensible**: Easy to add custom auditors using the AuditorInterface

## Installation

### Via Composer

```bash
composer require ferasshita/env-health
```

### From Source

```bash
git clone https://github.com/ferasshita/ENV-Health.git
cd ENV-Health
composer install
```

## Usage

Run the security audit in your project directory:

```bash
./vendor/bin/env-health
```

Or specify a custom path:

```bash
./vendor/bin/env-health --path=/path/to/project
```

## Security Auditors

### 1. DotEnv Auditor
Checks if `.env` file exists and verifies its permissions.

**Checks:**
- File existence
- Permissions should be `0600` or `0640`
- Not globally readable

**Score:**
- ✅ **PASS (100)**: Secure permissions (0600 or 0640)
- ⚠️ **WARN (80)**: No .env file found
- ⚠️ **WARN (50)**: Non-optimal permissions
- ❌ **FAIL (0)**: Globally readable

### 2. Private Key Auditor
Scans the project for `.pem` or `.key` files and validates their security.

**Checks:**
- Finds all private key files in the project
- Ensures keys are not in public directories (e.g., `/public`)
- Verifies permissions are set to `0600`

**Score:**
- ✅ **PASS (100)**: No keys found OR all keys are secure
- ❌ **FAIL (0)**: Keys in public directory OR wrong permissions

### 3. PHP Configuration Auditor
Checks PHP configuration settings for production readiness.

**Checks:**
- `display_errors` should be OFF
- `allow_url_fopen` is flagged as a potential risk

**Score:**
- ✅ **PASS (100)**: All settings secure
- ⚠️ **WARN (70)**: allow_url_fopen enabled
- ❌ **FAIL (30)**: display_errors enabled

### 4. Auth Method Auditor
Evaluates database authentication methods.

**Checks:**
- Looks for `DB_SSL_KEY` in environment (preferred)
- Checks for `DB_PASSWORD` (less secure)

**Score:**
- ✅ **PASS (100)**: Using SSL keys for authentication
- ⚠️ **WARN (80)**: No database authentication configured
- ⚠️ **WARN (60)**: Using password authentication

## Output Example

```
ENV-Health Security Audit
=========================

+--------------------------------------+--------+------------------------------------------------------------------------------------+
| Check Name                           | Status | Suggestion                                                                         |
+--------------------------------------+--------+------------------------------------------------------------------------------------+
| DotEnv Security Check                | ✓ PASS | .env file has secure permissions.                                                  |
| Private Key Security Check           | ✓ PASS | No private key files found in project.                                             |
| PHP Configuration Check              | ⚠ WARN | Warnings: allow_url_fopen is ON (potential security risk)                          |
| Database Authentication Method Check | ✓ PASS | Using SSL keys for database authentication. Excellent security practice!           |
+--------------------------------------+--------+------------------------------------------------------------------------------------+


 [OK] ══════════════════════════════════
        SECURITY HEALTH SCORE: 93/100
      ══════════════════════════════════
        Status: EXCELLENT ✓
```

## Color-Coded Health Scores

- 🟢 **80-100**: EXCELLENT ✓ (Green)
- 🟡 **50-79**: NEEDS IMPROVEMENT ⚠ (Yellow)
- 🔴 **0-49**: CRITICAL ✗ (Red)

## Development

### Running Tests

```bash
composer test
# or
./vendor/bin/phpunit
```

### Project Structure

```
ENV-Health/
├── bin/
│   └── env-health          # CLI executable
├── src/
│   ├── Contract/
│   │   ├── AuditorInterface.php
│   │   └── AuditResult.php
│   ├── Auditor/
│   │   ├── DotEnvAuditor.php
│   │   ├── PrivateKeyAuditor.php
│   │   ├── PhpIniAuditor.php
│   │   └── AuthMethodAuditor.php
│   ├── Command/
│   │   └── AuditCommand.php
│   └── AuditRunner.php
├── tests/
│   ├── Auditor/
│   └── AuditRunnerTest.php
└── composer.json
```

### Creating Custom Auditors

You can easily create your own auditors by implementing the `AuditorInterface`:

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
        // Your audit logic here
        $isSecure = true; // Your check

        if ($isSecure) {
            return new AuditResult(
                $this->getName(),
                AuditResult::STATUS_PASS,
                100,
                'Everything is secure!'
            );
        }

        return new AuditResult(
            $this->getName(),
            AuditResult::STATUS_FAIL,
            0,
            'Security issue found!'
        );
    }
}
```

Then add it to the runner in your custom command or script.

## Requirements

- PHP 8.3 or higher
- Symfony Console ^7.0
- Composer

## Tech Stack

- **PHP 8.3+**: Modern PHP with strict types
- **Symfony Console**: For beautiful CLI output
- **PHPUnit 11**: For comprehensive testing
- **PSR-4 Autoloading**: For organized code structure

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced under the MIT License. See the [LICENSE](LICENSE) file for details.

## Security

If you discover any security issues, please email the maintainers instead of using the issue tracker.

## Credits

Created by Feras Shita

---

**⭐ Star this repository if you find it helpful!**
