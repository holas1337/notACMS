---
title: "Design Reference"
description: "The notACMS design system — color tokens, typography, components, and SCSS class reference."
template: page/styleguide
slug: design-reference
menu:
  weight: 70
  label: "Design Reference"
---

This page is a living reference for the notACMS design system. Every component shown here maps to a named SCSS class. Override tokens in `local/assets/styles/` to apply your brand.

## Color Tokens

CSS custom properties defined in `assets/styles/_tokens.scss`. Override any of these in `local/assets/styles/` to retheme the entire site. The table below highlights the most commonly overridden tokens — see `_tokens.scss` for the complete set including the `--hero-*`, `--success-*`, `--danger-*`, and `--code-*` groups.

| Token | Light | Dark | Usage |
|---|---|---|---|
| `--bg` | `#ffffff` | `#0e0d0b` | Page background |
| `--bg-surface` | `#f8f9fa` | `#161410` | Elevated surfaces, sidebar |
| `--bg-elevated` | `#f1f3f5` | `#1e1c17` | Buttons, code inline bg |
| `--text` | `#212529` | `#f8f9fa` | Primary text |
| `--text-muted` | `#6c757d` | `#9ca3af` | Secondary text, captions |
| `--border` | `#dee2e6` | `#2d2a24` | Dividers, card borders |
| `--accent` | `#B45309` | `#FFB000` | Links, active states, CTAs |
| `--accent-bg` | `#fffbeb` | `rgba(255,176,0,.08)` | Accent backgrounds |
| `--sidebar-bg` | `#f8f9fa` | `#111009` | Docs sidebar |
| `--code-bg` | `#f6f8fa` | `#0d1117` | Code block background |
| `--nav-bg` | dark glass | dark glass | Always dark |
| `--footer-bg` | `#0e0d0b` | `#0e0d0b` | Always dark |

## Typography

The type scale is defined as SCSS variables in `assets/styles/_variables.scss`.

| Variable | Size | Usage |
|---|---|---|
| `$fs-xs` | 11px | Labels, badges, mono metadata |
| `$fs-sm` | 13px | Captions, small text |
| `$fs-base` | 16px | Default UI text |
| `$fs-lg` | 17px | Prose body text |
| `$fs-xl` | 22px | H2 headings |
| `$fs-xxl` | 28px | H1 small variant |

**Fonts:** `Inter` (UI/body) + `JetBrains Mono` (code, labels, mono elements). Loaded via Google Fonts CDN.

## Prose Components

All Markdown content is wrapped in `.prose`. These classes are defined in `assets/styles/_prose.scss`.

### Headings

H2 headings include a bottom border and `scroll-margin-top` for TOC anchor accuracy. H3 and H4 are progressively smaller and lighter.

### Code Blocks

```bash
# A code block with a bash language label
ddev build
```

```yaml
# YAML config example
site:
  name: "My Site"
  locales:
    en:
      label: "English"
```

### Callouts

Callouts use the `.callout` component (defined in `assets/styles/_components.scss`):

> **Tip:** Use callouts to highlight important information. The blockquote element renders as a styled callout in notACMS prose.

### Tables

See the Color Tokens table above. Tables are styled with a mono font for the header row and accent color for the first data column.

## Navigation Components

### `.sidebar-nav` — Docs sidebar

```
.docs-sidebar
  .sidebar-section
    .sidebar-label       ← "Documentation" heading
    .sidebar-nav
      li > a             ← default link
      li > a.is-active   ← active page
    .sidebar-icon        ← icon wrapper
  .sidebar-divider
```

### `.toc-list` — Right sidebar TOC

Auto-generated from h2/h3 headings in `.docs-content .prose` by `docs-toc.js`. Active item is tracked via `IntersectionObserver`.

### `.prev-next-nav` — Prev/Next

```
.prev-next-nav
  .prev-next-card               ← previous link
  .prev-next-card.prev-next-card--next  ← next link
    .prev-next-dir              ← "Previous" / "Next" label
    .prev-next-title            ← page title
```

## Card Components

### `.feature-card`

Used in the homepage features grid. Contains `.feature-icon` + title + description.

### `.release-card`

Used on the releases list page. Contains `.release-card-meta`, title, excerpt, `.read-more` link.

### `.stat-card`

Used in the homepage "What is notACMS?" section. Contains `.stat-card-num`, `.stat-card-label`, `.stat-card-desc`.

### `.prev-next-card` (blog posts)

The same `.prev-next-nav` component is reused for blog post prev/next navigation.

## Phosphor Icon Reference

Icons are loaded via Phosphor Icons CDN. Use `<i class="ph ph-{name}"></i>` syntax.

| Icon | Class | Usage |
|---|---|---|
| Book open | `ph-book-open` | Manual sidebar link |
| Tree structure | `ph-tree-structure` | Architecture sidebar link |
| Sliders | `ph-sliders` | Customization sidebar link |
| Globe | `ph-globe` | Locales sidebar link |
| Palette | `ph-palette` | Design Reference sidebar link |
| Rocket | `ph-rocket` | Releases sidebar link |
| GitHub logo | `ph-github-logo` | GitHub links |
| Arrow right | `ph-arrow-right` | CTAs, "next" labels |
| Arrow left | `ph-arrow-left` | "previous" labels |
| Arrow square out | `ph-arrow-square-out` | External links |
| Moon | `ph-moon` | Theme toggle (light mode state) |
| Sun | `ph-sun` | Theme toggle (dark mode state) |
| Lightbulb | `ph-lightbulb` | Tip callouts |
| Caret down | `ph-caret-down` | Docs dropdown in nav |
| Lightning | `ph-lightning` | Stats, speed |
| Magnifying glass | `ph-magnifying-glass` | Search |
| Code | `ph-code` | Code/PHP features |
| Markdown logo | `ph-markdown-logo` | Markdown feature |
| List | `ph-list` | Mobile sidebar toggle |
