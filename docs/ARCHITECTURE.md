# Architecture

> **Maintenance note:** This document describes patterns and mechanisms, not exhaustive inventories. Template trees show the routing/rendering structure (page types, directory layout), not every component file. JS sections describe loading strategies, not every script. When adding a new component, template, or script, you do not need to add it here — add it to `local/docs/STYLEGUIDE.md` (component list) or `docs/STYLEGUIDE.md` (styleguide mechanics) instead.

notACMS is a Symfony 7.4 application that reads Markdown + YAML content files and generates a static HTML website. There is no database and no Node.js dependency.

---

## Build Pipeline

Four-step static build:

```
1. php bin/console sass:build          → public/assets/app-<hash>.css
2. php bin/console asset-map:compile   → public/assets/ (hashed copies)
3. php bin/console app:build           → public/static/**/*.html
4. npx pagefind --site public/static   → public/pagefind/ (WASM search index)
```

Local development: `ddev start` + `php bin/console sass:build --watch`

---

## Request Flow (live mode)

```
HTTP Request
  → nginx (DDEV)
  → PHP-FPM
  → Symfony Kernel
  → LocaleListener (priority 8) — detects /pl/ prefix → sets locale to 'pl'; default is 'en'
  → Router
  → Controller
  → ContentService::findByUrl($url, $locale)
  → Twig render
  → HTML Response
```

---

## Content Layer

**Storage:** Unified `local/content/` directory — co-located Markdown files (`pl.md` + `en.md`) with YAML frontmatter. Blog posts in `local/content/blog/{category}/{post-dir}/`, pages in `local/content/pages/{page-dir}/`. Images alongside content in `files/` subdirs, served at `/media/{post-dir}/`.

**Key frontmatter fields:**

| Field | Type | Purpose |
|---|---|---|
| `title` | string | Page/post title |
| `description` | string | Meta description and excerpt fallback |
| `date` | date | Publication date |
| `updated` | date | Last modified date |
| `tags` | array | Tag list |
| `slug` | string | URL path from frontmatter (e.g. `blog/my-post`). The last segment is used as the directory key. `url()` returns the full resolved URL path including locale prefix |
| `category` | string | Localized category slug (e.g. `tips-and-tricks` or `ciekawostki`) |
| `image` | string | Featured image path (e.g. `/media/my-post/photo.jpg`) |
| `image_alt` | string | Alt text for featured image |
| `draft` | bool | Exclude from build if true |
| `pinned` | string (date) | Posts only. Sort to top of all listings until this date (e.g. `2026-06-01`); featured card style on homepage. `isPinned()` returns true while `pinned > today` |
| `featured` | bool | Projects only. If `true`, the post appears in the curated grid on `/projects/` |
| `dynamic` | bool | Skip static pre-rendering; always served by Symfony |
| `template` | string | Custom Twig template (default: `page/default`) |
| `menu` | object | Navigation entry (`weight`, `label`) |
| `series` | string | Series key (e.g. `lets-encrypt`). Posts sharing a key are shown as a numbered series |
| `series_order` | int | Position within the series (1-based) |
| `toc` | bool | Show auto-generated table of contents for the post (injected by `table-of-contents.js`) |
| `related` | array | Manual related post slugs; merged with algorithm results in `RelatedPostsService` |

**Classes:**

