# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-06-12

### Breaking changes

Full migration guide with before/after snippets: [UPGRADE-1.2.md](UPGRADE-1.2.md).

- **`getTree()` moved off the content facade**: `ContentServiceInterface` is now a pure query facade (gains `findByDirectoryKey()`, `getPostsByTag/Category/YearMonth/Year()`); tree-level consumers inject the new `ContentTreeProviderInterface { getTree(locale) }` instead. `local/src` code calling `getTree()` via `ContentServiceInterface` must swap the injected interface (one line).
- **`structured_data().blogPosting()` now takes a named map** (backed by the `BlogPostingData` VO) instead of 13 positional arguments — see UPGRADE-1.2.md for the before/after Twig snippet. Core, demo, and holas.pl post templates are updated.
- **`lang_switch_url` context key removed**: the language switcher consumes a typed `LangSwitchContext` (context key `lang_switch`) resolved by the new `LangSwitchUrlResolver`; tag pages get per-locale URLs for **all** other locales via `TagLangSwitchResolver` (previously only the first other locale — broken on 3+-locale sites). Theme templates calling `lang_switch_urls()` are unaffected.
- **`SiteConfigServiceInterface` split** into `LocaleConfigInterface` + `SiteSettingsInterface` + `ImageConfigInterface` via interface composition — existing type-hints keep working; narrow consumers now inject only the slice they use.
- **`ContentItem::directoryKey()` now returns the full relative content path** (e.g. `pages/about` instead of `about`), fixing silent collisions between same-named directories in different sections (translation map, hreflang, `content_item()` lookups). `content_item()`/`content_url()`/`findByDirectoryKey()` still accept a bare basename when it is unambiguous, so theme literals like `content_item('about', locale)` keep working; ambiguous basenames return null with a build warning. Breaking only if you compare `directoryKey()` against a literal or index `translation_map` with hand-written basenames.

### Changed

