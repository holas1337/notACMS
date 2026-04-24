# Styleguide — notACMS Promo Site

Site-specific design reference for the notACMS promotional and documentation site.

**For design tokens, colors, typography, elevation, shapes, and component rationale see [`DESIGN.md`](DESIGN.md).**  
For the styleguide system mechanics, see [`docs/STYLEGUIDE.md`](../../../../docs/STYLEGUIDE.md).

**Live reference:** The Design Reference page at `/design-reference/` shows all components with live examples. This document covers the SCSS structure and naming.

---

## Token Files

| File | Purpose |
|---|---|
| `assets/styles/_tokens.scss` | CSS custom properties — light mode in `:root`, dark overrides in `[data-theme="dark"]`. Nav/footer/hero tokens are always-dark (fixed values). |
| `assets/styles/_variables.scss` | Compile-time SCSS constants — font stacks, spacing scale, radii, shadows, breakpoints, layout dimensions. |

All colors and design tokens are defined in [`DESIGN.md`](DESIGN.md). The SCSS files above are the implementation. Keep them in sync with DESIGN.md.

---

## SCSS File Map

| File | Owns |
|---|---|
| `_components.scss` | `.callout`, `.feature-card`, `.release-card`, `.prev-next-nav`, `.about-profile`, `.recommendation-card`, `.cookie-banner`, `.pagination`, `.tag-pill` |
| `_pages.scss` | Homepage hero/features/stats, about page, contact form, error pages, `.page-header` |
| `_nav.scss` | `.site-header`, nav links, `.search-overlay`, `.theme-toggle`, mobile hamburger |
| `_prose.scss` | `.prose` wrapper — headings, paragraphs, lists, tables, blockquotes, code blocks |
| `_docs.scss` | `.docs-wrapper`, `.docs-sidebar`, `.docs-toc`, `.docs-title`, `sg-icon-*` |
| `_blog.scss` | `.releases-list`, blog post body, post meta |
| `_styleguide.scss` | `sg-*` scaffolding classes for the Design Reference page only |

---

## Tokens → SCSS Variables Mapping

DESIGN.md tokens map to SCSS compile-time variables in `_variables.scss`:

| DESIGN.md token | SCSS variable | Value |
|---|---|---|
| `spacing.xs` | `$sp-1` | `0.25rem` (4px) |
| `spacing.sm` | `$sp-2` | `0.5rem` (8px) |
| `spacing.md` | `$sp-3` | `1rem` (16px) |
| `spacing.lg` | `$sp-4` | `1.5rem` (24px) |
| `spacing.xl` | `$sp-5` | `2rem` (32px) |
| `spacing.xxl` | `$sp-6` | `3rem` (48px) |
| `rounded.sm` | `$radius-sm` | `4px` |
| `rounded.md` | `$radius` | `8px` |
| `rounded.lg` | `$radius-md` | `10px` |
| `rounded.xl` | `$radius-lg` | `12px` |
| `rounded.xxl` | `$radius-xl` | `16px` |
| `rounded.pill` | `$radius-pill` | `50rem` |

### Shadow scale

| SCSS variable | Value | Usage |
|---|---|---|
| `$shadow-sm` | `0 1px 2px rgba(0,0,0,0.06)` | Subtle lift on hover |
| `$shadow` | `0 2px 8px rgba(0,0,0,0.08)` | Cards, dropdowns |
| `$shadow-lg` | `0 8px 24px rgba(0,0,0,0.12)` | Popovers, nav dropdowns |
| `$shadow-xl` | `0 24px 80px rgba(0,0,0,0.35)` | Modal-level elevation (search overlay) |

When adding new tokens to DESIGN.md, add the corresponding SCSS variable to keep the implementation in sync.

## Breakpoints

| SCSS variable | Width | Typical use |
|---|---|---|
| `$bp-sm` | 576px | Single-column stack |
| `$bp-md` | 768px | Tablet |
| `$bp-lg` | 992px | Mobile nav breakpoint |
| `$bp-xl` | 1100px | Docs 3-column layout |
| `$bp-xxl` | 1280px | Max container width |

---

## Do's and Don'ts

- **Do** use CSS custom properties in component templates, never hardcoded hex
- **Do** maintain WCAG AA contrast ratios (4.5:1 for normal text)
- **Do** keep Inter and JetBrains Mono strictly separated — one for body, one for code/labels
- **Don't** use `$fs-*` SCSS variables in component templates — they compile to static values; use `rem` equivalents if needed in local SCSS
- **Don't** create new CSS classes in local overrides that duplicate existing component patterns