| Class | File | Purpose |
|---|---|---|
| `ContentItem` | `src/Content/ContentItem.php` | Value object: parsed frontmatter + rendered HTML body; key methods: `readingTime()` (word-count estimate), `wordCount()` (raw word count for schema.org), `relatedSlugs()` (manual `related:` frontmatter), `isScheduled()`, `isPinned()`, `isDraft()`, `isFeatured()`, `isDynamic()`, `hasToc()`, `series()`, `seriesOrder()`, `directoryKey()` (co-location translation key), `menuWeight()`, `isIndex()`, `url()` (full URL path including locale prefix), `category()`, `excerpt()`. Plus one-to-one getters for every supported frontmatter field (`title()`, `slug()`, `date()`, `updatedDate()`, `description()`, `tags()`, `image()`, `imageAlt()`, `template()`) |
| `ContentTree` | `src/Content/ContentTree.php` | Immutable typed collection of ContentItem for one locale; constructed with `$includeDrafts` and `$includeScheduled` flags; sorts posts by pinned first, then date DESC; key methods: `getAdjacentPosts()` (returns `AdjacentPosts` VO with `->prev` / `->next`), `getSeriesPosts()` (posts in the same series, sorted by series_order) |
| `ContentTreeBuilder` | `src/Service/Content/ContentTreeBuilder.php` | Implements `ContentTreeBuilderInterface`. Scans `local/content/` for `{locale}.md` and `_index_{locale}.md` files → ContentTree (uses `league/commonmark`; injected as `MarkdownParserInterface`). Uses `ContentTreeBuilderInterface::BLOG_CONTENT_PREFIX = 'blog/'` to distinguish posts from pages — any content path starting with `blog/` is treated as a blog post |
| `ParsedMarkdown` | `src/Content/ValueObject/ParsedMarkdown.php` | Value object returned by `MarkdownParser::parse()` — `->frontMatter` (array) + `->html` (string) |
| `ParsedVariant` | `src/Content/ValueObject/ParsedVariant.php` | Value object returned by `MediaController::parseVariant()` — `->originalFilename` (string) + `->variantWidth` (?int) |
| `AdjacentPosts` | `src/Content/ValueObject/AdjacentPosts.php` | Value object returned by `ContentTree::getAdjacentPosts()` — `->prev` + `->next` (`?ContentItem`) |
| `CardLayout` | `src/Content/Enum/CardLayout.php` | Enum: `Top` (layout-top), `Right` (layout-right), `Text` (layout-text), `Left` (layout-left); controls card layout cycling in homepage and project grid |
| `SidebarData` | `src/Content/ValueObject/SidebarData.php` | Value object passed to all docs/blog templates: recent posts, categories, tags, archive months |
| `CategoryCount` | `src/Content/ValueObject/CategoryCount.php` | Value object: category slug + count |
| `TagCount` | `src/Content/ValueObject/TagCount.php` | Value object: tag slug + count |
| `ArchiveMonth` | `src/Content/ValueObject/ArchiveMonth.php` | Value object: year + month for archive listings |
| `RenderResult` | `src/Content/ValueObject/RenderResult.php` | Value object returned by `renderPages()`: pages rendered, skipped, errors |
| `ContentService` | `src/Service/Content/ContentService.php` | Facade: `findByUrl()`, `findPostBySlug()`, `findScheduledPostBySlug()`, `getTree()`, `getRecentPosts()`, `getTranslationMap()` |
| `SrcsetExtension` | `src/Twig/SrcsetExtension.php` | Twig filter `srcset_media` — post-processes rendered HTML to inject `srcset`/`sizes` attributes into `<img src="/media/...webp">` tags for inline content images |
| `TranslationMapTwigExtension` | `src/Twig/TranslationMapTwigExtension.php` | Twig global `translation_map` — `{directoryKey: {locale: url}}` mapping for language switcher and hreflang tags |
| `ContentTwigExtension` | `src/Twig/ContentTwigExtension.php` | Twig functions `content_url(directoryKey, locale)` and `content_item(directoryKey, locale)` — resolve URL or full `ContentItem` by directory key |
| `LangSwitcherExtension` | `src/Twig/LangSwitcherExtension.php` | Twig function `lang_switch_urls(otherLocales)` — resolves language switcher URLs per locale using translation map, controller overrides, and route-based fallbacks (archive → paginated → blog list → home) |
| `StructuredDataExtension` | `src/Twig/StructuredDataExtension.php` | Twig functions `json_ld(data)` — renders array as `<script type="application/ld+json">` with `JSON_PRETTY_PRINT \| JSON_UNESCAPED_SLASHES`; and `structured_data()` — returns `StructuredDataBuilderInterface` for method chaining (e.g. `structured_data().person(...)`) |
| `SidebarExtension` | `src/Twig/SidebarExtension.php` | Twig function `sidebar_data(locale)` — lazily builds `SidebarData` (recent posts, categories, tags, archive months) only when sidebar is rendered; gracefully returns `null` on error |
| `BreadcrumbExtension` | `src/Twig/BreadcrumbExtension.php` | Twig function `breadcrumbs(contentItem, locale, options)` — returns breadcrumb array for any page type (home, blog list/post, archive, category, tag, static page) |

