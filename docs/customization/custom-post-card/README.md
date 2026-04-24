# Custom Post Card

Replaces the vertical post card with a horizontal layout (image on left, text on right).

## What It Does

- Replaces `templates/components/post_card.html.twig` with a horizontal row layout
- Places featured image on the left, content on the right
- Includes custom SCSS for flexbox layout
- Maintains all existing data fields (title, date, excerpt, tags, badges)

## Files

```
templates/
├── base.html.twig                         # Extends core base, loads app-local entrypoint
templates/components/
└── post_card.html.twig                    # Horizontal row layout
assets/
├── app.js                                 # Imports custom SCSS
└── styles/
    └── app_local.scss                     # .post-card--horizontal styles
```

## How to Apply

Copy the files to your `local/` directory:

```bash
cp -r docs/customization/custom-post-card/templates/* local/templates/
cp -r docs/customization/custom-post-card/assets/* local/assets/
```

Rebuild assets:

```bash
ddev exec bin/console asset-map:compile
```

## Technical Details

The horizontal layout:
- Uses CSS flexbox with `flex-direction: row`
- Image takes 40% width on desktop, full width on mobile
- Content area takes remaining space (`flex: 1`), gap provided by container
- Image uses `object-fit: cover` to maintain aspect ratio
- Responsive: stacks vertically on screens < 640px

The base template extends `@base/base.html.twig` and loads both the core `app` styles
and your custom `app-local` styles, so your card CSS is layered on top of the core layout.

## Result

Blog posts display in a horizontal card layout with image on the left and title/description on the right, instead of the default vertical stacked layout.
