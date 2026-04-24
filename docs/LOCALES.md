# Managing Locales

This guide covers how the locale system works and how to add a new language to the site.

---

## How it works

The locale list is defined in `local/content/_site.yaml` under `site.locales`. The **first key is the default locale** (currently `en`). All locale-aware behavior — routing, URL generation, templates, JavaScript — derives from this config via `SiteConfigServiceInterface`.

### Config files

| File | What it controls |
|---|---|
| `local/content/_site.yaml` | Locale list and per-locale metadata (label, og_locale, date_format, font_preload) |
| `local/content/_routes.yaml` | Translated URL path segments for structural routes (listings, archive, search, etc.) |
| `local/content/_tags.yaml` | Tag slug translations between locales |
| `translations/messages.{locale}.yaml` | Core UI strings (nav labels, form labels, section headings, error messages) — generic, work for any site persona |
| `local/translations/messages.{locale}.yaml` | Demo/persona-specific strings (e.g. `about.role`, `about.headline`, `about.skills.*`) — seeded from `docs/demo/translations/` on first bootstrap |
| `config/packages/framework.yaml` | `default_locale` for Symfony internals (translator fallback) |
| `config/packages/translation.yaml` | Translator paths: core `translations/` + `local/translations/` (merged on top) |
| `local/docker/nginx/error-pages.conf` | Locale-specific error pages — add an `if` block per non-default locale |
| `local/docker/nginx/redirects.conf` | SEO redirects and legacy URL mappings — add locale-specific entries as needed |

### URL patterns

- **Default locale** (EN): no prefix — `/`, `/blog/`, `/contact/`
- **Other locales**: `/{locale}/` prefix — `/pl/`, `/pl/wpisy/`, `/pl/kontakt/`
- Translated path segments are defined in `local/content/_routes.yaml`. Routes not listed there get auto-prefixed: e.g. `/feed/` → `/pl/feed/`

### Two URL resolution patterns in templates

| Type | Pattern | Example |
|---|---|---|
| Structural routes (listings, search, contact, etc.) | `path('route_name_' ~ locale)` | `path('blog_list_' ~ locale)` |
| Content pages/posts (about, privacy, blog posts) | `content_url(directoryKey, locale)` | `content_url('about', locale)` |

---

## Adding a new locale

Example: adding German (`de`).

### 1. `local/content/_site.yaml` — register the locale

Add a new key under `site.locales`. **Order matters** — the first key remains the default.

```yaml
site:
  locales:
    en:
      label: "English"
      og_locale: en_US
      date_format: "M d, Y"
      tagline: "$ whoami"
      tagline_commands: [...]
    pl:
      label: "Polski"
      og_locale: pl_PL
      font_preload: fonts/inter-normal-latin-ext.woff2
      date_format: "d.m.Y"
      tagline: "$ whoami"
      tagline_commands: [...]
    de:                              # ← new locale
      label: "Deutsch"
      og_locale: de_DE
      date_format: "d.m.Y"
      tagline: "$ whoami"
      tagline_commands: [...]
```

