---
title: "Theme Building"
description: "Building a full theme for notACMS — template layers, Twig API, context contracts, and portability rules."
template: page/doc
slug: theme-building
menu:
  weight: 45
  label: "Theme Building"
---

## Template Layer Hierarchy

notACMS resolves templates from three layers, applied in priority order:

1. **`local/templates/`** — site-specific overrides (highest priority)
2. **Theme templates** — registered via `twig.paths` in `local/config/packages/twig.yaml`
3. **Core bare templates** in `templates/`

Any file in `local/templates/` overrides the equivalent in the registered theme or core. The bare theme ships a minimal, complete set of templates that renders all content types without external dependencies. The demo theme (this site) adds styling and components on top.

## Template ↔ Context Contracts

Every template receives the [Twig globals](#twig-globals) plus page-specific context. `locale` is always present.

### Page Templates

| Route | Template | Key context |
|---|---|---|
| `home_{locale}` | `page/home.html.twig` | `content` (`?ContentItem`), `recentPosts` (`ContentItem[]`), `card_layouts` |
| `static_page_{locale}` | frontmatter `template:` | `content` (`ContentItem`) |
| `contact_{locale}` | `page/contact.html.twig` | `content`, `form`, `contact_form_available` (`bool`) |
| `projects_{locale}` | `page/projects.html.twig` | `content`, `featured_projects`, `total_projects` |
| `search_{locale}` | `search/index.html.twig` | `locale` only |
| error pages | `page/error.html.twig` | `status_code`, `status_text`, `locale`, `content` (null) |

### Blog Templates

| Route | Template | Key context |
|---|---|---|
| blog list + paginated | `blog/list.html.twig` | `posts`, `current_page`, `total_pages`, `total_posts`, `filter_type`, `filter_value`, `index_content`, `card_layouts` |
| single post | `blog/post.html.twig` | `content`, `related_posts`, `prev_post`, `next_post`, `series_posts`, `series_current_part`, `card_layouts` |
| scheduled post | `page/coming-soon.html.twig` | `post`, `card_layouts` |

`filter_type` values: `'category'`, `'tag'`, `'archive'`. `card_layouts` cycles through `['layout-top', 'layout-right', 'layout-text', 'layout-left']`.

### Feed & Email Templates

| Template | Context |
|---|---|
| `feed/rss.xml.twig` | `posts` (`ContentItem[]`), `locale` |
| `feed/sitemap.xml.twig` | `items_by_locale` (`array<locale, ContentItem[]>`) |
| `feed/llms.txt.twig` | `posts_by_locale`, `pages` |
| `feed/robots.txt.twig` | globals only |
| `email/contact.html.twig` | `contact` (array: `name`, `email`, `website`, `subject`, `message`), `locale` |

## Twig Globals

Available in every template:

| Global | Type | Source |
|---|---|---|
| `site_name`, `site_base_url`, `site_description` | `string` | `_site.yaml` |
| `site_social` | `array` | `_site.yaml` `social:` (shape is site-defined) |
| `site_author` | `array` | `_site.yaml` `author:` — always guard with `\|default()` |
| `site_locales` | `array<locale, array>` | per-locale `label`, `og_locale`, `date_format`, … |
| `site_locales_list` | `string[]` | locale codes, first = default |
| `site_default_locale` | `string` | first key of `locales:` |
| `image_variant_widths` | `int[]` | `_site.yaml` (default `[640, 960]`) |
| `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` | `int` | `_site.yaml` |
| `translation_map` | `array<directoryKey, array<locale, url>>` | published translations only; keys are full relative content paths |
| `cf_analytics_token`, `turnstile_site_key`, `notacms_project_url` | `string` | `twig.yaml` env vars |

## Twig Functions & Filters

| Function / filter | Signature | Returns |
|---|---|---|
| `content_item(key, locale)` | `(string, string): ?ContentItem` | Item by directory key — nullable, always guard |
| `content_url(key, locale)` | `(string, string): string` | Item URL; `'/'` when not found |
| `breadcrumbs(content, locale, options)` | `(?ContentItem, string, array): Breadcrumb[]` | `.label` and `.url` (null = current page) |
| `lang_switch_urls(other_locales)` | `(string[]): array<locale, url>` | Language-switcher URLs |
| `og_image_url(content, site_base_url)` | `(?ContentItem, string): string` | Featured image URL or default OG image |
| `post_badge(post, new_post_days)` | `(ContentItem, int): ?string` | `'new'`, `'updated'`, or null |
| `sidebar_data(locale)` | `(string): SidebarData` | `.recentPosts`, `.categories`, `.tags`, `.archiveMonths` |
| `structured_data()` | `(): StructuredDataBuilderInterface` | Schema.org builder — `webSite()`, `blogPosting()`, `breadcrumbList()`, etc. |
| `json_ld(data)` | `(array): string` | `<script type="application/ld+json">` block |
| `srcset_media` (filter) | `(string): string` | Injects `srcset`/`sizes` into `<img>` tags — standard body pipeline: `content.htmlContent\|srcset_media\|raw` |

## ContentItem API

Methods available on any `ContentItem` in templates:

```php
$item->title()         // string
$item->description()   // string
$item->slug()          // string — last segment of the slug frontmatter
$item->url()           // string — full path incl. locale prefix
$item->date()          // ?DateTimeImmutable — guard before |date()
$item->updatedDate()   // ?DateTimeImmutable
$item->tags()          // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()      // string (defaults to title)
$item->excerpt()       // string
$item->readingTime()   // int
$item->wordCount()     // int
$item->menuLabel()     // string
$item->menuWeight()    // int
$item->series()        // ?string
$item->seriesOrder()   // ?int
$item->directoryKey()  // ?string — full relative content path
$item->isDraft()       // bool
$item->isScheduled()   // bool
$item->isPinned()      // bool
$item->isFeatured()    // bool
$item->hasToc()        // bool
$item->htmlContent     // string (public property) — rendered HTML
$item->locale          // string (public property)
```

## Required Translation Keys

Every theme catalog must define these keys in every locale:

| Key | Used by |
|---|---|
| `blog.title` | `blog_filter_title()` fallback when no blog index page exists |
| `contact.form.error`, `contact.form.success` | contact API JSON responses |
| `contact.form.name`, `.email`, `.website`, `.subject`, `.message` | form field labels |
| `contact.form.unavailable` | shown when contact form is not configured |

Keys used by templates your theme does not override must also exist in your catalogs — the translator falls back to core `translations/` only for keys you don't define yourself.

## Theme Portability Rules

1. **Iterate `site_locales_list`** — never hardcode locale codes or assume exactly two locales.
2. **Guard nullable lookups** — `content_item()` returns null; `site_author.*` keys are site-defined (`site_author.name|default(site_name)`); `content.date()` can be null.
3. **Don't compare `directoryKey()` to literals** unless you control the content layout — it returns the full relative path.
4. **Every `|trans` key in your templates must exist in every locale catalog you ship** — missing keys render as raw key strings.
5. **Don't pattern-match URLs for section detection** when content metadata can answer the question.
6. **No `|raw` on translation-catalog output** — use `|sanitize_html('app.trans_inline')` when a theme genuinely needs inline markup in translated copy.

## Assets

Theme assets under `local/assets/` are referenced as `asset('local/<file>')`. Core assets use `asset('<file>')`. Content images are served at `/media/{basename}/{filename}`. The responsive image pipeline is WebP-only: variant URLs are `<base>-<width>w.webp` for each `image_variant_widths` entry.

The complete API contract — including all context fields, value object shapes, and edge-case notes — is maintained in `docs/THEME_BUILDING.md`. Breaking changes are documented in `UPGRADE-X.Y.md`.
