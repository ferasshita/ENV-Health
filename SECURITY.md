# Security Policy

## Supported Versions

The following versions of ENV-Health currently receive security updates:

| Version | Supported          |
| ------- | ------------------ |
| Latest  | ✅ Yes             |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability in ENV-Health, please report it responsibly by emailing the maintainer directly. You can find contact details on the [GitHub profile](https://github.com/ferasshita).

When reporting a vulnerability, please include:

- A description of the vulnerability and its potential impact
- Steps to reproduce the issue
- Any proof-of-concept code or examples
- Your suggested fix, if you have one

You can expect an acknowledgement within **48 hours** and a resolution timeline within **7 days** for critical issues.

## Security Considerations

ENV-Health is a CLI security auditing tool that reads local file system metadata (permissions, environment variables, PHP INI settings). It does **not**:

- Transmit data over the network
- Store or log sensitive information
- Require elevated privileges beyond reading project files

However, as with any tool that inspects environment configurations, ensure you run it only in trusted environments and review its output carefully.

## Disclosure Policy

Once a vulnerability is resolved, we will publish a security advisory on GitHub. We follow a coordinated disclosure process and ask reporters to give us reasonable time to fix the issue before any public disclosure.
