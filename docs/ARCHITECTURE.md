# Architecture

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
| `slug` | string | Full URL path (e.g. `2026/01/10/my-post` or `pl/2026/01/10/my-post`) |
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
| `ContentItem` | `src/Content/ContentItem.php` | Value object: parsed frontmatter + rendered HTML body; key methods: `readingTime()` (word-count estimate), `wordCount()` (raw word count for schema.org), `relatedSlugs()` (manual `related:` frontmatter), `isScheduled()`, `isPinned()`, `isDraft()`, `isFeatured()`, `series()`, `seriesOrder()`, `directoryKey()` (co-location translation key) |
| `ContentTree` | `src/Content/ContentTree.php` | Immutable typed collection of ContentItem for one locale; constructed with `$includeDrafts` and `$includeScheduled` flags; sorts posts by pinned first, then date DESC; key methods: `getAdjacentPosts()` (returns `AdjacentPosts` VO with `->prev` / `->next`), `getSeriesPosts()` (posts in the same series, sorted by series_order) |
| `ContentTreeBuilder` | `src/Service/Content/ContentTreeBuilder.php` | Implements `ContentTreeBuilderInterface`. Scans `local/content/` for `{locale}.md` and `_index_{locale}.md` files → ContentTree (uses `league/commonmark`; injected as `MarkdownParserInterface`) |
| `ParsedMarkdown` | `src/Content/ValueObject/ParsedMarkdown.php` | Value object returned by `MarkdownParser::parse()` — `->frontMatter` (array) + `->html` (string) |
| `ParsedVariant` | `src/Content/ValueObject/ParsedVariant.php` | Value object returned by `MediaController::parseVariant()` — `->originalFilename` (string) + `->variantWidth` (?int) |
| `AdjacentPosts` | `src/Content/ValueObject/AdjacentPosts.php` | Value object returned by `ContentTree::getAdjacentPosts()` — `->prev` + `->next` (`?ContentItem`) |
| `ContentService` | `src/Service/Content/ContentService.php` | Facade: `findByUrl()`, `findPostBySlug()`, `findScheduledPostBySlug()`, `getTree()`, `getRecentPosts()`, `getTranslationMap()` |
| `SrcsetExtension` | `src/Twig/SrcsetExtension.php` | Twig filter `srcset_media` — post-processes rendered HTML to inject `srcset`/`sizes` attributes into `<img src="/media/...webp">` tags for inline content images |
| `TranslationMapTwigExtension` | `src/Twig/TranslationMapTwigExtension.php` | Twig global `translation_map` — `{directoryKey: {locale: url}}` mapping for language switcher and hreflang tags |
| `ContentTwigExtension` | `src/Twig/ContentTwigExtension.php` | Twig function `content_url(directoryKey, locale)` — resolves URL for a content item by directory key |

**Global metadata:** `local/content/_site.yaml` — site name, base URL, locales config (first key = default), social links, and all site-level numeric/string settings (see Key Configuration Files). Loaded by `SiteConfigService` and exposed to templates via three Twig extensions: `SiteConfigExtension` (Twig globals: `site_name`, `site_base_url`, `site_description`, `site_social`, `site_author`, `site_locales`, `site_locales_list`, `site_default_locale`, `image_variant_widths`, `new_post_days`, `coming_soon_reveal_days`), `TranslationMapTwigExtension` (Twig global: `translation_map`), and `ContentTwigExtension` (Twig function: `content_url()`). Additionally, `cf_analytics_token` is registered as a Twig global in `config/packages/twig.yaml`, bound to the `CF_ANALYTICS_TOKEN` environment variable — used in `base.html.twig` to conditionally load the Cloudflare Web Analytics beacon script.

---

## Service Layer

All services are behind interfaces for testability. Controllers depend only on interfaces. Services are grouped by domain in subdirectories.

**`src/Service/Content/`** — content pipeline:

| Service | Interface | Purpose |
|---|---|---|
| `ContentService` | `ContentServiceInterface`, `ContentCacheInterface` | Content access facade: `getTree()`, `findByUrl()`, `getPosts()`, `getTotalPosts()`, `getRecentPosts()`, `getTranslationMap()`; `invalidateCache()` on `ContentCacheInterface` |
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

