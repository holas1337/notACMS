---
title: "notACMS 1.1.0 — Bare core, demo theme, pick your own"
slug: "blog/release-1-1-0"
description: "1.1.0 splits notACMS into a minimal bare core and an optional demo theme. Choose your starting point with --bare or --demo; customize everything through local/ without touching core."
date: 2026-04-24
category: releases
tags: [release, announcement]
template: blog/post
---

## Two themes, one pick on first build

The headline change in 1.1.0 is that notACMS now ships two themes out of the box, and you pick one on your very first build:

```bash
./notACMS deploy --demo    # default — the amber-phosphor design you see here
./notACMS deploy --bare    # a minimal wireframe: system fonts, light mode, ~200 lines of CSS
```

`ddev build` supports the same flags. Whichever you choose, every feature works: multi-language content, blog, RSS, sitemap, search, contact form, images with responsive variants, reading time, reading progress. The difference is only in how it looks — and, critically, in how much you inherit before you start customising.

### Bare core

The core at `templates/`, `assets/`, `translations/` is now a wireframe. It's intentionally minimal so that overriding a single block doesn't drag in a visual language you have to fight. If you're building a bespoke design, start from bare and you'll own the full look from day one.

### Demo theme

The demo lives at `docs/demo/` and is copied into `local/` when you choose `--demo`. It's the full amber-phosphor design you're reading now — dark mode, search overlay, docs sidebar, theme toggle, the lot. Start here if you want a polished design today and plan to tweak, not rebuild.

## The `local/` override system

Both themes consume the same mechanism: every file in `local/` takes precedence over the matching core path.

| Layer | Override location | Resolver |
|---|---|---|
| Twig templates | `local/templates/*.html.twig` | Symfony kernel |
| SCSS entrypoint | `local/assets/styles/app_local.scss` | Sass bundle |
| JS entrypoint | `local/assets/app.js` | AssetMapper importmap |
| Translations | `local/translations/messages.*.yaml` | Symfony translator |
| Content | `local/content/**` | `notacms_content` parameter |
| Nginx snippets | `local/docker/nginx/*.conf` | Container entrypoint |

Core is never edited. `git pull` stays clean. Customisations live in your site's repo, not in a fork of this one.

## Upgrading from 1.0.0

If you were running 1.0.0 and want your site to look exactly as it did, the compatibility package is a one-liner:

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

That drops the complete pre-1.1.0 theme — templates, SCSS, fonts, images, translations — into `local/`. Your site renders exactly as before. Then you can remove files from `local/` selectively as you adopt new design elements.

The breaking changes you'll actually hit if you customised the old core:

- **SCSS entrypoint renamed**: `local/assets/styles/local.scss` → `local/assets/styles/app_local.scss`. Rename the file and update the import in `local/assets/app.js`.
- **Core colour SCSS variables replaced with CSS custom properties**: `$color-body` → `var(--text)`, `$color-link` → `var(--accent)`, and so on. Full mapping in [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md).
- **Translation keys removed** from core (still present in the demo theme): `header.tagline`, `nav.projects`, `nav.search`, `blog.published_on`, `blog.comments_disabled`. If your templates call `'...'|trans` on those, update them.
- **`docs/examples/` directory removed**: the useful bits moved to `docs/customization/old-template/`; the scratch override examples were superseded by the bare/demo model.

See [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md) for the full list with before/after snippets.

## Also new in 1.1.0

- **Reading time** and **reading progress** on posts, docs, and releases.
- **Language switcher** as a Twig extension (`lang_switch_urls`) with proper fallback chains for archives, paginated lists, and home pages.
- **Post excerpts strip heading anchors** — no more stray `#` characters in listing cards.
- **Test suite scaffolding** at `tests/Unit/`, `tests/Integration/`, `tests/Fixtures/` with initial coverage and a `test` command runnable from the host.
- **AI-agent skills** under `.claude/skills/` for working with the repo: adding locales, doc alignment checks, site sweeps, translations, and upgrade-guide generation.

## Full changelog

Every change with its category: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#110---2026-04-24).
