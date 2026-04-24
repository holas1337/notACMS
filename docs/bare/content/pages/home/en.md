---
title: "Welcome to notACMS"
slug: ""
description: "You're looking at the bare wireframe theme. Every feature works — minimal styling, zero opinions."
template: page/home
---

## You're running the bare theme

This is the **bare wireframe** — every notACMS feature works (blog, pages, contact form, search, pagination, multi-language), but with intentionally minimal styling. No dark mode, no search overlay, no fancy footer. System fonts only.

This is your starting point for building a custom design.

## What is notACMS?

notACMS is a static-first CMS built with Symfony. You write Markdown, define your structure in YAML, run one command, and get pure static HTML. No database, no runtime, no surprises.

- **Markdown content** with YAML frontmatter
- **Multi-language routing** with automatic hreflang tags
- **Pagefind search** built into the static output
- **Image processing** — WebP variants, responsive srcset
- **DDEV development** — containerized PHP 8.5, Nginx, build tools
- **Static build** — one command produces deployable HTML

## Want the full demo design?

The demo theme includes dark mode, search overlay, documentation sidebar, amber-phosphor design, and three languages. Switch with:

```bash
./notACMS deploy --demo
```

## Documentation

- [Architecture](https://github.com/holas1337/notACMS/blob/main/docs/ARCHITECTURE.md) — how notACMS works under the hood
- [Customization](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md) — override templates, SCSS, translations
- [Editor Guide](https://github.com/holas1337/notACMS/blob/main/docs/EDITOR_GUIDE.md) — writing content, frontmatter, images