---
title: "notACMS v1.0.0"
slug: "blog/release-1-0-0"
description: "The first stable release — core content pipeline, multilingual routing, Pagefind search, image processing, and DDEV-based local development."
date: 2026-04-09
category: releases
tags: [release, announcement]
template: blog/post
---

## What's in v1.0.0

After several months of internal use on personal projects, I'm tagging the first stable release. The core feature set is solid enough to build real sites with.

### Content pipeline

The content pipeline is the heart of notACMS. It reads your `local/content/` directory, parses frontmatter, and generates a complete static site in one command.

- Markdown content with YAML frontmatter
- CommonMark rendering with heading permalinks
- Excerpt generation from prose content
- Reading time calculation

### Multilingual routing

Locales are defined in `_site.yaml`. Each locale gets its own URL space, with optional path overrides in `_routes.yaml`. hreflang tags are generated automatically.

```yaml
site:
  locales:
    en:
      label: "English"
      date_format: "M d, Y"
    pl:
      label: "Polski"
      date_format: "d.m.Y"
```

### Pagefind search

Full-text search is built into the static output via [Pagefind](https://pagefind.app/). The build command generates the search index automatically. No external API, no server-side search — just a static index that works offline.

### Image processing

Images co-located with content are processed during build. notACMS generates WebP variants at multiple widths, automatically updates `src` attributes with responsive `srcset`, and handles the path mapping between content and output directories.

### DDEV local development

The development environment is fully containerized with DDEV. `ddev start` gives you PHP 8.5, Nginx, and all build tools. `ddev build` produces the static output. `ddev code-check` runs PHPStan and PHP CS Fixer.

## Breaking changes

This is the first stable release. If you've been using a pre-1.0 version, review the `_site.yaml` schema — the `social` key changed from a list to a map.

## Upgrade

```bash
git pull
ddev composer install
ddev build
```

## What's next

v1.1.0 will focus on the design system and documentation. This site — built with notACMS — will become the official documentation and design reference.
