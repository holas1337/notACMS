---
title: "Manual"
description: "Complete guide to installing, configuring, and deploying notACMS."
template: page/doc
slug: manual
menu:
  weight: 20
  label: "Manual"
---

> ## Quick Start
> 
> ```bash
> # 1. Clone
> git clone https://github.com/holas1337/notACMS my-site && cd my-site
> 
> # 2. Configure production
> echo "APP_SECRET=$(php -r 'echo bin2hex(random_bytes(32));')" >> .env.local
> echo "URL=yourdomain.com" >> .env.local
> 
> # 3. Deploy
> ./notACMS deploy --prod
> ```
> 
> Site runs at `https://yourdomain.com`. Edit content in `local/content/`.

---

## Installation (Development)

notACMS uses [DDEV](https://ddev.readthedocs.io/) for local development.

**Prerequisites:** Docker Desktop (or Orbstack on Mac), DDEV 1.22+, Git.

```bash
git clone https://github.com/holas1337/notACMS my-site
cd my-site
ddev start
ddev composer install
ddev build
```

Site runs at `https://my-site.ddev.site`.

---

## Content Structure

```
local/content/
├── _site.yaml          # Site config
├── _routes.yaml        # URL overrides
├── _tags.yaml          # Tag translations
├── pages/              # Static pages
│   ├── home/
│   │   ├── en.md
│   │   ├── de.md
│   │   └── pl.md
│   └── about/
│       ├── en.md
│       └── pl.md
└── blog/
    ├── _index_en.md
    └── releases/
        └── v1-0-0/
            ├── en.md
            └── pl.md
```

---

## Page Frontmatter

| Field | Type | Required | Description |
|---|---|---|---|
| `title` | string | Yes | Page title |
| `slug` | string | Yes | URL path |
| `description` | string | No | Meta description |
| `date` | date | Posts | Publication date |
| `updated` | date | No | Last modified date |
| `category` | string | Posts | Category slug |
| `tags` | list | No | Tag list |
| `template` | string | No | Twig template |
| `image` | string | No | Featured image path |
| `image_alt` | string | Required with `image` | Image alt text (defaults to title if omitted) |
| `draft` | bool | No | Hide from production |
| `pinned` | date or false | No | Pin to the top of listings until this date (inclusive); auto-unpins once the date passes. Posts only. |
| `featured` | bool | No | Projects only, show in curated grid |
| `dynamic` | bool | No | Skip static pre-rendering |
| `series` | string | No | Series key |
| `series_order` | int | No | Position within series |
| `toc` | bool | No | Show table of contents |
| `related` | list | No | Manual related post slugs |
| `menu` | object | No | Nav config: `weight` (sort order) and `label` (override) |

> **Machine-readable schemas:** [`post.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/post.frontmatter.schema.json), [`page.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/page.frontmatter.schema.json), [`category.frontmatter.schema.json`](https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/category.frontmatter.schema.json)

---

## Images

Place images in a `files/` subdirectory alongside content:

```
blog/releases/my-post/
├── en.md
└── files/
    └── featured.webp
```

Reference in frontmatter: `image: /media/my-post/featured.webp`

> **Tip:** All images must be WebP. Use ImageMagick: `ddev exec convert input.jpg -quality 82 -strip -define webp:method=6 output.webp`

---

## Build Commands

| Command | Purpose |
|---------|---------|
| `ddev build` | Full dev build: cache + assets + pages + search |
| `ddev code-check` | PHPStan + PHP CS Fixer |
| `ddev code-fix` | Auto-fix code style |
| `./notACMS deploy --prod` | Production deploy (Docker + static build) |
| `./notACMS rebuild --prod` | Content update only (fast, no Docker rebuild) |

---

## Production Deployment

### Prerequisites

- Docker + Docker Compose
- Domain with DNS A-record pointing to server
- Ports 80/443 available (or custom via `NGINX_PORT`)

### First Deploy

```bash
./notACMS deploy --prod
```

What happens:
1. Seeds `local/` from `docs/demo/` (only if missing or empty — existing content is never touched)
2. Builds PHP Docker image
3. Installs Composer deps (`--no-dev` in prod)
4. Compiles SCSS + assets
5. Generates static HTML → `public/static/`
6. Builds Pagefind search index → `public/pagefind/`
7. Starts nginx container

### Content Updates

After editing Markdown files:

```bash
./notACMS rebuild --prod
```

Skips Docker rebuild — just regenerates HTML + search index (~10s vs ~2-3min).

### Container Commands

| Command | Purpose |
|---------|---------|
| `./notACMS deploy up` | Start containers (no rebuild) |
| `./notACMS deploy down` | Stop containers |
| `./notACMS deploy restart` | Restart without rebuild |

---

## Environment Variables

Create `.env.local` (never committed, gitignored):

| Variable | Required | Set via | Purpose |
|----------|----------|---------|---------|
| `APP_SECRET` | Yes | `php -r "echo bin2hex(random_bytes(32));"` | Symfony secret |
| `URL` | Yes | `yourdomain.com` | Must match `base_url` in `_site.yaml` |
| `NGINX_PORT` | No | `8123` | Host port (default: 8123) |
| `RUNTIME_PHP_ENABLED` | No | `true`/`false` | Enable PHP for contact form |
| `MAILER_DSN` | No* | `smtp://user:pass@host:587` | *Required if contact form enabled |
| `TURNSTILE_SITE_KEY` | No | Cloudflare key | CAPTCHA (optional) |
| `TURNSTILE_SECRET_KEY` | No | Cloudflare secret | CAPTCHA (optional) |
| `CERTRESOLVER` | No | `le` or `dummy` | SSL resolver (default: `le`) |
| `UID` / `GID` | No | `1000` | Match host user (`id`) |

---

## Troubleshooting

### Port Already in Use

**Error:** `bind: address already in use`  
**Fix:**
```bash
echo "NGINX_PORT=8080" >> .env.local
./notACMS deploy --prod
```

### Permission Denied

**Error:** 403 on static files  
**Fix:**
```bash
echo "UID=$(id -u)" >> .env.local
echo "GID=$(id -g)" >> .env.local
./notACMS deploy restart
```

### Contact Form 404

**Fix:** Enable PHP runtime:
```bash
echo "RUNTIME_PHP_ENABLED=true" >> .env.local
./notACMS deploy restart
```

### SSL Certificate Fails

**Error:** Traefik can't get certificate  
**Fix:** Check ports 80/443 open, DNS resolves. Or use `CERTRESOLVER=dummy` for testing.

### Styles Not Updating

**Fix:** Clear cache and rebuild:
```bash
rm -rf var/ public/assets/
./notACMS deploy --prod
```

### Build Hangs

**Fix:** Ensure Docker has sufficient memory (4GB+) for asset compilation.