- **Pinned semantics**: `pinned: <date>` is now inclusive (the post stays pinned *on* that date, as documented in the editor guide), and `pinned: true` pins indefinitely (previously silently never pinned).
- **Category/tag URL normalization**: uppercase or spaced `category:`/`tags:` values are lowercased (spaces → hyphens) for grouping and URLs instead of crashing URL generation with an `InvalidParameterException`; a build warning asks the author to fix the frontmatter.
- **Post detection by location, not date**: new `ContentItem::isPost()` (set from the `blog/` content prefix) replaces the date-presence heuristic — dated static pages no longer receive blog-post breadcrumbs. Also new: `ContentItem::isSame()`, `ContentTree::getSeriesPosition()`, `ContentTree::getPublishableStaticPages()`.
- **Static build decomposed**: `app:build` is now orchestration over three services (`StaticUrlCollector`, `StaticPageRenderer`, `MediaPublisher` in `src/Service/StaticBuild/`) — same CLI name, options, and output. `#[LocalizedRoute]` attributes are now also discovered in `local/src/Controller/` (`NotACms\Local\Controller\`), so site overrides can register localized routes. The two preview toggle controllers merged into one `PreviewToggleController` (same routes); `MediaController` builds its variant map lazily (no constructor I/O).

- **Hardcoded UI strings moved to translation keys** across bare (core) and demo themes. Core post template `[DRAFT]`/`[PLANNED]` banners are now `blog.draft_banner`/`blog.scheduled_banner` trans keys; coming-soon publishing date uses a `%date%` placeholder in `coming_soon.publishing`. Core navigation null-safety improved: `content_item()` return values are cached and checked before calling `.menuLabel()`, with `nav.label.*` trans keys as fallbacks. Demo template hardcoded strings replaced: `Tags:` label, `Post navigation`/`Page navigation`/`Key stats` aria-labels, and `breadcrumb` aria-label across all theme layers. New translation keys added to core EN/PL, demo EN/DE/FR/PL, and local-holas.pl EN/PL.

### Fixed

- **Build resilience for malformed content**: a markdown file with broken YAML frontmatter no longer 500s every page of its locale or aborts `app:build` — the file is skipped and reported (console "Content warnings" section + log) with its path and the parse error.
- **Homepage hijack via missing `slug`**: content files without a `slug` frontmatter key previously computed the URL `/` and silently overwrote the homepage. They are now skipped with a warning (the home page declares `slug: ""` explicitly).
- **Draft/scheduled leak**: pages with `draft: true` (or a future date) were rendered into static builds, served via URL lookup, and listed in menus and `/llms.txt`. Pages and the URL/menu maps now honour the same draft/scheduled visibility rules as posts (preview toggles unaffected).
- **Config hardening**: numeric `_site.yaml` settings (`posts_per_page`, `rss_limit`, `llms_limit`, `recent_posts_limit`, `related_posts_limit`, `image_quality`, `meta_description_length`) are clamped to ≥1 (`new_post_days`, `coming_soon_reveal_days` to ≥0) — `posts_per_page: 0` no longer causes a DivisionByZeroError.
- **nginx**: the locale-prefixed contact API location regex matched a literal `{2}` (escaped braces), so `POST /<locale>/api/contact` never reached PHP — the contact form was broken for every non-default locale in the Docker runtime deployment. The regex is now quoted.
- **Draft/scheduled translations in hreflang**: the translation map no longer advertises draft or scheduled locale variants — hreflang and language-switcher links no longer point at URLs that 404 in production.
- **Frontmatter robustness**: non-string scalars (`title: 42`, unquoted `slug: 2024`) no longer throw TypeErrors; dates that aren't exactly `YYYY-MM-DD` (e.g. with a time) now parse via a `DateTimeImmutable` fallback instead of silently becoming undated.
- **Build diagnostics**: duplicate URLs (two files computing the same URL), ambiguous directory keys, and media directory basename collisions now produce explicit build warnings naming the offending source paths (previously silent last-wins).
- **Config validation**: `locales:` written as a YAML list (instead of a map) and unparseable/missing `_site.yaml` now fail with descriptive errors naming the file and the fix.
- **Route cache freshness**: the routing cache now tracks `_site.yaml` and `_routes.yaml` as resources — adding a locale or editing route overrides takes effect in dev without a manual `cache:clear`.
- **Empty category indexes**: category index pages whose category has no published posts are skipped during the static build (previously a guaranteed build error on every run).
- **RSS feed correctness**: titles and excerpts inside CDATA are no longer double-encoded (`&amp;` shown literally in feed readers); a literal `]]>` in post HTML can no longer truncate the feed; `lastBuildDate` now uses the newest post date instead of regressing to a pinned post's date.
- **Missing `blog.title` translation**: added to core EN/PL catalogs — the blog-list title fallback rendered the raw key when no blog index page existed.
- **Home template null-safety**: the about-page action button is guarded — a site without an about page no longer crashes the homepage.
- **`turnstile_site_key` is now a Twig global** (configured in `twig.yaml`) instead of a per-render context key — same variable name, available everywhere; custom contact templates are unaffected.
- **Preview toggle return-redirect**: the draft/scheduled toolbar toggles now return to the page you were on (browsers send absolute Referer URLs, which the old same-site check always rejected — every toggle bounced to `/`). Same-origin validation kept.
- **Language switcher on year archives**: year-only archive pages now link to the other locale's year archive instead of its home page.
- **Uncategorized posts no longer "related"**: two posts with no category no longer score a category match in related-posts selection.
- **`app:build --force` guard**: the build refuses to clear an output directory other than the configured static dir unless `--force` is passed (previously `-o public` would silently wipe the whole public directory); the pagefind hint also reports the resolved output dir.
- **Empty archives 404**: `/archive/<year>/` and `/archive/<year>/<month>/` with no posts return 404 instead of an empty 200 listing (consistent with tag/category pages).
- **Media variant fallback**: a real source file named like a variant (`photo-640w.webp`) is served as-is when no `photo.webp` original exists, instead of 404ing.
- **Non-WebP image safety**: srcset generation (PHP service + the responsive_img component) is skipped for non-`.webp` sources instead of producing mangled variant URLs.
- **Multi-segment page slugs warn**: a non-blog page with `slug: docs/intro` now produces a build warning (the static-page route only matches single segments, so such pages 404).
- **Contact form misconfiguration surfaced**: when `contact_form.from`/`email` are missing from `_site.yaml`, the page shows a translated "form unavailable" notice and submissions return 503 with a logged error (previously a generic 500 on first real submission).
- **No `base_url`, no broken link attrs**: with an empty `base_url` the markdown external-link extension is skipped, instead of marking every absolute link to your own site as `nofollow external` + new-window.
- **Undated posts**: post pages no longer emit the current timestamp as `article:published_time`/JSON-LD `datePublished` when a post has no date.

- **locale-redirect no-cookie redirect**: visiting a non-default locale URL (e.g. `/pl/`) for the first time with no `lang` cookie no longer bounces the visitor to the browser-language equivalent — the explicit URL is treated as the preference, the cookie is set to match, and no redirect fires. The `Secure` cookie flag is now conditional on `https:` so the mechanism works in HTTP dev environments (DDEV `http://`) without weakening production behaviour. Affects core and demo copies.
- **search excerpt highlight tags**: `search.js` was passing pagefind excerpts through `esc()`, escaping `<mark>` highlight tags to literal `&lt;mark&gt;` text. Excerpts are now inserted as raw HTML — pagefind content is trusted, author-controlled static HTML. Affects core and demo copies.
- **bare `_site.yaml` missing `contact_form`**: the bare starter theme lacked `contact_form.email`/`from` entries, producing a logged error on every build. Placeholder values added.

