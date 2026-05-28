---
title: "notACMS 1.1.4 — Symfony 7.4.13 and Twig 3.27.0 security update"
slug: "blog/release-1-1-4"
description: "1.1.4 is a security-only release: Symfony 7.4.13 and Twig 3.27.0 close 11 CVEs including a firewall bypass, an SSRF bypass, and five Twig sandbox bypasses."
date: 2026-05-28
category: releases
tags: [release, security, symfony]
template: blog/post
---

## Security update: Symfony 7.4.13 and Twig 3.27.0

**Upgrade immediately.** Run `composer update` in your project, then rebuild.

### Symfony 7.4.13 (6 CVEs)

- **CVE-2026-48489** — Security firewall bypass: the `failure_forward` handler honored an attacker-supplied `_failure_path` parameter on the internal subrequest, allowing the firewall to be bypassed.
- **CVE-2026-48736** — SSRF bypass in `NoPrivateNetworkHttpClient` and `IpUtils::PRIVATE_SUBNETS` via IPv6 transition address forms (IPv4-mapped, IPv4-compatible, 6to4, Teredo).
- **CVE-2026-48761** — `HtmlSanitizer` failed to sanitize URL attributes on `<object>`, `<applet>`, `<iframe>`, `<img>`, and the URL inside `<meta http-equiv="refresh">` content.
- **CVE-2026-48760** — `HtmlSanitizer` accepted percent-encoded BiDi marks and Unicode whitespace in URLs, allowing sanitizer bypass via visual spoofing.
- **CVE-2026-48784** — `UrlGenerator` misencoded chained `../` and `./` segments, producing URLs that could traverse out of the intended path.
- **CVE-2026-48747** — Mailer: the Mailomat webhook signature algorithm was not pinned to SHA-256, allowing algorithm-substitution attacks.

Full list: [symfony.com/blog/symfony-7-4-13-released](https://symfony.com/blog/symfony-7-4-13-released).

### Twig 3.27.0 (5 CVEs)

All five are sandbox bypasses. If your theme renders user-controlled templates in a sandbox, these are critical:

- **CVE-2026-46636** — Filter, tag, and function allow-list bypass when sandbox state changes between renders in long-lived workers (e.g. FrankenPHP, RoadRunner).
- **CVE-2026-48808** — `column` filter bypassed the property allowlist under `SourcePolicyInterface`.
- **CVE-2026-48806** — `__toString()` policy bypass via dynamic mapping keys: `{% set arr = {(obj): "value"} %}`.
- **CVE-2026-48807** — `__toString()` policy bypass via `Traversable` objects in the `join`/`replace` filters and the `in`/`not in` operators.
- **CVE-2026-48805** — Sandbox state regression in deprecated internal wrappers (`twig_check_arrow_in_sandbox()`, `twig_array_some()`, `twig_array_every()`).

## Also in 1.1.4

- `symfony/polyfill-*` updated from v1.37.0 to v1.38.1.
- Dev dependencies bumped: `phpstan/phpstan` 2.1.55 → 2.2.1, `phpunit/phpunit` 13.1.10 → 13.1.13, `rector/rector` 2.4.4 → 2.4.5.

## How to upgrade

```bash
composer update
ddev build   # or your equivalent build command
```

No configuration changes or migration steps required.

## Full changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#114---2026-05-28)
