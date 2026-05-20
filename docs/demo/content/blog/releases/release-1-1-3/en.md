---
title: "notACMS 1.1.3 — JSON Schema files and Symfony 7.4.12 security update"
slug: "blog/release-1-1-3"
description: "1.1.3 ships JSON Schema files for all config and frontmatter, fixes stale Pagefind fragments, and updates Symfony 7.4.12 + Twig 3.26.0 to close 24 CVEs."
date: 2026-05-20
category: releases
tags: [release, security, symfony]
template: blog/post
---

## JSON Schema files for config and frontmatter

Six JSON Schema draft-07 files now live in `config/schema/`:

| Schema file | Describes |
|---|---|
| `site.schema.json` | `_site.yaml` — site-wide settings, locales, social links, contact form |
| `routes.schema.json` | `_routes.yaml` — locale URL path overrides |
| `tags.schema.json` | `_tags.yaml` — tag translation map |
| `post.frontmatter.schema.json` | Blog post Markdown frontmatter |
| `page.frontmatter.schema.json` | Static page Markdown frontmatter |
| `category.frontmatter.schema.json` | Category index Markdown frontmatter |

All template YAML files (`_site.yaml`, `_routes.yaml`, `_tags.yaml`) in `docs/bare/`, `docs/demo/`, and `docs/customization/old-template/` now carry a `# yaml-language-server: $schema=` comment pointing to the raw GitHub URL. VS Code (and any editor with YAML Language Server) picks these up automatically — you get validation and autocomplete in the content files without any project configuration.

Schemas are optimised for AI-assisted authoring: descriptions include defaults, constraints, and behaviour explanation so an AI agent working in another project can fetch a schema and know exactly what each field does.

Fetch any schema directly from the main branch:

```
https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/<name>.schema.json
```

## Security update: Symfony 7.4.12 and Twig 3.26.0

**Upgrade immediately.** Run `composer update` in your project, then rebuild.

### Symfony 7.4.12 (21 CVEs)

The most impactful for a CMS deployment:

- **CVE-2026-45073** — SQL injection in `Cache` via unsanitized `$prefix` in `PdoAdapter::doClear()`.
- **CVE-2026-45071** — XXE / local file disclosure in `DomCrawler::addXmlContent()` when `validateOnParse` is enabled.
- **CVE-2026-45075** — HEAD requests bypass the `methods` filter on `#[IsGranted]`, `#[IsCsrfTokenValid]`, and `#[IsSignatureValid]` attributes.
- **CVE-2026-45072** — XSS in `TwigBridge`'s `CodeExtension::fileExcerpt()`.
- **CVE-2026-45068** — Header injection in `SendmailTransport`; addresses starting with a dash are now rejected.
- **CVE-2026-45067** — Email addresses containing line breaks accepted in `Mime\Address` — now rejected.
- **CVE-2026-45305 / 45304 / 45133** — Catastrophic backtracking and unbounded recursion in the YAML parser.
- **CVE-2026-45066 / 45064 / 45753** — Three `HtmlSanitizer` bypasses: BiDi override characters, URL parser differentials, and unsanitized `action`/`formaction`/`poster`/`cite` attributes.

Full list: [symfony.com/blog/symfony-7-4-12-released](https://symfony.com/blog/symfony-7-4-12-released).

### Twig 3.26.0 (4 CVEs)

All four are sandbox bypasses. If your theme uses Twig's sandbox for user-supplied templates, these are critical:

- **CVE-2026-46635** — Property allowlist bypass via the `column` filter (`array_column` on objects).
- **CVE-2026-46638** — `{% sandbox %}{% include %}` skips `checkSecurity()` on cached templates; incomplete fix for CVE-2024-45411.
- **CVE-2026-24425** — Sandbox bypass when using a source policy.
- **CVE-2026-47732** — Multiple `__toString()` policy bypasses via unguarded string coercion.

## Also in 1.1.3

- **Stale Pagefind fragments fixed** — `scripts/rebuild-content.sh` now wipes `public/pagefind/` before reindexing. Previously, removing or renaming content left orphaned fragment files that Pagefind served alongside fresh results.
- **DESIGN.md improvements** — hardcoded hex values replaced with token references in both bare and demo `DESIGN.md` files; `primary` color alias added; `card-hover` component token added to the demo theme.

## How to upgrade

```bash
composer update
ddev build   # or your equivalent build command
```

No configuration changes or migration steps required.

## Full changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#113---2026-05-20)
