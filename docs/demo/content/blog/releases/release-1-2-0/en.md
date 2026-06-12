---
title: "notACMS 1.2.0 — the audit release"
slug: "blog/release-1-2-0"
description: "A full code, security, and theme audit turned into one release: ~170 fixes, a hardened build pipeline, a documented theme API, and a handful of deliberate breaking changes."
date: 2026-06-12
category: releases
tags: [release, security, refactoring]
template: blog/post
---

## The audit release

1.2.0 is the result of a complete audit of notACMS — architecture, security, code quality, and both template trees. Around 170 findings were fixed. The highlights:

### Robustness

- A markdown file with broken frontmatter no longer takes down a locale or the build — it's skipped and reported with its path and error.
- A missing `slug:` can no longer silently hijack the homepage; `posts_per_page: 0` can no longer crash every blog page; duplicate URLs, ambiguous directory keys, and invalid tag values now produce clear build warnings.
- Draft and scheduled **pages** are now genuinely excluded from builds, menus, and `/llms.txt` — previously only posts were filtered.
- `app:build -o <dir>` refuses to wipe a directory other than the configured static dir unless you pass `--force`.
- Visiting a `/pl/` link for the first time no longer bounces you to EN — the URL is treated as your language choice and a cookie is set. The locale cookie is now also `Secure`-conditional so the mechanism works in HTTP dev environments.
- Search result excerpts now render highlighted terms as actual `<mark>` highlights instead of literal `&lt;mark&gt;` text.
- The bare starter theme's `_site.yaml` now includes placeholder `contact_form` values — no more build error on a fresh install.

### Security

- ImageMagick runs through `Symfony\Process` with argv arrays — `exec()` is gone.
- Turnstile now validates the response hostname against your `base_url` and logs an error when the committed always-pass test keys are active in production. A boot check warns when `APP_SECRET` is still the placeholder.
- JSON-LD output is hex-escaped (`</script>` in a title can't break out), translation catalogs no longer carry HTML, and the nginx security headers now also apply to `/assets/` and `/media/` responses.
- The locale-prefixed contact API was unreachable in the Docker runtime deployment due to an nginx regex bug — fixed.

### For theme builders

- New [THEME_BUILDING.md](https://github.com/holas1337/notACMS/blob/main/docs/THEME_BUILDING.md): the full theme API contract — per-route template contexts, Twig functions and globals, the ContentItem API, and the translation keys every theme must define.
- `#[LocalizedRoute]` now works in `local/src/Controller/`, language switching works correctly on 3+-locale sites, and the static build pipeline is decomposed into reusable services.

### New

- The static build now produces a `/llms.txt` feed per locale — the most recent posts in a machine-readable format for LLM context windows. Configurable via `llms_limit` in `_site.yaml` (default: 5). The template is overridable per-theme via the `@base` Twig namespace.

### Breaking changes

A few — each with a one-line migration. `directoryKey()` returns full content paths (basename lookups still work), `getTree()` moved to `ContentTreeProviderInterface`, `blogPosting()` takes a named map, the `lang_switch_url` context key is gone, four dead translation keys were removed, and the 1.0 `old-template` package was dropped. See [UPGRADE-1.2.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.2.md) for the complete guide.
