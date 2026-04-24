# Custom Footer

The core `base.html.twig` already includes a minimal footer with a copyright notice and privacy policy link. This example shows how to add your own text or change its layout.

## What It Does

- Replaces the existing footer in `templates/base.html.twig` with a custom message.
- Adds a "Back to top" link and a third-party attribution line.

## Files

```
templates/
└── base.html.twig   # Full replacement with custom footer
```

## How to Apply

```bash
cp docs/customization/custom-footer/templates/base.html.twig local/templates/
```

Clear cache:

```bash
ddev exec bin/console cache:clear
```

## Technical Details

The footer is hardcoded in `base.html.twig` and not inside a `{% block %}`, so overriding it requires replacing the base template outright.

The replacement keeps:

- All `<head>` content and SEO tags
- hreflang alternate links
- Open Graph meta tags
- Structured data
- Navigation and main content blocks
- All JavaScript includes

## Result

A footer that displays a custom copyright string, a "Back to top" anchor, and a third-party credit line.
