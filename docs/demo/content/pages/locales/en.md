---
title: "Locales"
description: "How to add languages, translate content, and configure URL patterns for each locale."
template: page/doc
slug: locales
menu:
  weight: 50
  label: "Locales"
---

## Adding a New Locale

Open `local/content/_site.yaml` and add an entry to the `locales` map:

```yaml
site:
  locales:
    en:
      label: "English"
      og_locale: en_US
      date_format: "M d, Y"
      tagline: "static. simple. yours."
      # tagline_commands: ["git push", "ddev build"]  # optional: shown in hero below tagline
      # font_preload: true                                # optional: preload Inter + JetBrains Mono
    de:
      label: "Deutsch"
      og_locale: de_DE
      date_format: "d.m.Y"
      tagline: "statisch. einfach. deins."
    pl:                                 # this demo site also ships Polish
      label: "Polski"
      og_locale: pl_PL
      date_format: "d.m.Y"
      tagline: "statyczny. prosty. twój."
    fr:                                 # and French
      label: "Français"
      og_locale: fr_FR
      date_format: "d/m/Y"
      tagline: "statique. simple. vôtre."
```

That's it. notACMS will:
- Add `de` to the language switcher
- Generate `hreflang` tags for all pages with a DE translation
- Prefix DE URLs with `/de/` (or use your custom path)

The same applies for `fr`, `pl`, or any other locale you add.

## Content Co-location

Translations live alongside the source content. Each locale is a separate Markdown file in the same directory:

```
pages/about/
├── en.md    ← English
├── de.md    ← German
├── pl.md    ← Polish
└── fr.md    ← French
```

The `slug` field in frontmatter defines the URL path for each locale. If empty (or omitted for index pages), the homepage URL (`/` for the default locale, `/{locale}/` for others) is used. URLs always come from the `slug` frontmatter field — not from directory paths.

## URL Overrides

By default, non-default locales prefix the EN path: `/de/about/`, `/pl/about/`, `/fr/about/`. To use a fully custom path, add an entry to `_routes.yaml`:

```yaml
# local/content/_routes.yaml
routes:
  blog_list:
    de: /beitraege/
    pl: /wpisy/
    fr: /articles/
  blog_list_paginated:
    de: /beitraege/seite/{page}/
    pl: /wpisy/strona/{page}/
    fr: /articles/page/{page}/
```

Page-level URL overrides are set in frontmatter:

```yaml
---
title: "Über uns"
slug: ueber-uns
---
```

This produces `/de/ueber-uns/` instead of `/de/about/`.

## Tag Translations

Tags can be translated in `_tags.yaml`:

```yaml
# local/content/_tags.yaml
release:
  de: veröffentlichung
  pl: wydanie
  fr: version
```

The canonical tag (EN key) is used internally. The translated value appears in the UI for each locale. Tags that are identical across all locales don't need an entry — only list tags that differ.

## Language Switcher Behavior

The language switcher in the nav automatically resolves the correct URL for each other locale using the `lang_switch_urls()` Twig function. The fallback chain, in order:

1. **Translation map** — looks for a translated version of the current page using the `translation_map` global
2. **Controller override** — uses an explicit `lang_switch_url` when set (e.g. tag pages where the tag may not exist in the target locale)
3. **Archive** — if on an archive page, links to the same archive in the target locale
4. **Paginated blog list** — if on page 2+, links to the same page in the target locale
5. **Blog list** — if on any blog list page, links to the blog list in the target locale
6. **Homepage** — final fallback when no blog or translation context is available

The switcher only appears when there are 2+ configured locales with at least one other locale defined.

## Fallback Strategy

If a page exists in EN but not in DE, notACMS will:
- Not generate a DE URL for that page
- Not include it in the DE sitemap
- Show a link to the DE homepage in the language switcher (not to the EN version of the page)

The same applies for FR, PL, or any other locale. This means you can translate incrementally — EN is always complete, other locales grow over time.

## hreflang and SEO

notACMS automatically generates `<link rel="alternate" hreflang="...">` tags in the `<head>` for all pages that have translations. The `og_locale` field in the site config controls the Open Graph locale format (`en_US`, `de_DE`, etc.).

```html
<link rel="alternate" hreflang="en" href="https://example.com/about/">
<link rel="alternate" hreflang="de" href="https://example.com/de/about/">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/about/">
<link rel="alternate" hreflang="x-default" href="https://example.com/about/">
```

> **Tip:** Always provide an `x-default` translation pointing to your primary language. notACMS handles this automatically for the default locale.
