# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
