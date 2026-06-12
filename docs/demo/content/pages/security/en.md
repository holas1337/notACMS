---
title: "Security"
description: "notACMS security model — built-in protections, threat model, and deployment best practices."
template: page/doc
slug: security
menu:
  weight: 80
  label: "Security"
---

## Threat Model

notACMS is a **static site builder**: production serves pre-rendered HTML from `public/static/` via nginx. PHP runs at build time and, optionally, at runtime for one endpoint — `POST /api/contact`. Content authors are trusted (they own the deployment); the adversary is the anonymous web visitor, whose attack surface is nginx plus that single contact endpoint.

This model eliminates whole categories of vulnerability by design:

- **No database** — no SQL injection surface
- **No dynamic page rendering** — pages are pre-built files; nginx serves them directly without executing content
- **Build pipeline never executes content** — Markdown is parsed by League CommonMark; frontmatter by Symfony YAML without custom tags; image processing shells out to ImageMagick with every argument shell-escaped

## Security Features

- **Cloudflare Turnstile** — anti-forgery protection on the contact form. The validator checks the `hostname` Cloudflare reports against your configured `base_url`, rejecting tokens minted on foreign domains. An error is logged when always-pass test keys are detected outside debug mode.
- **Path traversal protection** — `MediaFileResolver` validates that resolved paths stay within the content directory.
- **Strict types** — all PHP files use `declare(strict_types=1)` to prevent type coercion vulnerabilities.
- **Input validation** — the contact form uses Symfony's Form component with built-in validation constraints.
- **Security headers** — nginx emits `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, and a configurable `Content-Security-Policy` on every response.
- **Debug-only endpoints** — draft/scheduled preview toggles and the styleguide are gated on `kernel.debug` and return 404 in production.

## Contact Form CSRF

The contact form has `csrf_protection: false` in `ContactType`. This is intentional: Turnstile provides the anti-forgery property. Turnstile tokens are single-use and bound to the sitekey's allowed domains, so a cross-site attacker cannot mint a valid token. The endpoint hard-rejects submissions without one, and the form is inert when no sitekey is configured — Turnstile is effectively mandatory for the contact feature.

**Production deployments must replace the committed always-pass test keys** in `.env.local`. Operators who want a different anti-forgery guard can override `ContactType` in `local/src/`.

## Deployment Best Practices

- **Never commit `.env.local`** — it contains real secrets and is gitignored for a reason.
- **Rotate demo secrets** — the `.env` file ships with placeholder Turnstile keys; replace them in `.env.local` before deploying.
- **Keep dependencies updated** — run `composer audit` regularly and update packages when security advisories are published.
- **Use HTTPS in production** — the Docker Compose setup includes Certbot for automatic TLS; do not serve the site over plain HTTP.

## Reporting a Vulnerability

Do not open a public issue for security vulnerabilities. Report privately via the [contact form](/contact/).
