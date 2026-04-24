# Styleguide

A living design system reference for this site, accessible at `/styleguide/` in the dev environment.

> **Core ships a minimal styleguide; demo overrides it.** The `/styleguide/` route and a wireframe template live in core so the page works on both themes. The **demo theme** (in `docs/demo/templates/page/styleguide.html.twig` and `docs/demo/assets/styles/`) ships a rich component reference — the one you see on notacms.holas.pl. If you built with `--bare`, you get the minimal version until you copy the demo template (and SCSS) into `local/`.

> **Site-specific design reference:** design tokens, color palette, typography, and component list for *this* site are documented in `local/docs/STYLEGUIDE.md` (seeded from `docs/demo/docs/STYLEGUIDE.md` on first deploy).

---

## Access

```
https://notacms.ddev.site/styleguide/
```

The page is **dev-only** — the controller returns 404 when `kernel.debug` is false. It is not included in the static site build. The route is locale-independent: a single `/styleguide/` URL with no `/pl/` or `/de/` prefix.

This page is separate from the `/design-reference/` user-facing content page: `/styleguide/` renders live components from dummy data for visual regression, while `/design-reference/` is an editable Markdown page describing the design system for end users. The main navigation links to `/design-reference/`, not `/styleguide/`.

---

## Implementation

| File | Purpose |
|---|---|
| `src/Controller/StyleguideController.php` | Dev-only controller gated by `kernel.debug`; constructs dummy `ContentItem` objects for component demos |
| `templates/page/styleguide.html.twig` | Styleguide template extending `base.html.twig`; overrides `sidebar` block for full-width layout |
| `assets/styles/_styleguide.scss` | Styleguide-specific layout styles (swatches, spacing bars, section separators) |

---

## Conventions

- **Use real CSS classes** in component demos, not custom `sg-*` wrappers that duplicate existing selectors. Style changes in SCSS then automatically reflect in the styleguide without extra maintenance.
- `sg-*` classes are reserved for scaffolding with no real-page equivalent: section separators, color swatches, spacing bars, type scale specimens.

---

## When to update `local/docs/STYLEGUIDE.md`

| Changed area | What to update |
|---|---|
| New Twig component added | Add row to the Components table; add a `sg-section` to `styleguide.html.twig` |
| Component removed or renamed | Remove its row; remove or update its `sg-section` |
| New design token (spacing, radius, shadow) | Update `local/docs/DESIGN.md` — not STYLEGUIDE.md (no token tables there) |
| Token value changed | Update `local/docs/DESIGN.md` — not STYLEGUIDE.md |
| Custom theme overrides a token | Update `local/docs/DESIGN.md` — not STYLEGUIDE.md |
| New SCSS mixin added | Add description + usage example to STYLEGUIDE.md Mixins section |
| Component usage / variables changed | Update the variable list in the STYLEGUIDE.md component table |