**Global metadata:** `local/content/_site.yaml` — site name, base URL, locales config (first key = default), social links, and all site-level numeric/string settings (see Key Configuration Files). Loaded by `SiteConfigService` and exposed to templates via Twig extensions: `SiteConfigExtension` (Twig globals: `site_name`, `site_base_url`, `site_description`, `site_social`, `site_author`, `site_locales`, `site_locales_list`, `site_default_locale`, `image_variant_widths`, `new_post_days`, `coming_soon_reveal_days`, `meta_description_length`), `TranslationMapTwigExtension` (Twig global: `translation_map`), `ContentTwigExtension` (Twig functions: `content_url()`, `content_item()`), `LangSwitcherExtension`, `SidebarExtension`, and `BreadcrumbExtension`. Additionally, `cf_analytics_token` is registered as a Twig global in `config/packages/twig.yaml`, bound to the `CF_ANALYTICS_TOKEN` environment variable — used in `base.html.twig` to conditionally load the Cloudflare Web Analytics beacon script.

---

## Service Layer

All services are behind interfaces for testability. Controllers depend only on interfaces. Services are grouped by domain in subdirectories.

**`src/Service/Content/`** — content pipeline:

| Service | Interface | Purpose |
|---|---|---|
| `ContentService` | `ContentServiceInterface`, `ContentCacheInterface` | Content access facade: `findByUrl()`, `findPostBySlug()`, `findScheduledPostBySlug()`, `getTree()`, `getPosts()`, `getTotalPosts()`, `getRecentPosts()`, `getTranslationMap()`; implements `ContentCacheInterface` with `invalidateCache($locale)` (cache key prefix: `content_tree_`) |
| `ContentTreeBuilder` | `ContentTreeBuilderInterface` | Scans `local/content/` filesystem and builds `ContentTree` per locale |
| `MarkdownParser` | `MarkdownParserInterface` | Parses Markdown + YAML frontmatter; returns `ParsedMarkdown` value object |
| `SidebarDataProvider` | `SidebarDataProviderInterface` | Builds sidebar data (recent posts, categories, tags, archive months) |
| `TagTranslationService` | `TagTranslationServiceInterface` | Translates tag slugs between locales; reads `local/content/_tags.yaml` |
| `RelatedPostsService` | `RelatedPostsServiceInterface` | Hybrid related-posts scoring algorithm (tags + category + series); replaces removed `ContentTree::getRelatedPosts()` |
| `TranslationMapBuilder` | `TranslationMapBuilderInterface` | Builds locale↔locale URL pairs via co-location (directory key) |

**`src/Service/Image/`** — image processing:

| Service | Interface | Purpose |
|---|---|---|
| `ImageResizer` | `ImageResizerInterface` | ImageMagick wrapper: `resize()` generates responsive variants; `optimize()` recompresses in-place |
| `MediaFileResolver` | `MediaFileResolverInterface` | Resolves a content media file path by `dirKey` + `filename` with `realpath()` path-traversal guard; returns `?string` (null on not-found or traversal) |
| `ResponsiveImageService` | `ResponsiveImageServiceInterface` | Computes variant widths and builds `srcset` attribute values |

**`src/Service/Preview/`** — dev preview toggles:

| Service | Interface | Purpose |
|---|---|---|
| `SessionToggleService` | `SessionToggleServiceInterface` | Generic session-based boolean toggle; injected into both `DraftPreviewService` and `ScheduledPreviewService` as named services |
| `DraftPreviewService` | `DraftPreviewServiceInterface` | Session-based toggle for showing `draft: true` posts in dev |
| `ScheduledPreviewService` | `ScheduledPreviewServiceInterface` | Session-based toggle for showing future-dated posts in dev |

**`src/Service/`** — standalone:

| Service | Interface | Purpose |
|---|---|---|
| `SiteConfigService` | `SiteConfigServiceInterface` | Central locale authority and config source: `getLocales()`, `getDefaultLocale()`, `getLocaleConfig()`, `getSiteConfig()`, `detectLocaleFromPath()`, `getUrlPrefix()`, `getBaseUrl()`, `getPostsPerPage()`, `getRssLimit()`, `getRecentPostsLimit()`, `getRelatedPostsLimit()`, `getImageVariantWidths()`, `getImageQuality()`, `getImageMagickFlags()`, `getNewPostDays()`, `getComingSoonRevealDays()`. Reads `local/content/_site.yaml`; locale list derived from `array_keys(site.locales)`, first key = default |
| `TurnstileValidator` | `TurnstileValidatorInterface` | Verifies Cloudflare Turnstile CAPTCHA tokens against siteverify API |
| `StructuredDataBuilder` | `StructuredDataBuilderInterface` | Builds typed PHP arrays for JSON-LD structured data (WebSite, Person, BlogPosting, CollectionPage, BreadcrumbList, ContactPage, WebPage, Organization, ImageObject); automatic empty-value stripping |
| `ContactFormConfig` | — (value object) | Holds contact form config from `_site.yaml`: `email`, `from`, `fromName`, `topic` |