### Added

- **`/llms.txt` feed**: new `GET /llms.txt` route included in the static build — one file per locale listing the most recent posts in a machine-readable format for LLM context use. Configurable via `llms_limit` in `_site.yaml` (default 5). The core template is overridable per-instance via the `@base` Twig namespace. `notacms_project_url` Twig global added for cross-theme reference.
- **`docs/THEME_BUILDING.md`** — the theme API reference: per-route template context contracts, Twig globals/functions/filters, the ContentItem API, required translation keys, request attributes, the assets contract, and theme portability rules. AGENTS.md now requires reviewing it whenever controller contexts, Twig extensions, or globals change.
- **Translated contact notification email** — new `contact.email.*` keys (EN/PL); the owner-facing email renders in the submission locale instead of hardcoded English.

### Removed

- **`docs/customization/old-template/`** (the 1.0→1.1 compatibility package) — copy it from a v1.1.x tag if you still need it; all docs references updated with that guidance.
- **Dead core translation keys** `blog.category`, `blog.archive`, `sidebar.archive`, `about.cta_text` — used by no shipped template (holas.pl/old-template themes define their own copies). Custom themes referencing these keys via core fallback must define them in `local/translations/`.

### Internal

- **ImageMagick via `Symfony\Process`**: `ImageResizer` builds argv arrays instead of shell strings (`exec()` removed).
- **Constants over magic values**: directory keys (`blog`/`home`), config filenames (`_site.yaml`, `_tags.yaml`, `_routes.yaml`), badge names, seconds-per-day, default OG image, default template, and data-collector ids are now named constants; `FilterType` enum used instead of string literals; interface method defaults reference the `SiteConfigServiceInterface::DEFAULT_*` constants.
- **`Breadcrumb` value object** replaces associative arrays in `BreadcrumbExtension` (templates unaffected — property access matches the old keys).
- **`MediaFileResolver` memoizes** directory lookups (was a full recursive content scan per media request).
- **Category index resolution** uses `findByDirectoryKey()` instead of reconstructing URLs by string concatenation.
- **Preview service wiring** moved from `services.yaml` argument blocks to `#[Autowire]` attributes; dead `$routeOverrides` caching and the unused `locale` form option removed; styleguide fixtures de-branded to generic paths.
- **AGENTS.md import-order rule corrected**: imports are alphabetical (enforced by PHP CS Fixer `ordered_imports`); the documented "App → PSR → Symfony" grouping was never the enforced rule.
- **`_static_build` documented** in ARCHITECTURE.md as a supported request attribute for themes/local extensions.
- **Template dedup & a11y**: shared `hreflang_alternates` macro (base head + sitemap), shared `page_article` component (default + projects pages), shared profiler `_toggle_item` (draft/scheduled collectors), `post_card` reuses `post_meta_line`; icon-only buttons gained `title` attributes; error template uses `error.title` key; robots.txt uses the `sitemap` route; favicon via `asset()`; base template sets `locale` once (illusory per-use guards removed); `og_image` variable no longer shadows the `og_image_url()` function; demo/bare `draft-post` demo content now actually carries `draft: true`.

