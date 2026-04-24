# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

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
