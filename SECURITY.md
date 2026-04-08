# Security Policy

## Supported Versions

Only the latest release receives security updates. If you are using an older version, please upgrade before reporting security issues.

## Reporting a Vulnerability

**Do not open a public issue** for security vulnerabilities.

Instead, report a vulnerability privately via the contact form:

> **https://holas.pl/contact/**

Include the following in your report:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if you have one)

You will receive a response as soon as possible.

## What to Expect

1. **Triage** — I will confirm or deny the vulnerability
2. **Fix** — If confirmed, I will develop and test a fix
3. **Release** — A patched version will be released with a security advisory
4. **Disclosure** — After the patch is available, I will publish a public disclosure (with credit to the reporter, if desired)

## Security Best Practices for Users

- **Never commit `.env.local`** — it contains your real secrets and is gitignored for a reason
- **Rotate demo secrets** — the `.env` file ships with placeholder Turnstile keys; replace them in `.env.local` before deploying
- **Keep dependencies updated** — run `composer audit` regularly and update packages when security advisories are published
- **Use HTTPS in production** — the Docker Compose setup includes Certbot for automatic TLS; do not serve the site over plain HTTP

## Security Features

- **Cloudflare Turnstile** — captcha protection on the contact form prevents spam and abuse
- **Path traversal protection** — `MediaFileResolver` validates resolved paths stay within the content directory
- **No database** — the architecture has no SQL injection surface
- **Strict types** — all PHP files use `declare(strict_types=1)` to prevent type coercion vulnerabilities
- **Input validation** — the contact form uses Symfony's Form component with built-in validation constraints
