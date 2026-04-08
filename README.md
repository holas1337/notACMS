<picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/logo/logo-white.png">
  <source media="(prefers-color-scheme: light)" srcset="docs/logo/logo-dark.png">
  <img alt="notACMS" src="docs/logo/logo-dark.png" height="48">
</picture>

![License: Apache 2.0](https://img.shields.io/badge/license-Apache--2.0-blue.svg)
![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777bb4.svg)
![Symfony 7.4](https://img.shields.io/badge/Symfony-7.4-000000.svg)
![PHPUnit 13](https://img.shields.io/badge/phpunit-13-3698ff.svg)
![Coverage](https://img.shields.io/badge/coverage-80%25-brightgreen.svg)

## Symfony-based, AI-friendly static site generator — Markdown content, multi-language, zero-database

notACMS is a static site generator built on Symfony 7.4. Write content in Markdown with YAML frontmatter, configure your locales and site settings in a single YAML file, and deploy a fully pre-rendered HTML site served by nginx — no database, no runtime PHP (except an optional contact form). Customize templates, styles, and content without touching core files via the `local/` override system.

## What is notACMS?

- **Static by default** — all pages are pre-rendered to HTML at build time; nginx serves them directly
- **Multi-language** — any number of locales, configured in `local/content/_site.yaml`; the demo ships with English and Polish
- **Markdown content** — posts and pages are Markdown files with YAML frontmatter; no admin panel, no database
- **`local/` overrides** — templates, SCSS, translations, nginx config, and content all live in `local/` so you never modify core files

## Quickstart

```bash
git clone https://github.com/holas1337/notACMS my-site
cd my-site
ddev start
ddev build   # bootstraps local/ with demo content, then builds the site
```

Open `https://notacms.ddev.site` to see your site. Edit `local/content/_site.yaml` to configure your domain, locales, and site name.

## Development
**DDEV**
```bash
ddev start
ddev build
```

**Docker Compose (prod-like, local)**
```bash
./notACMS deploy --port 8081        # APP_ENV=dev, override port at runtime
./notACMS deploy --prod --port 8081 # APP_ENV=prod, override port at runtime
```

## Production
```bash
./notACMS deploy --prod              # deploy with APP_ENV=prod, uses NGINX_PORT from .env (default 8123)
./notACMS deploy --prod --port 8081  # override port at runtime
```

Set `NGINX_PORT=80` in `.env.local` to expose on port 80.

## Tech Stack

- **PHP 8.5** + **Symfony 7.4** (minimal, no database)
- **DDEV** for local development
- **Markdown** content with YAML frontmatter — no database
- **Twig** templates
- **Pagefind** for client-side search (WASM, auto language split)
- **Cloudflare Turnstile** captcha on contact form
- **AssetMapper** for frontend assets (no Node.js build step)

## Documentation

| File | Contents |
|---|---|
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Deep-dive into the content pipeline, routing, services, and templates |
| [docs/EDITOR_GUIDE.md](docs/EDITOR_GUIDE.md) | How to write and publish posts and pages (frontmatter, images, drafts, series) |
| [docs/STYLEGUIDE.md](docs/STYLEGUIDE.md) | Design tokens, components, and conventions for the living styleguide |
| [docs/LOCALES.md](docs/LOCALES.md) | How to add, remove, or manage locales — config files, URL routing, translations, nginx |
| [docs/CUSTOMIZATION.md](docs/CUSTOMIZATION.md) | How to override templates, JS, SCSS, and nginx config via the `local/` directory |

## Architecture

```
Request → nginx → static HTML (95%+ of requests, no PHP)
                → Symfony PHP-FPM (POST /api/contact only)
```

Markdown content → Symfony static site generator → pre-rendered HTML served by nginx. The default locale is served at `/`; additional locales at `/{locale}/`. Locale list and site settings are configured in `local/content/_site.yaml`. See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the full pipeline.

## Build & Deploy

```bash
ddev build                  # full build: sass + assets + static HTML + pagefind search index
./notACMS deploy --prod     # production: build Docker image, start services, full build
./notACMS rebuild           # rebuild static HTML + search index (containers already running)
./notACMS --help            # show all commands and options
```

## Code Quality

```bash
ddev test         # run PHPUnit test suite
ddev code-check   # composer validate + audit + PHP CS Fixer (dry-run) + Rector (dry-run) + PHPStan + Twig lint
ddev code-fix     # auto-fix PHP CS Fixer and Rector issues, then re-run code-check
```

## Key Commands

### DDEV — Development

| Command | Description |
|---|---|
| `ddev start` | Start DDEV development environment |
| `ddev build` | Full production build (static HTML + search index) |
| `ddev test` | Run PHPUnit test suite |
| `ddev code-check` | Run code quality checks (CS Fixer, Rector, PHPStan, Twig lint) |
| `ddev code-fix` | Auto-fix PHP code style issues |
| `ddev exec php bin/console sass:build --watch` | SCSS watch mode |

### `./notACMS` — Production / development without DDEV (not recommended)

| Command | Description |
|---|---|
| `./notACMS deploy --prod` | Production deploy: build image, start containers, full build |
| `./notACMS deploy down` | Stop and remove containers |
| `./notACMS rebuild` | Rebuild static HTML + search index (containers already running) |
| `./notACMS --help` | Show all commands and options |

## Requirements

- [DDEV](https://ddev.readthedocs.io/) (provides PHP 8.5, Composer, nginx)
- Node.js/npx (for Pagefind only, at build time)

## Configuration

`URL=example.site` in `.env` is used by Docker Compose and Certbot for the production nginx/TLS setup. It is the bare-domain equivalent of `base_url: "https://example.com"` in `local/content/_site.yaml`. PHP code reads `base_url` from `_site.yaml` via `SiteConfigService`; the `URL` env var is only for the container orchestration layer. Both values must be kept in sync when the domain changes.

## AI / MCP Servers

notACMS is designed to be AI-friendly — content is Markdown with structured YAML frontmatter, all configuration lives in plain text files, and the `local/` override system means an AI agent can customize the site without touching core files.

Two optional MCP servers are used for AI-assisted content workflows (image generation).

### Draw Things (local image generation)

Requires [Draw Things](https://drawthings.ai/) running locally with the API server enabled (Settings → API Server).

**Claude Code:**
```bash
claude mcp add draw-things \
  --env DRAWTHINGS_HOST=YOUR_DRAW_THINGS_IP \
  --env DRAWTHINGS_PORT=7860 \
  -- npx -y mcp-drawthings
```

**OpenCode** — add to `~/.opencode/opencode.json`:
```json
{
  "$schema": "https://opencode.ai/config.json",
  "mcp": {
    "draw-things": {
      "type": "local",
      "command": ["npx", "-y", "mcp-drawthings"],
      "environment": {
        "DRAWTHINGS_HOST": "YOUR_DRAW_THINGS_IP",
        "DRAWTHINGS_PORT": "7860"
      },
      "timeout": 600000,
      "enabled": true
    }
  }
}
```

**Important:** Klein 9B image generation takes time — `timeout: 600000` (600 seconds) is required. Also, **always pass all parameters explicitly** (`model`, `width`, `height`, `steps`, `seed`, `cfg_scale`) when calling the MCP tool, even if they match current Draw Things settings.

See `local/docs/EDITOR_GUIDE.md` → "AI image generation" for model settings and batch workflow.

### Hugging Face (cloud image generation fallback)

**Claude Code:**
```bash
claude mcp add --transport http hf-mcp-server https://huggingface.co/mcp \
  --header "Authorization: Bearer YOUR_HF_TOKEN"
```

**OpenCode** — add to `~/.opencode/opencode.json`:
```json
{
  "hf-mcp-server": {
    "type": "remote",
    "url": "https://huggingface.co/mcp",
    "headers": {
      "Authorization": "Bearer YOUR_HF_TOKEN"
    },
    "enabled": true
  }
}
```

Get your token at [huggingface.co/settings/tokens](https://huggingface.co/settings/tokens). Used as a fallback when Draw Things is unavailable.

## License

Apache 2.0 — see [LICENSE](LICENSE).

## Third-Party Licenses

This project uses open-source software. All dependency licenses are available
in `vendor/*/LICENSE` after running `composer install`.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup, code standards, and the PR process. For site customizations, use the `local/` override system — no core files need editing. See [docs/CUSTOMIZATION.md](docs/CUSTOMIZATION.md).

## Contact

For the love of static sites by [holas](https://holas.pl).
