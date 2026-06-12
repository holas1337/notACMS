# Theme Building Guide

This document is the **theme API reference**: everything notACMS guarantees to your templates — context variables, Twig globals, functions and filters, the `ContentItem` API, required translation keys, and the assets contract. For *how to override* templates, SCSS, and JS, see [CUSTOMIZATION.md](CUSTOMIZATION.md).

Anything listed here is a supported contract: it is maintained across releases, and breaking changes to it are documented in `UPGRADE-X.Y.md`. Anything *not* listed here is internal and may change without notice.

> **Maintenance note:** when a controller's `render()` context, a Twig extension in `src/Twig/`, or `twig.globals` changes, update this file in the same change.

---

## Template ↔ context contracts

Every template receives the [Twig globals](#twig-globals) plus the context below. `locale` is always present.

### Page templates

| Route(s) | Controller | Template | Context |
|---|---|---|---|
| `home_{locale}` `/` | `HomeController` | `page/home.html.twig` | `content` (`?ContentItem` — the home page, null if missing), `recentPosts` (`ContentItem[]`), `locale`, `card_layouts` |
| `static_page_{locale}` `/{slug}/` | `PageController` | frontmatter `template:` (default `page/default.html.twig`) | `content` (`ContentItem`), `locale` |
| `contact_{locale}` `/contact/` | `ContactController` | `page/contact.html.twig` | `content` (`?ContentItem`), `form` (Symfony FormView), `locale`, `contact_form_available` (`bool` — false when `contact_form` is not configured in `_site.yaml`) |
| `projects_{locale}` `/projects/` | `ProjectsController` | `page/projects.html.twig` | `content` (`ContentItem`), `featured_projects` (`ContentItem[]`), `total_projects` (`int`), `locale` |
| `search_{locale}` `/search/` | `SearchController` | `search/index.html.twig` | `locale` |
| error pages (`error_404`/`error_500` + prod error controller) | `ErrorController` | `page/error.html.twig` | `status_code` (`int`), `status_text` (`string`), `locale`, `content` (`null`) |

### Blog templates

| Route(s) | Controller | Template | Context |
|---|---|---|---|
| `blog_list_{locale}` `/blog/`, `blog_list_paginated_{locale}` `/blog/page/{page}/` | `BlogController::list` | `blog/list.html.twig` | `posts` (`ContentItem[]`), `current_page`, `total_pages`, `total_posts` (`int`), `filter_type` (`null`), `filter_value` (`null`), `index_content` (`?ContentItem` — the blog `_index` page), `locale`, `card_layouts` |
| `blog_category_{locale}` `/blog/{category}/` (category listing) | `BlogController` | `blog/list.html.twig` | as list, with `filter_type: 'category'`, `filter_value` (category slug), `index_content` (`?ContentItem` — the category `_index` page), `current_page: 1`, `total_pages: 1` |
| same route resolving to a post | `BlogController` | `blog/post.html.twig` | `content` (`ContentItem`), `related_posts` (`ContentItem[]`), `prev_post`/`next_post` (`?ContentItem`), `series_posts` (`ContentItem[]`), `series_current_part` (`int`, 1-based), `locale`, `card_layouts` |
| same route resolving to a scheduled post | `BlogController` | `page/coming-soon.html.twig` | `post` (`ContentItem`), `locale`, `card_layouts` |
| `blog_tag_{locale}` `/tag/{tag}/` | `BlogController` | `blog/list.html.twig` | as category, with `filter_type: 'tag'`, `index_content: null` |
| `blog_archive_{locale}` `/archive/{year}/{month}/`, `blog_archive_year_{locale}` `/archive/{year}/` | `BlogController` | `blog/list.html.twig` | as list, with `filter_type: 'archive'`, `archive_year` (`int`), `archive_month` (`int`, month route only), `index_content: null` |

`filter_type` values are the `NotACms\Content\Enum\FilterType` enum values: `'category'`, `'tag'`, `'archive'`.
Blog list contexts also carry `lang_switch` (`LangSwitchContext` VO) — consumed by `lang_switch_urls()`; templates normally don't read it directly.
`card_layouts` is `CardLayout::cycle()` — `['layout-top', 'layout-right', 'layout-text', 'layout-left']` for cycling card styles (e.g. `card_layouts[loop.index0 % 4]`).

### Feed templates

| Route | Template | Context |
|---|---|---|
| `rss_{locale}` `/feed/` | `feed/rss.xml.twig` | `posts` (`ContentItem[]`), `locale` |
| `sitemap` `/sitemap.xml` | `feed/sitemap.xml.twig` | `items_by_locale` (`array<locale, ContentItem[]>` — posts + pages) |
| `llms_txt` `/llms.txt` | `feed/llms.txt.twig` | `posts_by_locale` (`array<locale, ContentItem[]>`), `pages` (`ContentItem[]` — default-locale publishable static pages) |
| `robots` `/robots.txt` | `feed/robots.txt.twig` | globals only |

### Email templates

| Trigger | Template | Context |
|---|---|---|
| Contact form submission | `email/contact.html.twig` | `contact` (`array` with `name`, `email`, `website`, `subject`, `message`), `locale` (submission locale) |

---

## Twig globals

Available in every template.

| Global | Type | Source |
|---|---|---|
| `site_name`, `site_base_url`, `site_description` | `string` | `_site.yaml` via `SiteConfigExtension` |
| `site_social` | `array` | `_site.yaml` `social:` (shape is site-defined) |
| `site_author` | `array` | `_site.yaml` `author:` (shape is site-defined — always guard with `\|default()`) |
| `site_locales` | `array<locale, array>` | `_site.yaml` `locales:` map (per-locale `label`, `og_locale`, `date_format`, …) |
| `site_locales_list` | `string[]` | locale codes, first = default |
| `site_default_locale` | `string` | first key of `locales:` |
| `image_variant_widths` | `int[]` | `_site.yaml` `image_variant_widths` (default `[640, 960]`) |
| `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` | `int` | `_site.yaml` numeric settings |
| `translation_map` | `array<directoryKey, array<locale, url>>` | `TranslationMapTwigExtension` — published (non-draft, non-scheduled) translations only; keys are **full relative content paths** (e.g. `pages/about`); index it with `content.directoryKey()` |
| `cf_analytics_token` | `string` | `twig.yaml` ← `CF_ANALYTICS_TOKEN` env |
| `turnstile_site_key` | `string` | `twig.yaml` ← `TURNSTILE_SITE_KEY` env |
| `notacms_project_url` | `string` | `twig.yaml` (notACMS GitHub URL) |

---

## Twig functions & filters

| Function / filter | Signature | Returns |
|---|---|---|
| `content_item(key, locale)` | `(string, string): ?ContentItem` | Item by directory key — accepts a **bare basename** (`'about'`) when unambiguous, or a full path (`'pages/about'`). **Nullable** — always guard: `{% set item = content_item('about', locale) %}{% if item %}…{% endif %}` |
| `content_url(key, locale)` | `(string, string): string` | Item URL by directory key; `'/'` when not found — don't use the result as an "is active" prefix without checking it isn't `'/'` |
| `breadcrumbs(content, locale, options)` | `(?ContentItem, string, array): Breadcrumb[]` | Crumb objects with `.label` (`string`) and `.url` (`?string`, null = current). Options: `home_label`, `filter_type`, `filter_value`, `archive_date` |
| `blog_filter_title(filter_type, filter_value, archive_year, archive_month, locale)` | `(?string, ?string, ?int, ?int, string): string` | Listing title for the active filter, falling back to the blog index `menuLabel()` or the `blog.title` translation |
| `lang_switch_urls(other_locales)` | needs context; `(string[]): array<locale, url>` | Language-switcher URL per other locale, resolved from `translation_map` / controller overrides / route fallbacks |
| `og_image_url(content, site_base_url)` | `(?ContentItem, string): string` | Featured image URL or the default OG image |
| `post_badge(post, new_post_days)` | `(ContentItem, int): ?string` | `'new'`, `'updated'`, or null |
| `sidebar_data(locale)` | `(string): SidebarData` | `.recentPosts` (`ContentItem[]`), `.categories` (`CategoryCount[]`), `.tags` (`TagCount[]`), `.archiveMonths` (`ArchiveMonth[]`) |
| `structured_data()` | `(): StructuredDataBuilderInterface` | Schema.org builder — `webSite()`, `webPage()`, `collectionPage()`, `contactPage()`, `breadcrumbList()`, `person()`, `organization()`, `imageObject()`; `blogPosting({...})` takes a named hash matching `BlogPostingData` (headline, url, inLanguage, datePublished, dateModified, description, wordCount, articleSection, keywords, image, author, publisher, mainEntityOfPage) |
| `json_ld(data)` | `(array): string` | `<script type="application/ld+json">` block (HEX-escaped, safe against `</script>` in content) |
| `srcset_media` (filter) | `(string html): string` | Injects `srcset`/`sizes` into `<img src="/media/….webp">` tags in rendered content HTML — the standard body pipeline is `content.htmlContent\|srcset_media\|raw` |

---

## ContentItem API

Methods your templates may call on any `ContentItem`:

`title()`, `description()`, `slug()` (last segment of the frontmatter slug), `url()` (full path incl. locale prefix), `date()`/`updatedDate()` (`?DateTimeImmutable` — **guard before `\|date()`**), `tags()` (`string[]`, URL-safe normalized), `category()` (`?string`, URL-safe normalized), `image()` (`?string`), `imageAlt()`, `template()`, `excerpt(length = 250)`, `readingTime()`, `wordCount()`, `menuLabel()`, `menuWeight()`, `series()` (`?string`), `seriesOrder()` (`?int`), `relatedSlugs()`, `isPost()`, `isSame(other)`, `directoryKey()` (`?string` — **full relative content path**, e.g. `blog/my-post`; use it to index `translation_map`), `isDraft()`, `isScheduled()`, `isPinned()`, `isFeatured()`, `isDynamic()`, `isIndex()`, `hasToc()`, and the public properties `htmlContent`, `locale`, `sourcePath`.

Value objects: `Breadcrumb` (`.label`, `.url`), `SidebarData` (above), `TagCount`/`CategoryCount` (`.slug`, `.count`), `ArchiveMonth` (`.year`, `.month`, `.count`).

---

## Required translation keys

Core PHP renders these keys regardless of which templates your theme ships — **every theme catalog must define them in every locale**:

| Key | Used by |
|---|---|
| `blog.title` | `blog_filter_title()` fallback when no blog index page exists |
| `contact.form.error`, `contact.form.success` | contact API JSON responses |
| `contact.form.name`, `.email`, `.website`, `.subject`, `.message` | form field labels (`ContactType`) |
| `contact.form.unavailable` | shown when `contact_form` is not configured |

Everything else is referenced by templates, so a theme that overrides a template owns its keys. Keys used by core templates your theme does **not** override must also exist in your catalogs (the translator falls back to core `translations/` only for keys you don't define).

---

## Request attributes

| Attribute | Meaning |
|---|---|
| `_static_build` | `true` on every sub-request rendered by `app:build`. Branch on it via `app.request.attributes.get('_static_build')` to vary output between live serving and the static build. |

---

## Assets contract

- Importmap entrypoint: `importmap('app')` (core) — themes typically define their own entry (e.g. `app-local`) in `importmap.php`.
- Theme assets under `local/assets/` are exposed as `asset('local/<file>')`; core assets as `asset('<file>')`.
- Images: content images live in each content directory's `files/` subfolder, served at `/media/{directoryKey-basename}/{filename}`. The responsive pipeline is **WebP-only**: variant URLs are `<base>-<width>w.webp` for each `image_variant_widths` entry; non-`.webp` sources get no srcset.

---

## Theme portability rules

1. **Iterate `site_locales_list`** — never hardcode locale codes or assume exactly two locales.
2. **Guard nullable lookups** — `content_item()` returns null; `site_author.*` keys are site-defined (`site_author.name|default(site_name)`); `content.date()` can be null.
3. **Don't compare `directoryKey()` to literals** unless you control the content layout — it returns the full relative path.
4. **Every `|trans` key in your templates must exist in every locale catalog you ship** — missing keys render as raw key strings.
5. **Don't pattern-match URLs for section detection** when content metadata can answer the question.
6. **No `|raw` on translation-catalog output** — keep markup in templates where possible; when a theme genuinely needs inline markup in copy (marketing headings), render it through `|sanitize_html('app.trans_inline')` (a strict `strong`/`em`/`code`/`br` allowlist configured in `config/packages/html_sanitizer.yaml`).