---

## Routing

### `#[LocalizedRoute]` attribute + `LocalizedRouteLoader`

Controllers use a custom `#[LocalizedRoute]` attribute with the **default locale's path**. The `LocalizedRouteLoader` (`src/Routing/LocalizedRouteLoader.php`) generates `{name}_{locale}` routes for all configured locales:

- **Default locale** → attribute's path as-is (e.g. `/blog/`)
- **Override in `local/content/_routes.yaml`** → `/{locale}` + override path (e.g. `/pl/wpisy/`)
- **No override** → `/{locale}` + attribute's path (e.g. `/pl/feed/`)

Each generated route gets a `locale` default parameter, so controller methods receive `string $locale`.

### URL resolution — two patterns

| What | Pattern | Source of truth |
|---|---|---|
| **Structural routes** (listings, archive, tags, search, contact, feed, errors, API) | `path('route_name_' ~ locale)` | `#[LocalizedRoute]` + `local/content/_routes.yaml` |
| **Content pages/posts** (about, privacy, individual posts) | `content_url(directoryKey, locale)` | `slug` in `.md` frontmatter |

`content_url()` is a Twig function that looks up a `ContentItem` by directory key + locale and returns `->url()`.

### Controllers & Routes

| Controller | EN routes | PL routes (overrides) | DE routes (overrides) |
|---|---|---|---|
| `HomeController` | `GET /` | `GET /pl/` | `GET /de/` |
| `BlogController` | `GET /blog/`, `/blog/page/{page}/`, `/blog/{category}/`, `/tag/{tag}/`, `/archive/{year}/{month}/`, `/archive/{year}/`, `/feed/` | `/pl/wpisy/`, `/pl/wpisy/strona/{page}/`, `/pl/wpisy/{category}/`, `/pl/wpisy/tag/{tag}/`, `/pl/archiwum/{year}/{month}/`, `/pl/archiwum/{year}/`, `/pl/feed/` | `/de/beitraege/`, `/de/beitraege/seite/{page}/`, `/de/beitraege/{category}/`, `/de/beitraege/tag/{tag}/`, `/de/archiv/{year}/{month}/`, `/de/archiv/{year}/`, `/de/feed/` |
| `MediaController` | `GET /media/{dirKey}/{filename}` | Same (no locale prefix) | Same |
| `ContactController` | `GET /contact/`, `POST /api/contact` | `GET /pl/kontakt/`, `POST /pl/api/contact` | `GET /de/kontakt/`, `POST /de/api/contact` |
| `ProjectsController` | `GET /projects/` | `GET /pl/realizacje/` | `GET /de/projekte/` |
| `SearchController` | `GET /search/` | `GET /pl/szukaj/` | `GET /de/suchen/` |
| `ErrorController` | `GET /404/`, `GET /500/` | `GET /pl/404/`, `GET /pl/500/` | `GET /de/404/`, `GET /de/500/` |
| `PageController` | `GET /{slug}/`, `GET /sitemap.xml`, `GET /robots.txt` | `GET /pl/{slug}/` | `GET /de/{slug}/` |
| `StyleguideController` | `GET /styleguide/` (dev only) | — | — |
| `DraftPreviewController` | `GET /dev/drafts/toggle` | (same, no locale prefix) | (same) |
| `ScheduledPreviewController` | `GET /dev/scheduled/toggle` | (same, no locale prefix) | (same) |

Route naming convention: `<name>_pl` / `<name>_en` (e.g. `home_pl`, `blog_list_en`). Templates always use `path('home_' ~ locale)` — never hardcoded URLs.

**Common template variables** passed by every controller:

| Variable | Type | Source |
|---|---|---|
| `locale` | `string` | `'en'` or `'pl'` |
| `content` | `ContentItem\|null` | `ContentService::findByUrl()` |

