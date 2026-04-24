# Styleguide — notACMS Bare Theme

Site-specific design reference for the bare wireframe theme.

**For design tokens, colors, typography, elevation, shapes, and component rationale see [`DESIGN.md`](DESIGN.md).**  
For the styleguide system mechanics, see [`docs/STYLEGUIDE.md`](../../docs/STYLEGUIDE.md).

**Live reference:** The Styleguide page at `/styleguide/` shows all components with live examples.

---

## Philosophy

The bare theme is intentionally minimal. It provides:
- A readable, functional wireframe for every notACMS feature
- A clean starting point for custom theming
- System fonts only — no external font dependencies
- No dark mode (light mode only)
- No decorative gradients or shadows

Override everything in `local/assets/styles/` and `local/templates/`.

---

## Token Files

| File | Purpose |
|---|---|
| `assets/styles/_tokens.scss` | CSS custom properties — light mode only. No dark-mode overrides. |
| `assets/styles/_variables.scss` | Compile-time SCSS constants — font stacks, spacing scale, radii, breakpoints. |

All colors and design tokens are defined in [`DESIGN.md`](DESIGN.md). The SCSS files above are the implementation. Keep them in sync with DESIGN.md — if you change a token in DESIGN.md, update the matching variable or custom property in SCSS.

---

## SCSS File Map

| File | Owns |
|---|---|
| `_components.scss` | `.post-card`, `.post-navigation`, `.breadcrumb`, `.pagination`, `.badge`, `.alert`, `.contact-form`, `.widget`, `.cookie-banner`, `.series-nav`, `.share-buttons` |
| `_pages.scss` | `.home-hero`, `.home-section`, `.home-features`, `.home-steps`, `.home-cta`, `.projects-list` |
| `_nav.scss` | `.site-header`, `.nav-toggle`, `#nav-list` |
| `_prose.scss` | `.prose` wrapper — headings, paragraphs, lists, tables, blockquotes, code blocks |
| `_blog.scss` | `.reading-progress-bar`, `.post-featured-image`, `.code-copy-btn`, `#lightbox` |
| `_styleguide.scss` | `sg-*` scaffolding classes for the Styleguide page only |
| `_utilities.scss` | Atomic utility classes — `.flex-between`, `.border-bottom`, `.mt-5`, `.text-muted-sm`, etc. |

---

## Tokens → SCSS Variables Mapping

DESIGN.md tokens map to SCSS compile-time variables in `_variables.scss` and custom properties in `_tokens.scss`:

| DESIGN.md token | SCSS variable | Value |
|---|---|---|
| `spacing.xs` | `$sp-1` | `0.25rem` (4px) |
| `spacing.sm` | `$sp-2` | `0.5rem` (8px) |
| `spacing.md` | `$sp-3` | `1rem` (16px) |
| `spacing.lg` | `$sp-4` | `1.5rem` (24px) |
| `spacing.xl` | `$sp-5` | `2rem` (32px) |
| `spacing.xxl` | `$sp-6` | `3rem` (48px) |
| `rounded.sm` | `$radius-sm` | `3px` |
| `rounded.md` | `$radius` | `4px` |
| `rounded.lg` | `$radius-md` | `6px` |

When adding new tokens to DESIGN.md, add the corresponding SCSS variable to keep the implementation in sync.

---

## Utility Classes

The bare theme uses atomic utility classes in components. Defined in `_utilities.scss`:

| Class | Effect |
|---|---|
| `.text-muted-sm` | `font-size: $fs-sm; color: var(--text-muted)` |
| `.text-sm` | `font-size: $fs-sm` |
| `.text-xs` | `font-size: $fs-xs` |
| `.border-bottom` | `border-bottom: 1px solid var(--border)` |
| `.border-top` | `border-top: 1px solid var(--border)` |
| `.border-left` | `border-left: 3px solid var(--border)` |
| `.flex` | `display: flex` |
| `.flex-between` | `display: flex; justify-content: space-between; align-items: center` |
| `.flex-wrap` | `flex-wrap: wrap` |
| `.gap-sm` | `gap: $sp-1` |
| `.gap-md` | `gap: $sp-2` |
| `.mb-3` | `margin-bottom: $sp-3` |
| `.mt-5` | `margin-top: $sp-5` |
| `.pt-4` | `padding-top: $sp-4` |

Use these in templates to reduce custom component CSS. Example:

```twig
<footer class="flex-between flex-wrap gap-md">
```

---

## Do's and Don'ts

- **Do** use CSS custom properties (`--accent`, `--bg`, `--text-muted`) for colors
- **Do** use utility classes when a pattern repeats across components
- **Do** keep components small and focused — prefer composition over monolithic CSS
- **Do** override in `local/assets/styles/` rather than edit core SCSS
- **Don't** add dark-mode tokens — bare theme is light-mode only
- **Don't** use drop shadows for elevation — borders and background contrast are sufficient
- **Don't** import external fonts — bare theme uses system fonts only
- **Don't** create new SCSS component files for one-off layouts — use utilities