| Controller | EN routes | PL routes (overrides in `_routes.yaml`) |
|---|---|---|
| `HomeController` | `GET /` | `GET /pl/` |
| `BlogController` | `GET /blog/`, `/blog/page/{page}/`, `/blog/{category}/`, `/tag/{tag}/`, `/archive/{year}/{month}/`, `/archive/{year}/`, `/feed/` | `/pl/wpisy/`, `/pl/wpisy/strona/{page}/`, `/pl/wpisy/{category}/`, `/pl/tag/{tag}/`, `/pl/archiwum/{year}/{month}/`, `/pl/archiwum/{year}/`, `/pl/feed/` |
| `MediaController` | `GET /media/{dirKey}/{filename}` | Same (no locale prefix) |
| `ContactController` | `GET /contact/`, `POST /api/contact` | `GET /pl/kontakt/`, `POST /pl/api/contact` |
| `ProjectsController` | `GET /projects/` | `GET /pl/realizacje/` |
| `SearchController` | `GET /search/` | `GET /pl/szukaj/` |
| `ErrorController` | `GET /404/`, `GET /500/` | `GET /pl/404/`, `GET /pl/500/` |
| `PageController` | `GET /{slug}/`, `GET /sitemap.xml`, `GET /robots.txt` | `GET /pl/{slug}/` |
| `DraftPreviewController` | `GET /dev/drafts/toggle` | (same, no locale prefix) |
| `ScheduledPreviewController` | `GET /dev/scheduled/toggle` | (same, no locale prefix) |

Route naming convention: `<name>_pl` / `<name>_en` (e.g. `home_pl`, `blog_list_en`). Templates always use `path('home_' ~ locale)` — never hardcoded URLs.

**Common template variables** passed by every controller:

| Variable | Type | Source |
|---|---|---|
| `locale` | `string` | `'en'` or `'pl'` |
| `content` | `ContentItem\|null` | `ContentService::findByUrl()` |
| `sidebar` | `SidebarData` | `SidebarDataProvider::getData()` |

`translation_map` (`array`) is injected as a Twig global by `TranslationMapTwigExtension` — controllers no longer pass it explicitly.

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

**Search index** — Pagefind is run via `npx pagefind --site public/static` after `app:build`. There is no PHP command for this step.

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
├── base.html.twig                    ← layout, hreflang, OG + Twitter social meta (with image dimensions), WebSite JSON-LD, nav, sidebar, cookie banner
├── page/
│   ├── home.html.twig                ← homepage with post cards
│   ├── default.html.twig             ← generic content page
│   ├── about.html.twig               ← about page with profile, skills, recommendations
│   ├── projects.html.twig            ← portfolio grid with featured project cards
│   ├── contact.html.twig             ← contact form + Turnstile widget
│   ├── error.html.twig               ← error pages (404, 500)
│   └── coming-soon.html.twig         ← scheduled post placeholder (green terminal, noindex)
├── search/
│   └── index.html.twig               ← Pagefind client-side search
├── blog/
│   ├── list.html.twig                ← post listing with pagination (category/tag/archive); CollectionPage + ItemList JSON-LD when posts exist
│   └── post.html.twig                ← single post; BlogPosting JSON-LD with `inLanguage`, `wordCount`, `articleSection`, `keywords`, ImageObject for featured image
├── feed/
│   ├── rss.xml.twig                  ← RSS feed with `content:encoded` (full HTML), `<category>` tags, `<lastBuildDate>`
│   ├── robots.txt.twig               ← robots.txt served by PageController at `/robots.txt`
│   └── sitemap.xml.twig              ← XML sitemap with hreflang + image:image entries (image:loc, image:title) for posts with featured images
├── email/
│   └── contact.html.twig             ← email template for contact form submissions
└── components/
    ├── navigation.html.twig
    ├── sidebar_top.html.twig
    ├── sidebar_bottom.html.twig
    ├── language_switcher.html.twig
    ├── breadcrumb.html.twig           ← HTML breadcrumb nav + BreadcrumbList JSON-LD for rich results
    ├── pagination.html.twig
    ├── post_card.html.twig           ← card component; 4 layout variants (top/right/text/left); uses responsive_img
    ├── post_card_mini.html.twig      ← compact card for related posts section; entire card is a single `<a>` block
    ├── post_meta_line.html.twig      ← category + date meta line; used in post cards and post header
    ├── post_navigation.html.twig     ← prev/next post navigation (prev = older, next = newer)
    ├── recommendation_card.html.twig ← expandable blockquote card for recommendations on about page
    ├── responsive_img.html.twig      ← `<img>` with srcset (640w/960w/1280w) and sizes; used by featured_image and post_card
    ├── share_buttons.html.twig
    ├── series_nav.html.twig          ← collapsible series navigation for multi-part posts
    └── featured_image.html.twig      ← wraps responsive_img; adds pagefind meta attribute
