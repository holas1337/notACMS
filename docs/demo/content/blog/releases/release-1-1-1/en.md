---
title: "notACMS 1.1.1 — Deploy respects existing content"
slug: "blog/release-1-1-1"
description: "1.1.1 fixes a bug where ./notACMS deploy --prod would overwrite existing local/ content. Deploy now only seeds when local/ is missing or empty — matching ddev build behaviour."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## The bug

Running `./notACMS deploy --prod` would **back up and replace** your entire `local/` directory with `docs/demo/` on every deploy — even when it already contained your content, templates, and customisations. The seed logic didn't distinguish between "user explicitly requested a theme re-seed" and "user just wants to redeploy with existing content."

## The fix

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
