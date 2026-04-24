# Twig Filter

Adds a `|excerpt` filter for generating plain text snippets from HTML.

## What It Does

- Adds a `|excerpt(length)` Twig filter
- Strips HTML tags, normalises whitespace, and truncates to `length` characters
- Lives in `local/src/` under the `NotACms\Local\` namespace

## Files

```
local/src/Twig/
└── ExcerptExtension.php
```

## How to Apply

```bash
cp -r docs/customization/twig-filter/src/ local/src/
```

Clear cache:

```bash
ddev exec bin/console cache:clear
```

## Usage in Templates

```twig
{{ post.body()|excerpt(200) }}
```

## Technical Details

The `#[AsTwigFilter]` attribute wires the method into Twig automatically. No
additional service registration is required.

## Result

Any HTML field can be piped through `|excerpt(200)` to produce a clean,
plain-text summary string.