### Security

- **Turnstile hostname validation**: `TurnstileValidator` now verifies the `hostname` returned by Cloudflare's siteverify against the configured `base_url` host (www-tolerant; skipped in debug or when `base_url` is unset), rejecting tokens minted on foreign domains.
- **Test-key and placeholder-secret detection**: an error is logged when the committed always-pass Turnstile test keys are used outside debug mode, and a critical is logged at first request when `APP_SECRET` is still the committed `changeme` placeholder.
- **JSON-LD hardening**: structured data is encoded with `JSON_HEX_TAG`/`JSON_HEX_AMP`/`JSON_HEX_APOS`/`JSON_HEX_QUOT`, so a `</script>` sequence in a title or description can no longer terminate the JSON-LD block.
- **Translation catalogs no longer carry HTML**: the `contact.about_nudge` link markup moved into the template (new `contact.about_nudge_link` key); the `|raw` filter on catalog output is gone.
- **nginx security headers on static assets**: `add_header` in the `/assets/` and `/media/` locations was suppressing all inherited server-level headers; the security header set (X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, CSP) is now emitted there too.
- **Threat model documented**: SECURITY.md now records the static-first threat model and the deliberate design decisions (Turnstile-as-CSRF on the contact endpoint, debug-gated previews, no content execution at build time).

## [1.1.4] - 2026-05-28

### Changed

- `symfony/polyfill-*` updated from v1.37.0 to v1.38.1. `phpstan/phpstan` 2.1.55 → 2.2.1. `phpunit/phpunit` 13.1.10 → 13.1.13. `rector/rector` 2.4.4 → 2.4.5.

### Security

