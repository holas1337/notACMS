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

- **Cloudflare Turnstile** — captcha protection on the contact form prevents spam and abuse. The validator also checks the `hostname` Cloudflare reports against your configured `base_url` (rejecting tokens minted on foreign domains) and logs an error if the always-pass test keys are still active outside debug mode.
- **Path traversal protection** — `MediaFileResolver` validates resolved paths stay within the content directory
- **No database** — the architecture has no SQL injection surface
- **Strict types** — all PHP files use `declare(strict_types=1)` to prevent type coercion vulnerabilities
- **Input validation** — the contact form uses Symfony's Form component with built-in validation constraints

## Threat Model & Design Decisions

notACMS is a **static site builder**: production serves pre-rendered HTML from `public/static/` via nginx. PHP runs at build time (operator's machine) and, optionally, at runtime for exactly one endpoint — `POST /api/contact`. Content authors are trusted (they own the deployment); the adversary is the anonymous web visitor, whose attack surface is nginx plus that single endpoint.

Decisions that follow from this model:

- **Contact form CSRF is intentionally disabled** (`csrf_protection: false` in `ContactType`). Turnstile provides the anti-forgery property: tokens are single-use and bound to the sitekey's allowed domains, so a cross-site attacker cannot mint a valid token, and the endpoint hard-rejects submissions without one. The form is inert when no sitekey is configured — Turnstile is effectively mandatory for the contact feature. **Production deployments MUST replace the committed always-pass test keys** (see Best Practices above); the validator logs an error when they are detected outside debug. Operators who want a different guard can override `ContactType` in `local/src/`. Do not "fix" the disabled CSRF flag without revisiting this reasoning.
- **Draft/scheduled preview endpoints** (`/dev/*/toggle`) and the styleguide are gated on `kernel.debug` — they 404 in production.
- **The build pipeline never executes content** — markdown is parsed by League CommonMark; frontmatter by Symfony YAML without custom tags; image processing shells out to ImageMagick with every argument shell-escaped.
