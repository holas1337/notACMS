---
title: "Architecture"
description: "How notACMS works internally — the content pipeline, routing system, and build process."
template: page/doc
slug: architecture
menu:
  weight: 30
  label: "Architecture"
---

## Content Pipeline

Every build follows a four-step pipeline:

```
1. sass:build         → compile SCSS to CSS
2. asset-map:compile  → hash and copy assets
3. app:build          → render all HTML pages from content + templates
4. pagefind            → build the search index
```

Step 3 is driven by `BuildStaticSiteCommand` in `src/Command/`, which scans content, renders templates, generates responsive images, and writes static HTML to `public/static/`. Step 4 runs `npx pagefind` as a separate process.

URLs come from the `slug` frontmatter field, not directory paths. A post at `blog/releases/release-1-0-0/en.md` with `slug: "blog/release-1-0-0"` generates `/blog/release-1-0-0/`.

## Routing System

Routes are generated from the content tree. Each `ContentItem` has a URL defined by its `slug` frontmatter field.

The `LocalizedRouteLoader` reads `local/content/_routes.yaml` and registers Symfony routes for each locale. Default locale (EN) routes are prefix-free. Other locales get a `/{locale}/` prefix unless overridden.

**Example URL generation:**

| Content file | Slug frontmatter | Locale | Generated URL |
|---|---|---|---|
| `pages/about/en.md` | `about` | EN | `/about/` |
| `pages/about/pl.md` | `o-projekcie` | PL | `/pl/o-projekcie/` |
| `pages/about/de.md` | `ueber-uns` | DE | `/de/ueber-uns/` |
| `pages/about/fr.md` | `a-propos` | FR | `/fr/a-propos/` |
| `blog/releases/release-1-0-0/en.md` | `blog/release-1-0-0` | EN | `/blog/release-1-0-0/` |
| `blog/releases/release-1-0-0/pl.md` | `blog/release-1-0-0` | PL | `/wpisy/release-1-0-0/` |

## Core Services

### `ContentService`

The main facade for loading and querying content. Delegates tree building to `ContentTreeBuilder` and implements `ContentCacheInterface` so `invalidateCache($locale)` can drop cached trees when content changes.

```php
$posts = $contentService->getPosts($locale, 1, 5);
$recent = $contentService->getRecentPosts($locale, 5);
$item  = $contentService->findByUrl('/about/', $locale);
```

### `ContentItem`

Represents a single piece of content. Exposes methods for frontmatter fields:

```php
$item->title()          // string
$item->slug()           // string — last segment of the slug frontmatter (e.g. "my-post" from "blog/my-post")
$item->url()            // string (full URL path, e.g. /blog/my-post/)
$item->date()           // ?DateTimeImmutable
$item->updatedDate()    // ?DateTimeImmutable
$item->description()    // string
$item->tags()           // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()       // string (defaults to title)
$item->template()       // string
$item->isDraft()        // bool
$item->isScheduled()    // bool
$item->isPinned()       // bool
$item->isDynamic()      // bool
$item->isFeatured()     // bool
$item->hasToc()         // bool
$item->series()         // ?string
$item->seriesOrder()    // ?int
$item->directoryKey()   // ?string
$item->relatedSlugs()   // string[]
$item->menuWeight()     // int
$item->menuLabel()      // string (menu.label frontmatter, falls back to title())
$item->isIndex()       // bool
$item->readingTime()    // int
$item->wordCount()      // int
$item->excerpt()        // string
$item->htmlContent      // rendered HTML string (public property)
```

### `SiteConfigService`

Reads `local/content/_site.yaml` and exposes site-wide settings as a service. Injected into controllers and Twig globals via `SiteConfigExtension`.

### `ContentTree`

Internal representation of the content hierarchy for a single locale. Used for navigation, prev/next calculation, and series detection. `ContentService` wraps it as the facade, so the same lookup names (`findByUrl`, `findPostBySlug`, `findScheduledPostBySlug`) exist on both: the service version takes a `$locale` and delegates to the per-locale tree.

Key methods:
- `getAdjacentPosts()` — returns `AdjacentPosts` value object with `->prev` / `->next` (`?ContentItem`)
- `getSeriesPosts()` — posts in the same series, sorted by `series_order`
- `getAllPosts()`, `getAllPages()`, `getStaticPages()`, `getAllItems()`
- `getPostsByCategory()`, `getPostsByTag()`, `getPostsByYearMonth()`, `getPostsByYear()`
- `getAllTags()`, `getAllCategories()`, `getArchiveMonths()`, `getArchiveYears()`
- `findByDirectoryKey()`, `findByUrl()`, `findPostBySlug()`, `findScheduledPostBySlug()`

### Other Services

| Service | Purpose |
|---------|---------|
| `ImageResizer` | ImageMagick wrapper: `resize()` generates responsive variants; `optimize()` recompresses in-place |
| `ResponsiveImageService` | Computes responsive variant widths and builds `srcset` attribute values |
| `SrcsetExtension` | Twig filter `srcset_media` — post-processes HTML to inject `srcset`/`sizes` attributes into `<img>` tags |
| `MediaFileResolver` | Resolves media file paths across locales |
| `RelatedPostsService` | Finds related posts by shared tags and manual `related` frontmatter |
| `SessionToggleService` | Generic session-based boolean toggle; shared dependency of both preview services below |
| `DraftPreviewService` | Handles draft content preview with session-based toggles |
| `ScheduledPreviewService` | Manages scheduled content preview for future-dated posts |
| `TranslationMapBuilder` | Builds cross-locale URL translation maps for the language switcher |
| `ContactFormConfig` | Provides contact form configuration (recipient, Turnstile, etc.) |
| `MarkdownParser` | Converts Markdown to HTML via CommonMark |
| `SidebarDataProvider` | Assembles sidebar data (categories, tags, recent posts) |
| `TagTranslationService` | Translates tag slugs between locales |
| `StructuredDataBuilder` | Builds typed PHP arrays for JSON-LD structured data (WebSite, Person, BlogPosting, CollectionPage, BreadcrumbList, ContactPage, WebPage, Organization, ImageObject); automatic empty-value stripping |

