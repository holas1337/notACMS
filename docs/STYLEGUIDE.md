# Styleguide

A living design system reference for this site, accessible at `/styleguide/` in the dev environment.

> **Site-specific design reference:** design tokens, color palette, typography, and component list for *this* site are documented in `local/docs/STYLEGUIDE.md` (seeded from `docs/examples/docs/STYLEGUIDE.md` on first deploy).

---

## Access

```
https://notacms.ddev.site/styleguide/
```

The page is **dev-only** — the controller returns 404 in any environment other than `dev`. It is not included in the static site build.

The link appears in the main navigation automatically when `APP_ENV=dev`.

---

## Implementation

| File | Purpose |
|---|---|
| `src/Controller/StyleguideController.php` | Dev-only controller; constructs dummy `ContentItem` objects for component demos |
| `templates/page/styleguide.html.twig` | Styleguide template extending `base.html.twig`; overrides `sidebar` block for full-width layout |
| `assets/styles/_styleguide.scss` | Styleguide-specific layout styles (swatches, spacing bars, section separators) |
| `templates/components/navigation.html.twig` | Conditionally appends the Styleguide nav link when `app.environment is same as('dev')` |

---

## Conventions

- **Use real CSS classes** in component demos, not custom `sg-*` wrappers that duplicate existing selectors. Style changes in SCSS then automatically reflect in the styleguide without extra maintenance.
- `sg-*` classes are reserved for scaffolding with no real-page equivalent: section separators, color swatches, spacing bars, type scale specimens.

---

## When to update `local/docs/STYLEGUIDE.md`

| Changed area | What to update |
|---|---|
| New color variable added to `_variables.scss` | Add row to the relevant color table |
| Color value changed | Update the hex value in the table |
| New Twig component added | Add row to the Components table; add a `sg-section` to `styleguide.html.twig` |
| Component removed or renamed | Remove its row; remove or update its `sg-section` |
| New design token (spacing, radius, shadow) | Add row to the relevant token table |
| Custom theme overrides a token | Update the value to reflect the active value |