```

---

## Asset Pipeline

- **SCSS** → CSS: `assets/styles/app.scss` compiled by dart-sass (`symfonycasts/sass-bundle`). No Node.js required.
- **JavaScript**: all files in `assets/`. Global (every page): `cookie-banner.js`, `nav-toggle.js`, `locale-redirect.js` (runs sync in `<head>`). Also global: `lightbox.js` — bundled into `app.js` via importmap, not a `<script src>` tag. Page-specific (via `{% block javascripts %}`): `code-copy.js` (blog posts), `copy-link.js` (blog posts), `reading-progress.js` (blog posts), `table-of-contents.js` (blog posts — auto-generates ToC for 3+ headings), `contact.js` + Turnstile (contact page), `search.js` (search page), `recommendation-expand.js` (about page + styleguide). See AGENTS.md → "JavaScript loading" for the rule on adding new JS.
- Asset versioning via `symfony/asset-mapper` — `{{ asset('file.js') }}` in templates outputs hashed paths.

SCSS directory layout (flat — no subdirectories):

```
assets/styles/
├── app.scss          ← entry point, @imports all partials
├── _variables.scss   ← design tokens (colors, typography, spacing, layout, animation)
├── _base.scss        ← CSS reset + base typography
├── _layout.scss      ← .container, .content-layout, .content-sidebar
├── _nav.scss         ← .site-header, .site-title, .site-nav
├── _sidebar.scss     ← sidebar widgets
├── _components.scss  ← shared UI: buttons, badges, alerts, code, tables, images
├── _blog.scss        ← post cards, post body, post header, post meta, series nav
├── _pages.scss       ← page-specific styles: home, contact, about, projects, error, cookie banner
└── _styleguide.scss  ← dev-only styleguide scaffolding (swatches, spacing bars)
```

---

## Contact Form Flow

The contact page (`/contact/`, `/pl/kontakt/`) is **pre-rendered static HTML** by `app:build`. CSRF protection is intentionally disabled on `ContactType` because Symfony's CSRF tokens are session-bound — a token baked into static HTML would be identical for all visitors and tied to the build-time session, making it impossible to validate against any visitor's own session. The defenses instead are: Cloudflare Turnstile CAPTCHA (prevents automated abuse) and the `X-Requested-With: XMLHttpRequest` header on the AJAX request (triggers CORS preflight, preventing cross-origin form submission from malicious sites).

1. nginx serves pre-rendered static HTML with Turnstile widget (site key baked in at build time)
2. Turnstile callback writes token into hidden `contact[turnstile_token]` field
3. `contact.js` intercepts submit, posts form data via `fetch()` to `/api/contact` (EN) or `/pl/api/contact` (PL)
4. `ContactController::handleSubmit()` validates form, verifies Turnstile token, sends email via `MailerInterface`
5. Returns `{"success": true}` (200), `{"error": "..."}` (422 validation), or `{"error": "..."}` (500 mailer)
6. `contact.js` shows success or error message based on response

---

## Key Configuration Files

| File | Purpose |
|---|---|
| `local/content/_site.yaml` | **Locale list** (keys of `site.locales`, first = default), per-locale metadata (`og_locale`, `label`, `date_format`, `tagline`), site name, base URL (`base_url`), social links, contact form config (`contact_form.email/from/from_name/topic`), `posts_per_page`, `rss_limit`, `recent_posts_limit`, `related_posts_limit`, `new_post_days`, `coming_soon_reveal_days`, `image_variant_widths`, `image_quality`, `image_magick_flags` |
| `local/content/_routes.yaml` | Locale-specific URL path overrides for structural routes (e.g. `blog_list: {pl: /wpisy/}`) |
| `local/content/_tags.yaml` | Tag translations: canonical (default locale) tag → `{locale: equivalent}`. Used by `TagTranslationService::translate()` for language switcher on tag pages |
| `.env` | `MAILER_DSN`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `URL` (bare domain, e.g. `example.com` — used by Docker Compose / Certbot; must match `base_url` in `_site.yaml`) |
| `.ddev/config.yaml` | DDEV local environment: PHP 8.5, nginx-fpm, no database, Mailpit on port 1025 |
| `config/packages/framework.yaml` | `default_locale: en` — Symfony internals only (translator fallback); must match first key in `_site.yaml` |
| `config/packages/translation.yaml` | `default_locale: en`; adds `local/translations/` as extra path (merged on top of core `translations/`) |
| `config/packages/` | Symfony package configuration (mailer, twig, framework, etc.) |
| `docker/nginx.conf` | Production nginx: static-first serving, API routing, SEO redirects, error pages |