`translation_map` (`array`) is injected as a Twig global by `TranslationMapTwigExtension` — controllers no longer pass it explicitly. `sidebar` is no longer passed by controllers; templates call `sidebar_data(locale)` via `SidebarExtension` where needed.

---

## Static Build Internals

**`BuildStaticSiteCommand`** (`src/Command/BuildStaticSiteCommand.php`):
- Invalidates the content cache so content changes are always picked up
- Collects URLs using `UrlGeneratorInterface` to generate all route URLs dynamically — no hardcoded paths
- Also renders scheduled (future-dated, non-draft) post URLs as Coming Soon pages — `BlogController` detects the post is scheduled and returns `page/coming-soon.html.twig` with `noindex`
- Makes Symfony sub-requests via `HttpKernelInterface::handle()` — no real HTTP traffic
- Writes each response to `public/static/{path}/index.html`
- Skips dynamic pages (`dynamic: true` in frontmatter) and POST/API endpoints
- Copies `files/` directories from `local/content/` to `public/static/media/{post-dir}/` (`copyMediaFiles()`)
- Recompresses all `.webp` originals in `public/static/media/` in-place via `ImageResizer::optimize()` (`optimizeOriginals()`) — strips EXIF metadata and applies max WebP encoder effort; skips variant files (`-640w`/`-960w` suffix); runs before variant generation so variants are derived from already-stripped sources
- Generates responsive image variants (`-640w.webp`, `-960w.webp`) for all `.webp` images wider than 640px using `ImageResizer::resize()` (`generateResponsiveImages()`); used by `srcset` in templates and `srcset_media` Twig filter
- `renderPages()` handles all HTML rendering (posts, listings, pages, error pages, sitemap, RSS) and returns a `RenderResult` value object with `pages`, `skipped`, and `errors` counts
- Renders sitemap and RSS feeds (EN at `/feed/`, PL at `/pl/feed/`)

