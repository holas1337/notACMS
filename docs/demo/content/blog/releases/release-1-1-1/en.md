---
title: "notACMS 1.1.1 — Deploy respects existing content"
slug: "blog/release-1-1-1"
description: "1.1.1 fixes a bug where ./notACMS deploy --prod would overwrite existing local/ content. Deploy now only seeds when local/ is missing or empty — matching ddev build behaviour."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Frontmatter-driven navigation labels

Navigation labels for content pages now come from frontmatter — not translation keys. Set `menu.label` in any page's frontmatter and it becomes the label in the navigation; omit it and the page `title` is used as fallback.

```yaml
---
title: "Architecture guide"
slug: "architecture-guide"
menu:
  label: "Architecture"
  weight: 30
---
```

New `content_item()` Twig function resolves any content page by directory key:

```twig
{{ content_item('architecture-guide', 'en').menuLabel() }}
{# → "Architecture" (or the page title if menu.label is absent) #}
```

**Why this matters:** Previously every locale's `messages.*.yaml` had to duplicate translations for `nav.home`, `nav.blog`, `nav.about`, `nav.contact`, `nav.privacy_policy` (core) and `site.releases`, `site.about`, `site.manual`, `site.architecture`, `site.customization`, `site.locales`, `site.design_reference`, `site.contact` (demo). Adding a page meant updating N translation files. Now it's one frontmatter field. Custom templates referencing the removed keys should switch to `content_item('key', locale).menuLabel()`.

## Deploy no longer overwrites existing `local/`

### The bug

Running `./notACMS deploy --prod` would **back up and replace** your entire `local/` directory with `docs/demo/` on every deploy — even when it already contained your content, templates, and customisations. The seed logic didn't distinguish between "user explicitly requested a theme re-seed" and "user just wants to redeploy with existing content."

### The fix

Deploy now works exactly like `ddev build`:

- **No `--bare` / `--demo` flag** → seeds `local/` only if it's missing or empty; skips if it has content.
- **`--bare` or `--demo` passed explicitly** → backs up existing `local/` and seeds the chosen theme.

```bash
./notACMS deploy --prod          # preserves existing local/
./notACMS deploy --prod --demo   # forces re-seed from docs/demo/
./notACMS deploy --prod --bare   # forces re-seed from docs/bare/
```

`--prod` now only controls Composer flags (`--no-dev`) and the runtime environment (`APP_ENV=prod`). It never touches `local/`.

## Also in 1.1.1

- **Dependency bump:** `symfony/polyfill-*` packages updated from v1.36.0 to v1.37.0.
- **Polish, German, and French demo content** reviewed and improved across all pages and blog posts.
- **Translation style guides** added to the AI-agent skill system for Polish, German, and French to ensure consistent quality in future translations.

## Full changelog

Every change with its category: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#111---2026-04-26).