### Twig Extensions

Eight Twig extensions expose data to templates:

| Extension | Type | Provides |
|---|---|---|
| `SiteConfigExtension` | Globals | `site_name`, `site_base_url`, `site_description`, `site_social`, `site_author`, `site_locales`, `site_locales_list`, `site_default_locale`, `image_variant_widths`, `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` |
| `TranslationMapTwigExtension` | Global | `translation_map` — `{directoryKey: {locale: url}}` mapping for language switcher and hreflang tags |
| `ContentTwigExtension` | Function | `content_url(directoryKey, locale)` — resolves URL for a content item by directory key; `content_item(directoryKey, locale)` — returns the full `ContentItem` (or `null` if not found) |
| `LangSwitcherExtension` | Function | `lang_switch_urls(otherLocales)` — resolves language switcher URLs per locale using translation map, controller overrides, and route-based fallbacks (archive → paginated → blog list → home) |
| `SrcsetExtension` | Filter | `srcset_media` — post-processes HTML to inject `srcset`/`sizes` attributes into `<img>` tags for responsive images |
| `StructuredDataExtension` | Function | `json_ld(array)` — encodes a PHP array as JSON-LD and returns a full `<script>` tag; `structured_data()` — returns the builder for method chaining like `structured_data().blogPosting(...)` |
| `SidebarExtension` | Function | `sidebar_data(locale)` — lazily builds sidebar data (recent posts, categories, tags, archive months); called in `base.html.twig` where sidebar is rendered |
| `BreadcrumbExtension` | Function | `breadcrumbs(contentItem, locale, options)` — returns breadcrumb array for any page type |

Additionally, `cf_analytics_token` is registered as a Twig global in `config/packages/twig.yaml`, bound to the `CF_ANALYTICS_TOKEN` environment variable.

## Request Flow (Live Mode)

```
Browser → nginx → PHP-FPM → Symfony Kernel → LocaleListener → Router
          → Controller → ContentService → Twig → HTML response
```

Controllers are thin. All business logic lives in services.

## Test Strategy

```bash
ddev test           # Run the full test suite
ddev code-check     # PHPStan level 6 + PHP CS Fixer
```

- **Hundreds of test methods** across integration and unit suites.
- **PHPStan:** Static analysis at level 6. All services are fully typed.
- **PHP CS Fixer:** Enforces PSR-12 + Symfony coding standards.
- **Integration tests:** Cover routing, content loading, controllers, build output, image processing, preview services, and Twig extensions.
- **Unit tests:** Cover `ContentItem`, `ContentTree`, value objects, `RelatedPostsService`, `TagTranslationService`, and `ResponsiveImageService`.

See `docs/TESTING.md` for details.

## Adding Features

notACMS follows the Symfony service container pattern. To add a new content type or build step:

1. **For core contributions:** Create an interface in `src/Service/`, then the implementation. Symfony auto-discovers and autowires it — no manual `services.yaml` registration needed.
2. **For site-specific customizations:** Place PHP classes in `local/src/` using the `NotACms\Local\` namespace. These are also auto-discovered. See [Customization](/customization/) for details.
3. Inject the service into the relevant controller or command.
4. Override templates in `local/templates/` if needed.

> **Tip:** All core services use interfaces. This makes it easy to swap implementations — for example, replacing the local filesystem loader with an S3 source.

## PHP Runtime (Optional)

By default, the built site is purely static — Nginx serves `public/static/` directly and PHP is not involved.

The production Docker setup in `local/docker/` includes an optional PHP container. Set `RUNTIME_PHP_ENABLED=true` in your production environment to start it. When enabled, Nginx routes `/api/` (and `/{locale}/api/`) to the PHP container — everything else continues to be served as static files.

The default use case is the **contact form** (`ContactController` via SMTP), which lives at `/api/contact`.

> **Note:** Local development (`ddev start`) runs the full Symfony application dynamically. This is a dev-only convenience — it is not part of the production PHP runtime.

---

## Deployment Architecture

### Static-First Serving

Nginx serves pre-built files from `public/static/` directly. PHP is only involved for:
- `/api/contact` — if `RUNTIME_PHP_ENABLED=true`
- Development mode (`ddev start`)

Production traffic flow:
```
Browser → Nginx → public/static/ (HTML, CSS, JS)
                → Proxy to PHP container (only for /api/*)
```

### Build vs Runtime

| Phase | Command | Output | Duration |
|-------|---------|--------|----------|
| 1. Compile | `sass:build` | `public/assets/app-<hash>.css` | ~2s |
| 2. Assets | `asset-map:compile` | Hashed asset copies in `public/assets/` | ~1s |
| 3. Build | `app:build` (BuildStaticSiteCommand) | `public/static/**/*.html` | ~5s |
| 4. Index | `pagefind` | `public/pagefind/` search index | ~3s |
| 5. Serve | nginx | HTTP responses | ongoing |

Total first deploy: ~2-3 minutes (Docker build)
Content update: ~10 seconds

### Container Profiles

| Profile | Containers | Use case |
|---------|------------|----------|
| (none) | nginx only | Static sites, no contact form |
| `--profile runtime` | nginx + php | Contact form enabled |

Switch profiles by setting `RUNTIME_PHP_ENABLED` in `.env.local`.