Required fields: `label`, `og_locale`, `date_format`.
Optional: `font_preload` (path to a font file that needs preloading for this locale's character set), `tagline`, `tagline_commands`.

### 2. `local/content/_routes.yaml` — add translated URL paths

Add entries for any structural route that needs a German slug. Routes without an entry auto-prefix with `/de/`.

```yaml
routes:
  blog_list:
    pl: /wpisy/
    de: /beitraege/              # ← new
  blog_list_paginated:
    pl: /wpisy/strona/{page}/
    de: /beitraege/seite/{page}/ # ← new
  blog_category:
    pl: /wpisy/{category}/
    de: /beitraege/{category}/   # ← new
  blog_tag:
    pl: /wpisy/tag/{tag}/
    de: /beitraege/tag/{tag}/    # ← new
  blog_archive:
    pl: /archiwum/{year}/{month}/
    de: /archiv/{year}/{month}/  # ← new
  blog_archive_year:
    pl: /archiwum/{year}/
    de: /archiv/{year}/          # ← new
  contact:
    pl: /kontakt/
    de: /kontakt/                # ← new (same as PL in this case)
  projects:
    pl: /realizacje/
    de: /projekte/               # ← new
  search:
    pl: /szukaj/
    de: /suche/                  # ← new
```

Routes you can skip (auto-prefix with `/de/`): `rss`, `api_contact`, `error_404`, `error_500`, `static_page`, `home`. Note that `blog_tag` is overridden in the shipped demo (`/pl/wpisy/tag/{tag}/`, `/de/beitraege/tag/{tag}/`) so its path nests under the translated `blog_list` segment — add a `de:` entry if you want the same nesting for your new locale.

### 3. `local/content/` — create content files

Create `de.md` files alongside existing `en.md` and `pl.md`:

```
local/content/pages/about/
    en.md
    pl.md
    de.md     ← new

local/content/blog/tutorials/my-post/
    en.md
    pl.md
    de.md     ← new
```

Each `de.md` needs frontmatter with a German `slug` and `category`:

```yaml
---
title: "Über mich"
slug: "ueber-mich"
description: "..."
template: page/about
menu:
  weight: 40
  label: "Über mich"
---
```

Not every page/post needs a German version. The site only shows content that exists in each locale. The language switcher falls back to the other locale's homepage if no translation exists.

### 4. `translations/messages.de.yaml` — UI strings

Create the translation file with all keys from `messages.en.yaml`:

```bash
cp translations/messages.en.yaml translations/messages.de.yaml
# Then translate all values
```

### 5. `local/content/_tags.yaml` — tag translations

Add German equivalents for tags that differ from the English canonical slug:

```yaml
security:
  pl: bezpieczenstwo
  de: sicherheit          # ← new
hardware:
  pl: sprzet
  de: hardware             # ← same as EN, can omit
```

Tags identical to the canonical (EN) slug don't need entries.

### 6. `config/packages/translation.yaml` — translator path

No changes needed. The file sets `local/translations/` as `default_path` (overrides take priority) with core `translations/` as an extra path (fallback):

```yaml
framework:
    default_locale: en
    translator:
        default_path: '%kernel.project_dir%/local/translations'
        paths:
            - '%kernel.project_dir%/translations'
```

The new `translations/messages.de.yaml` is picked up automatically. If you add demo-specific strings for the new locale (e.g. `about.role` in German), put them in `docs/demo/translations/messages.de.yaml` — they will be seeded to `local/translations/` on first bootstrap.

### 7. `local/docker/nginx/` — production nginx

The core `docker/nginx.conf.template` handles the generic cases automatically (API routes match any `/{locale}/api/` pattern). Locale-specific configuration lives in `local/docker/nginx/`, which is gitignored and seeded on first deploy.

**Error pages** — edit `local/docker/nginx/error-pages.conf` and add an `if` block for the new locale:
```nginx
location @error_404 {
    root /app/public/static;
    if ($request_uri ~ "^/pl/") { rewrite ^ /pl/404.html break; }
    if ($request_uri ~ "^/de/") { rewrite ^ /de/404.html break; }
    rewrite ^ /404.html break;
}

location @error_5xx {
    root /app/public/static;
    if ($request_uri ~ "^/pl/") { rewrite ^ /pl/500.html break; }
    if ($request_uri ~ "^/de/") { rewrite ^ /de/500.html break; }
    rewrite ^ /500.html break;
}
```

**SEO redirects** — add any German legacy URL redirects to `local/docker/nginx/redirects.conf` if needed.

**Catch-all dated URLs** — add pattern for `/de/YYYY/MM/DD/slug/` in `local/docker/nginx/redirects.conf` if applicable.

### 8. No PHP or Twig changes required

All PHP services, controllers, templates, and JavaScript read locale configuration dynamically. No code changes are needed to add a new locale.

---

## Removing a locale

1. Remove the key from `local/content/_site.yaml` → `site.locales`
2. Remove its entries from `local/content/_routes.yaml`
3. Remove its entries from `local/content/_tags.yaml`
4. Delete `translations/messages.{locale}.yaml`
5. Optionally delete `{locale}.md` content files (they'll be ignored if the locale isn't configured)
6. Remove locale-specific `if` blocks from `local/docker/nginx/error-pages.conf` and any redirects from `local/docker/nginx/redirects.conf`

---

## Changing the default locale

The default locale is the **first key** in `site.locales`. To change it:

1. Reorder keys in `local/content/_site.yaml` (new default first)
2. Update `config/packages/framework.yaml` → `default_locale` to match
3. Update `local/content/_routes.yaml` — the new default locale's routes lose the prefix; other locales gain it
4. Update content slugs — the default locale's content uses unprefixed URLs
5. Update `local/docker/nginx/error-pages.conf` — remove the old default-locale `if` blocks and update `local/docker/nginx/redirects.conf` as needed
6. Rebuild: `ddev build`

This is a significant change — test thoroughly.

---

## Key architecture details

- `SiteConfigServiceInterface` (`src/Service/SiteConfigService.php`) — single authority for locale config. Injected by all services, controllers, listeners, and the route loader.
- `LocalizedRouteLoader` (`src/Routing/LocalizedRouteLoader.php`) — generates `{name}_{locale}` routes from `#[LocalizedRoute]` attributes + `local/content/_routes.yaml` overrides.
- `content_url(directoryKey, locale)` — Twig function that looks up content pages by directory key. Used for linking to about, privacy, etc. without hardcoded slugs.
- `TagTranslationService::translate()` — translates tags between locales using `local/content/_tags.yaml`.
- JavaScript reads locale config from `data-*` attributes on `<html>` (set by `base.html.twig`): `data-locales`, `data-default-locale`, and translated UI labels.
- nginx locale-dependent config lives in `local/docker/nginx/` — `error-pages.conf` for error pages, `redirects.conf` for SEO redirects. Core `docker/nginx.conf.template` is locale-agnostic.
