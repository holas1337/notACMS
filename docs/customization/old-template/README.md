# Old Template

Full standalone override that restores the pre-redesign notACMS look and feel after upgrading to the new core.

## What It Does

- Restores the `site-branding` two-column header (name + tagline) instead of the SVG logo
- Brings back the Projects link in main navigation
- Restores original SCSS layout, variables, and component styles
- Includes old fonts, images (favicon, OG default), and translations (with `header.tagline`)
- Replaces all core templates to match the pre-redesign HTML structure

## How to Use

Copy everything to your `local/` directory:

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

Your site will render exactly as it did before the redesign. You can then selectively remove individual files from `local/` to adopt new design elements one at a time.

> **Note:** This override does not include `content/` — your existing site content stays in place.

## Migrating Previous Customizations

If you had customizations on top of the old template, apply them after copying old-template to `local/`:

### Custom SCSS

If you had `local/assets/styles/local.scss` with extra rules, import it at the end of `app_local.scss`:

```scss
// at the bottom of assets/styles/app_local.scss
@import 'local';
```

Old SCSS variables (`$color-text`, `$font-body`, etc.) are still available — they come from `_variables.scss`.

### Custom templates extending `@base/base.html.twig`

`@base` always points to the **new** core `templates/` directory, not to this override. If your custom template extended the old base, change the extends target:

```twig
{# Before — extends new core, wrong after upgrade #}
{% extends '@base/base.html.twig' %}

{# After — extends old-template's base.html.twig via root namespace #}
{% extends 'base.html.twig' %}
```

### Custom templates overriding components

If you fully replaced a component (no `extends`), it just works — your file in `local/templates/` takes priority, old-template's version is ignored.

If you partially extended a component via `@base`, apply your changes directly to the corresponding file from `old-template/templates/` instead — that's cleaner than fighting namespace resolution.

### Custom translations

Your `local/translations/messages.*.yaml` files are merged on top of old-template's translations automatically — no changes needed.

## Files

```
templates/
├── base.html.twig                         # Old layout: site-branding, tagline, Projects nav
├── blog/                                  # Old blog list and post templates
├── components/                            # All original components
├── page/                                  # All original page templates (including projects.html.twig)
├── feed/, email/, search/, data_collector/
assets/
├── app.js                                 # Loads app_local.scss + lightbox.js
├── lightbox.js                            # Required by app.js (relative import)
├── styles/
│   ├── app_local.scss                     # SCSS entrypoint (compiled by sass-bundle)
│   └── _*.scss                            # All original style partials
├── fonts/                                 # Open Sans + Source Code Pro
└── images/                                # Original favicon, OG image
translations/
├── messages.en.yaml                       # Includes header.tagline
└── messages.pl.yaml
src/
└── .gitkeep                               # Required placeholder for local/src/
```