- **Symfony 7.4.12 → 7.4.13** (all components unified) — 6 CVEs fixed. Notable: CVE-2026-48489 (Security firewall bypass — attacker-controlled `_failure_path` honored on `failure_forward` internal subrequest), CVE-2026-48736 (SSRF bypass in `NoPrivateNetworkHttpClient` / `IpUtils` via IPv6 transition address forms), CVE-2026-48761/48760 (two further `HtmlSanitizer` bypasses — unfiltered URL attributes on `<object>`, `<applet>`, `<iframe>`, `<img>`, `<meta refresh>`, and BiDi marks/Unicode whitespace in URLs), CVE-2026-48784 (`UrlGenerator` misencodes chained `../`/`./` segments), CVE-2026-48747 (Mailer: Mailomat webhook signature algorithm not pinned to SHA-256). Full list: [symfony.com/blog/symfony-7-4-13-released](https://symfony.com/blog/symfony-7-4-13-released).
- **Twig 3.26.0 → 3.27.0** — 5 CVEs fixed, all sandbox bypasses: CVE-2026-46636 (allow-list bypass when sandbox state changes between renders in long-lived workers), CVE-2026-48808 (`column` filter bypasses property allowlist under `SourcePolicyInterface`), CVE-2026-48806 (`__toString()` bypass via dynamic mapping keys in array expressions), CVE-2026-48807 (`__toString()` bypass via `Traversable` in `join`/`replace` filters and `in`/`not in` operators), CVE-2026-48805 (sandbox state regression in deprecated internal wrappers).

## [1.1.3] - 2026-05-20

### Added

- **JSON Schema files** for all config and content frontmatter — six JSON Schema draft-07 files in `config/schema/`: `site.schema.json`, `routes.schema.json`, `tags.schema.json`, `post.frontmatter.schema.json`, `page.frontmatter.schema.json`, `category.frontmatter.schema.json`. Schemas are optimised for AI-assisted authoring with rich descriptions, defaults, and constraint explanations. Schemas are fetchable directly from the main branch at `https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/<name>.schema.json`.
- **`yaml-language-server` comments** added to all template YAML files (`_site.yaml`, `_routes.yaml`, `_tags.yaml`) in `docs/bare/`, `docs/demo/`, and `docs/customization/old-template/`, pointing to the raw GitHub schema URL — enables in-editor validation and autocomplete without project configuration.
- **Schema documentation** in `AGENTS.md` (new "Config & frontmatter schemas" section), `docs/EDITOR_GUIDE.md` (blockquote in frontmatter reference), `docs/ARCHITECTURE.md` (schema column on content config rows), and `docs/demo/content/pages/manual/` (schema links in all four locales).
- **AI-agent skills**: `.claude/skills/generate-featured-image/` — generates, reviews, and deploys featured images using Draw Things.

### Fixed

- **Stale Pagefind fragments** — `scripts/rebuild-content.sh` now wipes `public/pagefind/` before reindexing. Previously, removing or renaming content left orphaned fragment files that Pagefind served alongside fresh results.

### Changed

- **DESIGN.md token hygiene** — hardcoded `rgba()`/hex values replaced with token references in `docs/bare/docs/DESIGN.md` and `docs/demo/docs/DESIGN.md`. `primary: "{colors.accent}"` alias added to both. `card-hover` component token (accent border on hover) added to the demo DESIGN.md. `typography.label` reference corrected in demo DESIGN.md.

### Security

- **Symfony 7.4.8 → 7.4.12** — 21 CVEs fixed. Notable: CVE-2026-45073 (SQL injection in `Cache` via unsanitized prefix), CVE-2026-45071 (XXE in `DomCrawler`), CVE-2026-45075 (HEAD bypass of `#[IsGranted]`/`#[IsCsrfTokenValid]`/`#[IsSignatureValid]`), CVE-2026-45072 (XSS in `TwigBridge::CodeExtension`), CVE-2026-45068 (header injection in `SendmailTransport`), CVE-2026-45067 (line-break injection in `Mime\Address`), CVE-2026-45305/45304/45133 (YAML parser ReDoS/recursion), CVE-2026-45066/45064/45753 (three `HtmlSanitizer` bypasses). Full list: [symfony.com/blog/symfony-7-4-12-released](https://symfony.com/blog/symfony-7-4-12-released).
- **Twig 3.24.0 → 3.26.0** — 4 CVEs fixed, all sandbox bypasses: CVE-2026-46635 (property allowlist bypass via `column` filter), CVE-2026-46638 (incomplete fix for CVE-2024-45411 — cached template skips `checkSecurity()`), CVE-2026-24425 (source policy bypass), CVE-2026-47732 (multiple `__toString()` bypasses via string coercion).

## [1.1.2] - 2026-05-04

### Added

- **`StructuredDataBuilder` service** (`src/Service/StructuredDataBuilder.php` + interface) — fluent builder for Schema.org JSON-LD (`webSite`, `webPage`, `blogPosting`, `collectionPage`, `contactPage`, `person`). Inline `\|json_encode\|raw` JSON-LD blocks across core and demo templates were replaced with `{{ json_ld(structured_data().<type>(...)) }}`.
- **`DefaultLocaleRedirectListener`** — issues a 301 from `/<default-locale>/...` to the canonical unprefixed URL (e.g. `/en/blog/` → `/blog/` when `en` is the default locale). Runs before `RouterListener` so requests don't 404 first. Refuses to redirect protocol-relative shapes (`/<default>//evil.com`).
- **New Twig extensions** for previously inlined template logic — `BreadcrumbExtension` (`breadcrumbs()`), `SidebarExtension` (`sidebar_data()`), `BlogFilterExtension` (`blog_filter_title()`), `OgImageExtension` (`og_image_url()`), `PostBadgeExtension` (`post_badge()`). Each is `final readonly` with a focused unit test.
- **`structured_data` blocks** on bare core templates `templates/page/contact.html.twig`, `templates/page/default.html.twig`, `templates/page/projects.html.twig` — bare deploys now emit Schema.org markup matching the demo theme.
- **`ContentTree::directoryKeyMap`** — O(1) `findByDirectoryKey()` lookups, replacing the prior linear scan.
- **`ArchiveYearData` value object** for archive-year aggregates returned to templates.
- **AI-agent skills**: `.claude/skills/switch-theme/`, `.claude/skills/write-content/`.

### Changed

- **Inline JSON-LD removed from all core and demo templates** — replaced with the `json_ld()` / `structured_data()` helpers introduced above. Affects `templates/base.html.twig`, `templates/blog/list.html.twig`, `templates/blog/post.html.twig`, `templates/page/about.html.twig`, `templates/feed/sitemap.xml.twig`, and the corresponding `docs/demo/templates/` files.
- **`directoryKey` rename** — internal abbreviation `tk` renamed to `directoryKey` across `src/`, core templates, and demo templates per the no-abbreviations naming rule. Custom templates referring to `tk` as a Twig local should rename to `directoryKey`.
- **Customisation examples migrated** — `docs/customization/custom-footer/` and `docs/customization/self-hosted-fonts/` now use `json_ld(structured_data().webSite(...))` instead of inline JSON encoding. `docs/customization/old-template/` is intentionally **not** migrated (see Deprecated).
- **Demo `about.html.twig` Person.url** corrected from `site_base_url` (site root) to `site_base_url ~ content_url('about', locale)` (the about page itself).
- **`StructuredDataExtension`** uses `JSON_THROW_ON_ERROR` so encoding failures surface as exceptions instead of silently shipping `<script type="application/ld+json">false</script>`.
- **Test suite decoupled from `local/`** — integration tests no longer load instance-specific templates from `local/templates/`, removing flakiness when `local/` is empty or seeded with a different theme.
- `symfony/*` dependencies updated from v7.4.8 to v7.4.9 (filesystem, event-dispatcher, console, var-exporter, cache, dependency-injection, config, dotenv, type-info, form, mime, routing, framework-bundle, monolog-bridge, validator, web-profiler-bundle, http-client, asset-mapper). `phpstan/phpstan` 2.1.51 → 2.1.54. `phpunit/phpunit` 13.1.7 → 13.1.8.

### Deprecated

- **`docs/customization/old-template/`** is deprecated and will be removed in **1.2.0**. The package was a one-shot 1.0→1.1 transition aid for installs that wanted to keep the pre-redesign look. New customisation work should branch from `docs/customization/custom-footer/` or `docs/customization/self-hosted-fonts/` instead — those examples track current core conventions.

### Fixed

- **Open-redirect hardening in `DefaultLocaleRedirectListener`** — `Request::getPathInfo()` does not collapse repeated slashes, so `/<default-locale>//evil.com` would otherwise strip to a `Location: //evil.com` header (browser-cross-origin protocol-relative redirect). The listener now refuses to issue such redirects.
- **XSS in search results** — `assets/search.js` interpolated the Pagefind `excerpt` into innerHTML without escaping. Now uses the existing `esc()` helper, matching the demo build (which already had this fix). Loses Pagefind's `<mark>` highlight tags as a deliberate trade-off.

## [1.1.1] - 2026-04-26

### Added

- **Frontmatter-driven navigation labels** — nav labels for content pages now come from `menu.label` frontmatter (with `title` as fallback) instead of translation keys. New `content_item(directoryKey, locale)` Twig function returns a `ContentItem` for any page; `ContentItem::menuLabel()` reads `menu.label` or falls back to `title()`. Eliminates the need to maintain nav label translations in every locale file.

### Changed

- `symfony/polyfill-*` dependencies updated from v1.36.0 to v1.37.0.
- Polish (PL), German (DE), and French (FR) demo content reviewed and improved across all pages and blog posts.
- Translation style guides added to `.claude/skills/translate-content/SKILL.md` for PL, DE, and FR locales.

### Removed

- **Navigation label translation keys** removed from core, demo, and holas.pl translations — `nav.home`, `nav.blog`, `nav.about`, `nav.contact`, `nav.privacy_policy` (core); `site.releases`, `site.about`, `site.manual`, `site.architecture`, `site.customization`, `site.locales`, `site.design_reference`, `site.contact` (demo). Labels now come from page frontmatter. Custom templates referencing these keys must switch to `content_item('key', locale).menuLabel()`.

### Fixed

- **`./notACMS deploy --prod` no longer overwrites existing `local/` content.** Previously, every deploy would back up `local/` and replace it with `docs/demo/`, destroying customisations. Now deploy only seeds `local/` when it is missing or empty — matching the `ddev build` behaviour. Pass `--bare` or `--demo` explicitly to force a re-seed.

## [1.1.0] - 2026-04-24

> **Upgrading from 1.0.0?** See [UPGRADE-1.1.md](UPGRADE-1.1.md) for breaking changes and migration steps. A drop-in compatibility package is available at `docs/customization/old-template/` to keep the 1.0.0 look.

### Added

- **Bare wireframe theme as new core** — system fonts, light mode only, minimal CSS, every feature works without any `local/` override. Lives under `templates/`, `assets/`, `translations/`.
- **Demo amber-phosphor theme** — full design with dark mode, search overlay, docs sidebar, theme toggle, typography tokens. Shipped as a seed at `docs/demo/` (templates, SCSS, JS, content, translations, nginx, docs).
- **Bare seed content** at `docs/bare/content/` with minimal pages, blog posts, tags, and routes for the two core locales.
- **`./notACMS deploy --bare` / `--demo` flags** — choose which theme to seed on a fresh deploy. Default is `--demo`.
- **`ddev build --bare` / `--demo` flags** — same choice for the DDEV build workflow. Default auto-seeds `docs/demo/` when `local/` is empty.
- **`./notACMS rebuild --bare` / `--demo` flags** — same choice for in-place rebuilds.
- **Old-template compatibility package** at `docs/customization/old-template/` — copies the complete pre-1.1.0 look (content, templates, assets, translations) into `local/` via one `cp -r` command. Replaces the previous `docs/examples/` tree.
- **Template override system documentation** — `local/` is now the sole surface for customization; core never needs editing.
- **`src/Twig/LangSwitcherExtension.php`** — `lang_switch_urls()` Twig function for the language switcher, resolving translation maps via content items.
- **`src/Content/Enum/FilterType.php`** — type-safe enum for archive/tag/category filter URLs.
- **New SCSS partials** in core: `_tokens.scss` (CSS custom properties and dark mode), `_prose.scss` (`.prose` / `.prose--post` rendered-Markdown styles), `_utilities.scss` (layout utilities).
- **Reading time display** on blog posts, releases, and documentation pages — shows estimated minutes based on word count (e.g. "4 min read"). New translation key `blog.reading_time`.
- **Reading progress indicator** — amber horizontal bar at the top of post / doc pages that fills as you scroll.
- **New translation keys**: `nav.main` (ARIA), `nav.prev`, `nav.next`, `sidebar.search`.
- **New configuration**: `config/packages/http_client.yaml`, `config/packages/test/cache.yaml`, `config/services_test.yaml`.
- **Test suite scaffolding**: `tests/Unit/`, `tests/Integration/`, `tests/Fixtures/` with initial coverage.
- **AI-agent skills** under `.claude/skills/`: `add-locale`, `doc-alignment`, `site-sweep`, `translate-content`, `upgrade-guide`.

### Changed

- **Core templates replaced with bare wireframe versions** (**breaking** if you extend core templates — see [UPGRADE-1.1.md](UPGRADE-1.1.md#core-template-redesign)). Every file under `templates/` was rewritten: base layout, blog list/post, page variants (home, about, contact, projects, default, error, coming-soon, styleguide), component partials.
- **Core SCSS reorganised** — `assets/styles/` now uses CSS custom properties for colour tokens. Compile-time variables (`$font`, `$fs-base`, `$lh`, spacing, breakpoints) are unchanged.
- **SCSS local entrypoint renamed**: `local/assets/styles/local.scss` → `local/assets/styles/app_local.scss` (**breaking** — rename the file and update the import in `local/assets/app.js`).
- **Core colour SCSS variables replaced with CSS custom properties** (**breaking** if `local/assets/styles/` references `$color-body`, `$color-body-bg`, `$color-link`, `$color-link-hover`, `$color-dark`, `$color-border`, `$color-muted` — see mapping in UPGRADE).
- **Core `translations/messages.*.yaml` stripped** to keys used by the bare templates (demo-specific strings moved to `docs/demo/translations/`).
- **`assets/app.js` simplified** to a bare entrypoint; the demo theme adds its own JS on top at `docs/demo/assets/` (search overlay, docs sidebar, docs TOC, theme toggle, language dropdown).
- **Controllers and services refactored**: `BlogController`, `PageController`, `ContactController`, `SiteConfigService` (+ `SiteConfigServiceInterface`), `ContentItem`, `ContentTree`, `BuildStaticSiteCommand`, `Preview\SessionToggleService`, `SiteConfigExtension`.
- **`ContactType` form labels** now pass translation keys directly; Symfony's form layer resolves them (non-breaking internal refactor).
- **Post excerpts strip heading anchors** before rendering — no more `#` characters leaking into listing excerpts.
- **DDEV test command moved**: `.ddev/commands/web/test` → `.ddev/commands/host/test` (run from host; PHP runtime optional since 1.0.0's final commit).
- **Build pipeline** (`.ddev/commands/web/build`): added `--bare` / `--demo` flags, auto-seed fallback when `local/` is empty, ensures `local/assets/images/og-default.jpg` before compile.
- **Deploy / rebuild scripts** updated with theme selection and consistent help output.
- **README.md, AGENTS.md, CLAUDE.md** updated to describe the bare/demo split and the `local/` override model.
- **`docs/ARCHITECTURE.md`, `docs/CUSTOMIZATION.md`, `docs/EDITOR_GUIDE.md`, `docs/LOCALES.md`, `docs/STYLEGUIDE.md`, `docs/TESTING.md`, `docs/TESTS.md`** updated for 1.1.0 architecture.

### Removed

- **`docs/examples/` directory** — content moved into `docs/customization/old-template/`; template-override scratch examples (`block-override`, `full-override`, `material-cards`, `starter-extend`, `starter-full-override`, translation overrides, `EDITOR_GUIDE.md`, `STYLEGUIDE.md`, `nginx/*.conf`) dropped as superseded by the bare/demo model.
- **Translation keys** (**breaking** if referenced directly in custom templates):
  - `header.tagline` — tagline removed from header layout
  - `nav.projects` — removed from main navigation
  - `nav.search` — search moved to sidebar (`sidebar.search`)
  - `blog.published_on` — replaced by inline date in meta line
  - `blog.comments_disabled` — comments feature removed from templates

### Fixed

- Excerpts in blog listings no longer contain leaked `#` characters from heading anchor links.
- **Pagefind pinned to `@1.5.0`** in `scripts/rebuild-content.sh` — later Pagefind releases ship a jemalloc-linked ARM64 binary that crashes with `<jemalloc>: Unsupported system page size` on hosts with 16K kernel pages (e.g. Raspberry Pi 5), aborting the deploy build. 1.5.0 still works across 4K and 16K page sizes. Upstream: [Pagefind#1147](https://github.com/Pagefind/pagefind/issues/1147).
- **Nginx template startup failure** (`unexpected "{" at line 20`) — removed a Twig-style `{% if %}` conditional wrapping the `/api/` location block in `docker/nginx.conf.template`. The nginx Docker image uses envsubst, which only substitutes `${VAR}` and leaves `{% ... %}` intact for nginx to choke on. The `/api/` block is always present now; lazy DNS resolution (`resolver 127.0.0.11`) keeps nginx starting cleanly even when the php upstream is absent (`RUNTIME_PHP_ENABLED=false`) — `/api/` simply returns 502 in that case.
- **Traefik router/middleware name collision** when running multiple notACMS stacks behind one Traefik instance — labels in `docker-compose.yaml` were hardcoded to `routers.apache.*` and `middlewares.nonwwwredirect.*`, causing Traefik to silently drop one stack's router when both registered the same names. Labels now derive from `${COMPOSE_PROJECT_NAME}` (new `.env` variable, default `notacms`) so every stack gets a unique router and middleware namespace. Set a distinct `COMPOSE_PROJECT_NAME` per stack in `.env.local`.
- **CSP blocked demo-theme external assets** — the core nginx template's `Content-Security-Policy` is now bare-theme-safe and emitted via a `$csp` variable that overrides in `local/docker/nginx/*.conf` can redefine before it reaches `add_header`. The demo theme ships a `csp.conf` seed that widens the policy for its own external origins (Phosphor Icons on `unpkg.com`, Inter / JetBrains Mono webfonts on `fonts.googleapis.com` + `fonts.gstatic.com`) without touching core. Bare deployments keep the tight default.

## [1.0.0] - 2026-04-09

### Added

- Initial release of notACMS.