**Search index** — Pagefind is run via `npx pagefind@1.5.0 --site public/static` after `app:build`. There is no PHP command for this step. The version is pinned because later Pagefind releases ship a jemalloc-linked ARM64 binary that crashes on 16K-page kernels (e.g. Raspberry Pi 5) — tracked upstream at [Pagefind#1147](https://github.com/Pagefind/pagefind/issues/1147).

---

## Template Override System (`local/`)

notACMS ships as a thin **bare** core plus an optional **demo** theme. Customization happens exclusively in `local/` — core files are never modified.

### Core vs demo

- **Core** (`templates/`, `assets/`, `translations/`): the bare wireframe theme. System fonts, light mode only, minimal CSS. Every feature works without any override.
- **Demo seed** (`docs/demo/`): the amber-phosphor theme shown on notacms.holas.pl. Copied into `local/` by the deploy/build scripts when `--demo` is chosen.
- **Bare seed** (`docs/bare/`): minimal starter content (pages, posts, tags, routes) used when `--bare` is chosen.
- **Compatibility package** (`docs/customization/old-template/`): a one-command restore of the pre-1.1.0 look for users upgrading from 1.0.0.

### Resolution order

Every layer falls back from `local/` to core:

| Layer | Local path | Core fallback | Resolver |
|---|---|---|---|
| Templates | `local/templates/*.html.twig` | `templates/*.html.twig` | Symfony kernel (`NotACms\Local\` namespace resolved first) |
| SCSS entrypoint | `local/assets/styles/app_local.scss` | — (optional) | Imported from `local/assets/app.js` alongside core `app.scss` |
| JS entrypoint | `local/assets/app.js` | `assets/app.js` | AssetMapper importmap (`app-local` overrides `app` when present) |
| Translations | `local/translations/messages.*.yaml` | `translations/messages.*.yaml` | Symfony translator (`default_path: local/translations/`, core is `extra_path`) |
| Content | `local/content/**` | — | `notacms_content` parameter (`%kernel.project_dir%/local/content`) |
| Nginx config | `local/docker/nginx/*.conf` | `docker/nginx.conf.template` | Merged at container entrypoint |

### Seed + build flow

```
./notACMS deploy [--bare|--demo]     # or: ddev build [--bare|--demo]
  │
  ├─ local/ empty or missing?
  │    ├─ --bare:   cp -r docs/bare/content/. local/content/
  │    └─ --demo:   cp -r docs/demo/. local/          (default)
  │
  ├─ Ensure local/assets/images/og-default.jpg exists (copy from core if missing)
  ├─ composer install
  ├─ php bin/console sass:build
  ├─ php bin/console asset-map:compile
  ├─ php bin/console app:build          ← reads local/content/_site.yaml
  └─ npx pagefind --site public/static
```

Subsequent builds with non-empty `local/` skip the seed step. Pass `--bare` or `--demo` explicitly to force a reseed of `local/content/` from the chosen template.

### Writing an override

1. Copy the core file you want to override from `templates/`, `assets/`, or `translations/` into the matching path under `local/`.
2. Edit the copy. The kernel/resolver picks it up automatically on the next build.
3. For SCSS additions that shouldn't replace core, create `local/assets/styles/app_local.scss` and import it from `local/assets/app.js`.

See [docs/CUSTOMIZATION.md](CUSTOMIZATION.md) for concrete examples.

---

## Multi-Language Architecture

**Locale configuration** is centralized in `local/content/_site.yaml`. The `site.locales` keys define the locale list; **first key = default locale**. `SiteConfigServiceInterface` is the single authority — all PHP code injects it instead of `%locales%` or `%kernel.default_locale%`.

- **English (default):** root paths (`/`, `/contact/`, `/blog/`)
- **Polish:** `/{locale}/` prefix (`/pl/`, `/pl/kontakt/`, `/pl/wpisy/`); translated path segments via `local/content/_routes.yaml`
- `LocaleListener` (`src/EventListener/LocaleListener.php`, priority 8) iterates non-default locales from `SiteConfigServiceInterface` to detect locale from URL path; defaults to the configured default locale
- First-time visitors are auto-redirected by `assets/locale-redirect.js` based on `navigator.language`; preference stored in a session cookie (`lang`)
- Content in different languages is linked via **co-location** — `pl.md` and `en.md` in the same directory are automatically linked
- `TranslationMapBuilder` builds `[directoryKey → {locale → url}]` passed to every template for the language switcher and hreflang tags
- UI strings in `translations/messages.pl.yaml` and `translations/messages.en.yaml`
- SEO redirects for old URLs (e.g. `/wpisy/` → `/pl/wpisy/`, `/uploads/` → `/media/`) handled in nginx configuration

---

## Template Hierarchy

```
templates/
├── base.html.twig              ← layout, blocks, global scripts/styling, hreflang, OG/Twitter meta, JSON-LD
├── page/                       ← one template per route (extends base)
│   ├── home.html.twig
│   ├── default.html.twig
│   ├── about.html.twig
│   ├── projects.html.twig
│   ├── contact.html.twig
│   ├── error.html.twig
│   ├── coming-soon.html.twig
│   ├── doc.html.twig
│   └── styleguide.html.twig
├── blog/
│   ├── list.html.twig          ← post listing with pagination (CollectionPage + ItemList JSON-LD)
│   └── post.html.twig          ← single post (BlogPosting JSON-LD with image dimensions)
├── search/
│   └── index.html.twig         ← Pagefind client-side search
├── feed/
│   ├── rss.xml.twig            ← RSS with content:encoded, categories
│   ├── robots.txt.twig         ← served by PageController at /robots.txt
│   └── sitemap.xml.twig        ← XML sitemap with hreflang + image entries
├── email/
│   └── contact.html.twig       ← email template for contact form submissions
└── components/                 ← reusable partials ({{ include('components/...') }})
    └── *.html.twig             ← naming: snake_case, e.g. post_card.html.twig
```

---

## Asset Pipeline

- **SCSS** → CSS: `assets/styles/app.scss` compiled by dart-sass (`symfonycasts/sass-bundle`). No Node.js required.
- **JavaScript**: loaded in three ways. **Importmap entries** (bundled by AssetMapper): `app` (entry point — imports `app.scss`, plus `nav-toggle.js` in the bare core). **Sync `<script>` in `<head>`**: `locale-redirect.js` (runs before render for browser language detection). **Deferred `<script>` at `</body>`**: `cookie-banner.js`. **Page-specific** (via `{% block javascripts %}`): one script per page that needs it (e.g. `contact.js`, `code-copy.js`, `reading-progress.js`, `table-of-contents.js`, `search.js`). The demo theme layers additional scripts on top via `local/assets/` (`theme-toggle.js`, `search-overlay.js`, `docs-sidebar.js`, `docs-toc.js`, `lang-dropdown.js`).
- Asset versioning via `symfony/asset-mapper` — `{{ asset('file.js') }}` in templates outputs hashed paths.

SCSS directory layout (flat — no subdirectories):

```
assets/styles/
├── app.scss          ← entry point, @imports all partials
├── _tokens.scss      ← CSS custom properties for theming (light/dark mode colors)
├── _variables.scss   ← compile-time SCSS constants (spacing, radii, breakpoints, fonts)
├── _base.scss        ← CSS reset, base element styles
├── _layout.scss      ← page layout: containers, content grids, main wrapper
├── _nav.scss         ← site header, navigation, search overlay
├── _components.scss  ← shared reusable UI components
├── _prose.scss       ← rendered Markdown content styling
├── _pages.scss       ← page-specific styles (home, contact, about, etc.)
├── _blog.scss        ← blog post and list page styles
├── _styleguide.scss  ← dev-only design reference page scaffolding
└── _utilities.scss   ← atomic utility classes (spacing, display, text)
```

---

## Contact Form Flow

The contact page (`/contact/`, `/pl/kontakt/`) is **pre-rendered static HTML** by `app:build`. CSRF protection is intentionally disabled on `ContactType` because Symfony's CSRF tokens are session-bound — a token baked into static HTML would be identical for all visitors and tied to the build-time session, making it impossible to validate against any visitor's own session. The defenses instead are: Cloudflare Turnstile CAPTCHA (prevents automated abuse) and the `X-Requested-With: XMLHttpRequest` header on the AJAX request (triggers CORS preflight, preventing cross-origin form submission from malicious sites).

1. nginx serves pre-rendered static HTML with Turnstile widget (site key baked in at build time)
2. Turnstile callback writes token into hidden `contact[turnstile_token]` field
3. `contact.js` intercepts submit, posts form data via `fetch()` to `/api/contact` (EN) or `/pl/api/contact` (PL)
4. `ContactController::submit()` validates form, verifies Turnstile token, sends email via `MailerInterface`
5. Returns `{"success": true}` (200), `{"error": "..."}` (422 validation), or `{"error": "..."}` (500 mailer)
6. `contact.js` shows success or error message based on response

---

## Key Configuration Files

| File | Purpose |
|---|---|
| `local/content/_site.yaml` | **Locale list** (keys of `site.locales`, first = default), per-locale metadata (`og_locale`, `label`, `date_format`, `tagline`), site name, base URL (`base_url`), social links, contact form config (`contact_form.email/from/from_name/topic`), `posts_per_page`, `rss_limit`, `recent_posts_limit`, `related_posts_limit`, `new_post_days`, `coming_soon_reveal_days`, `meta_description_length`, `image_variant_widths`, `image_quality`, `image_magick_flags` |
| `local/content/_routes.yaml` | Locale-specific URL path overrides for structural routes (e.g. `blog_list: {pl: /wpisy/}`) |
| `local/content/_tags.yaml` | Tag translations: canonical (default locale) tag → `{locale: equivalent}`. Used by `TagTranslationService::translate()` for language switcher on tag pages |
| `config/services.yaml` | `notacms_content` parameter (default: `%kernel.project_dir%/local/content`, overridden to `tests/Fixtures/content` in test env); `notacms.local_dir` parameter (default: `local`, overridden to `tests/Fixtures` in test env to decouple Twig paths from instance templates); `notacms_static_dir` parameter (default: `%kernel.project_dir%/public/static`); service auto-discovery for `NotACms\` and `NotACms\Local\` namespaces |
| `.env` | `MAILER_DSN`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `URL` (bare domain, e.g. `example.com` — used by Docker Compose / Certbot; must match `base_url` in `_site.yaml`) |
| `.ddev/config.yaml` | DDEV local environment: PHP 8.5, nginx-fpm, no database, Mailpit on port 1025 |
| `config/packages/framework.yaml` | `default_locale: en` — Symfony internals only (translator fallback); must match first key in `_site.yaml` |
| `config/packages/translation.yaml` | `default_locale: en`; `default_path` is `local/translations/` (overrides take priority), core `translations/` loaded as extra path (fallback) |
| `config/packages/` | Symfony package configuration (mailer, twig, framework, etc.) |
| `docker/nginx.conf` | Production nginx: static-first serving, API routing, SEO redirects, error pages |
