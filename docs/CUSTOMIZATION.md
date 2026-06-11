# Customisation Guide

notACMS is designed to be customised without touching core files. Everything goes
in a `local/` directory at the project root — it is gitignored, so your changes
are never overwritten by upstream updates.

## Bare vs demo

notACMS ships with **two themes** you can pick from on first build:

| Flag | Seeds `local/` from | Result |
|---|---|---|
| `--demo` *(default)* | `docs/demo/` (full: templates, SCSS, JS, content, translations, nginx, docs) | The amber-phosphor theme from notacms.holas.pl — dark mode, search overlay, docs sidebar, theme toggle. |
| `--bare` | `docs/bare/content/` (content only) | Minimal starter content; the **bare core** (`templates/`, `assets/`, `translations/`) renders the site with system fonts and ~200 lines of CSS. |

```bash
ddev build --demo    # or: ./notACMS deploy --demo  (default)
ddev build --bare    # or: ./notACMS deploy --bare
```

Flags are mutually exclusive and only apply to the first build (when `local/` is empty). After that, `local/` is your source of truth; re-pass the flag to force a reseed.

Both themes are fully functional. Pick `--demo` to start from a polished design and delete what you don't want; pick `--bare` to build your own look on top of a minimal baseline. The rest of this guide shows how to layer customisations on whichever you chose.


```
local/
├── content/                    # site content (auto-seeded on first build)
│   ├── _site.yaml              # site name, locales, author, contact form
│   ├── _routes.yaml            # translated URL path segments per locale
│   ├── _tags.yaml              # tag slug translations between locales
│   ├── blog/
│   │   └── demo/               # example category
│   └── pages/
│       ├── about/
│       ├── contact/
│       ├── home/
│       ├── privacy-policy/
│       └── projects/
├── templates/                  # override any Twig template
├── translations/               # override or add UI strings
│   ├── messages.en.yaml
│   └── messages.pl.yaml
├── assets/                     # JS, SCSS, image overrides
│   ├── app.js                  # local JS entrypoint (app-local)
│   └── styles/
│       └── app_local.scss      # override / extend SCSS
├── docker/
│   └── nginx/                  # nginx config snippets (auto-seeded on first deploy)
│       ├── redirects.conf       # site-specific redirects (SEO, legacy URLs)
│       ├── error-pages.conf    # locale-aware error pages
│       └── csp.conf             # Content-Security-Policy override (demo-only seed)
├── docs/
│   ├── EDITOR_GUIDE.md         # site-specific writing guide (auto-seeded on first deploy)
│   └── STYLEGUIDE.md           # site-specific design reference (auto-seeded on first deploy)
└── src/                        # PHP extensions (NotACms\Local\ namespace)
```

---

## Template overrides

### Replace a template entirely

Create the same relative path under `local/templates/`. The file is used as-is;
no `extends` required.

```twig
{# local/templates/components/navigation.html.twig #}
<nav class="my-nav">
    <a href="/">Home</a>
    <a href="{{ path('blog_list_' ~ locale) }}">Blog</a>
    <a href="{{ path('contact_' ~ locale) }}">Contact</a>
</nav>
```

Any template you do **not** create in `local/templates/` continues to use the
original from `templates/`.

### Override a single block

Use the `@base` namespace to extend the **original** template (not your local
copy) and override only the blocks you need.

```twig
{# local/templates/blog/post.html.twig #}
{% extends '@base/blog/post.html.twig' %}

{% block title %}{{ block('title', '@base/blog/post.html.twig') }} — My Site{% endblock %}

{% block sidebar_bottom %}
    <div class="my-widget">…</div>
    {{ block('sidebar_bottom', '@base/blog/post.html.twig') }}
{% endblock %}
```

Available blocks are defined in `templates/base.html.twig`:
`title`, `meta_description`, `robots`, `stylesheets`, `structured_data`,
`navigation`, `body`, `javascripts`, and more.

### Structured data (JSON-LD)

The `structured_data` block emits JSON-LD metadata for search engines. Two Twig
functions make it clean and composable:

- **`json_ld(array)`** — encodes a PHP array as JSON and wraps it in a
  `<script type="application/ld+json">` tag. Pass it the result of any builder
  method.
- **`structured_data()`** — returns the `StructuredDataBuilderInterface`
  instance, letting you chain schema-typed builder methods directly in Twig.

**Built-in schema types:**

| Builder method | Schema.org type | Used on |
|---|---|---|
| `structured_data().webSite(...)` | WebSite | base.html.twig |
| `structured_data().person(...)` | Person | about page, blog post author |
| `structured_data().blogPosting(...)` | BlogPosting | blog post pages |
| `structured_data().collectionPage(...)` | CollectionPage | blog listing pages |
| `structured_data().breadcrumbList(...)` | BreadcrumbList | breadcrumb component |
| `structured_data().contactPage(...)` | ContactPage | contact page |
| `structured_data().webPage(...)` | WebPage | generic pages |
| `structured_data().organization(...)` | Organization | sub-structure for publisher |
| `structured_data().imageObject(...)` | ImageObject | sub-structure for post images |

To customize the structured data for your site, override the
`{% block structured_data %}` block in `local/templates/base.html.twig`:

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block structured_data %}
{{ json_ld(structured_data().webSite(
    site_name,
    site_base_url,
    structured_data().person(site_author.name, site_base_url),
    {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": site_base_url ~ path('search_' ~ locale) ~ '?q={search_term_string}'
        },
        "query-input": "required name=search_term_string"
    }
)) }}
{% endblock %}
```

The builder automatically strips empty/null values — if `site_author.email` is
not set, the `email` key will be absent from the output rather than appearing as
empty.

### Layout helpers

A handful of pure Twig functions encapsulate logic that templates would
otherwise inline. The components shipped with core (`breadcrumb.html.twig`,
`sidebar.html.twig`, `post_card.html.twig`, …) call them; if you write custom
templates that need the same data, call them yourself.

| Function | Returns | What it does |
|---|---|---|
| `breadcrumbs(content, locale, options = {})` | array of `{label, url}` | Builds the breadcrumb trail. Pass `null` for the blog list, a `ContentItem` for any other page. Options: `home_label`, `filter_type`, `filter_value`, `archive_date`. |
| `sidebar_data(locale)` | `SidebarData` VO | Recent posts, categories, tags, archive years for the locale. |
| `blog_filter_title(filterType, filterValue, archiveYear, archiveMonth, locale)` | string | Heading shown on a filtered blog list — category name, `#tag`, formatted archive date, or the localised "Blog" fallback. |
| `og_image_url(content, siteBaseUrl)` | string | Absolute URL of the page's featured image, or the site default. |
| `post_badge(content, newPostDays)` | `'new'`, `'updated'`, or `null` | Marks recent posts. Threshold from `_site.yaml`'s `new_post_days`. |

```twig
{# local/templates/components/breadcrumb.html.twig — example #}
{% set crumbs = breadcrumbs(content, locale, {home_label: 'My site'}) %}
<nav aria-label="breadcrumb">
    {% for crumb in crumbs %}
        {% if crumb.url %}<a href="{{ crumb.url }}">{{ crumb.label }}</a>{% else %}<span>{{ crumb.label }}</span>{% endif %}
    {% endfor %}
</nav>
```

---

## JavaScript and CSS overrides

The `app-local` importmap entrypoint points to `local/assets/app.js` when that
file exists. To activate it, override the `stylesheets` block in
`local/templates/base.html.twig`:

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block stylesheets %}
    {{ importmap('app') }}
    {{ importmap('app-local') }}
{% endblock %}
```

This guarantees the correct CSS load order: `app.css` (original) is emitted first,
then `local.css` (your overrides).

### JS-only changes (keep all original styles)

```js
// local/assets/app.js
import './my-feature.js';        // your new JS (local/assets/my-feature.js)
```

### Add CSS overrides on top of the original

```js
// local/assets/app.js
import './styles/app_local.scss'; // your overrides (no @import of original needed)
// import './my-feature.js';     // optional extra JS
```

```scss
/* local/assets/styles/app_local.scss */

/* Optional: import original SCSS variables for use in your rules */
@import '../../../../assets/styles/variables';

/* Your overrides — these load after the original CSS */
:root {
    --color-accent: #e85d04;
}

.post-body {
    font-size: 1.0625rem;
    color: var(--accent);    /* CSS custom property from _tokens.scss */
}
```

---

## SCSS overrides

Create `local/assets/styles/app_local.scss`. It is compiled automatically by
`sass:build` alongside the original — no configuration needed.

> **Note:** the local SCSS root file **must be named `app_local.scss`** (matching
> the `app-local` importmap entrypoint), not `app.scss`. `sass-bundle` requires
> all root SCSS files to have unique basenames; both the original and a file
> named `app.scss` would conflict at compile time.

> **Note:** when you provide an `app_local.scss` you must also provide a local
> `app.js` that imports it, and a `local/templates/base.html.twig` that loads
> both entrypoints — otherwise the compiled CSS is never linked in the page.

### Override — keep original styles, add on top

Write only your additions and overrides. The original CSS is loaded separately
by the `app` entrypoint, so there is no double-loading. Optionally import
`_variables.scss` to access SCSS variables like `$fs-lg` or `$sp-3` at compile time.

```scss
/* local/assets/styles/app_local.scss */
@import '../../../../assets/styles/variables';   /* SCSS variables — no CSS rules */

/* your additions / overrides below */
:root {
    --color-accent: #e85d04;
}

.my-component {
    border: 1px solid var(--accent);
}
```

### Replace — completely new styles

```scss
/* local/assets/styles/app_local.scss — written from scratch */
* { box-sizing: border-box; }
body { font-family: sans-serif; background: #fff; color: #111; }
```

For a full replacement, override the `stylesheets` block to load only your
entrypoint and not the original:

```twig
{% block stylesheets %}
    {{ importmap('app-local') }}
{% endblock %}
```

---

## Content

The `local/content/` directory is the live content source for the site. It contains blog
posts, pages, and the three global config files (`_site.yaml`, `_routes.yaml`, `_tags.yaml`).

On the first `ddev build` (or `./notACMS rebuild`), if `local/content/` is empty the build
automatically seeds it from `docs/bare/content/` (minimal placeholder content). After that,
all content changes go in `local/content/`.

An optional richer demo is available in `docs/demo/content/` — you can copy that instead
if you prefer a populated starting point.

```
local/content/
├── _site.yaml          # site name, locales, author, contact form
├── _routes.yaml        # translated URL path segments per locale
├── _tags.yaml          # tag slug translations between locales
├── blog/               # blog posts, co-located by locale
│   └── demo/           # example category — add your own
└── pages/              # static pages
    ├── about/
    ├── contact/
    ├── home/
    ├── privacy-policy/
    └── projects/
```

See `docs/EDITOR_GUIDE.md` for full content authoring reference.

### Site configuration (`_site.yaml`)

Most behaviour values are read from `local/content/_site.yaml` under the `site:` key. You can change them without touching PHP:

| Key | Default | What it controls |
|---|---|---|
| `posts_per_page` | `10` | Posts per listing page |
| `rss_limit` | `20` | Items in the RSS feed |
| `llms_limit` | `5` | Posts listed per locale in `/llms.txt` |
| `recent_posts_limit` | `6` | Posts shown in sidebar "Recent" |
| `related_posts_limit` | `3` | Posts shown in "Related Posts" |
| `new_post_days` | `14` | Age at which `[NEW]` badge appears |
| `coming_soon_reveal_days` | `14` | Days before scheduled post to show teaser |
| `image_variant_widths` | `[640, 960]` | Responsive image sizes generated |
| `image_quality` | `82` | JPEG/WebP quality (`0–100`) |
| `image_magick_flags` | `"-strip"` | Extra flags passed to ImageMagick |

```yaml
# local/content/_site.yaml
site:
  name: "My Site"
  posts_per_page: 5
  image_quality: 90
```

Only override the keys you need — omitted keys keep their interface defaults. If YAML is not enough (runtime conditions, external APIs, computed values), use a PHP decorator (see the `php-service-decorator` example for the Turnstile skip).

---

## Translation overrides

Create `local/translations/messages.en.yaml` and/or `local/translations/messages.pl.yaml`
with only the keys you want to change. They are merged on top of the core translation files,
so any key you omit keeps its original value.

```yaml
# local/translations/messages.en.yaml
nav:
  blog: "Articles"

blog:
  read_more: "Continue reading →"
```

```yaml
# local/translations/messages.pl.yaml
nav:
  blog: "Artykuły"

blog:
  read_more: "Czytaj dalej →"
```

You can override any key from `translations/messages.*.yaml`. Nested keys use the same
indented YAML structure as the original files. You do not need to include both locales —
override only the ones you need.

---

## PHP extensions

Place PHP classes in `local/src/` using the `NotACms\Local\` namespace. They are auto-wired and
auto-configured by Symfony — no additional YAML registration needed.

Supported patterns (all via PHP attributes):

```php
// Event listener
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class MyListener
{
    public function __invoke(MyEvent $event): void { … }
}
```

```php
// Twig filter
use Twig\Attribute\AsTwigFilter;

final class MyExtension
{
    #[AsTwigFilter('my_filter')]
    public function myFilter(string $value): string { … }
}
```

```php
// Service decorator — wrap an existing NotACms\ service
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: SiteConfigServiceInterface::class)]
final class MySiteConfigDecorator implements SiteConfigServiceInterface
{
    public function __construct(private readonly SiteConfigServiceInterface $inner) {}
    // override only the methods you need
}
```

---

## nginx overrides

Any `.conf` file placed in `local/docker/nginx/` is included inside the `server {}` block when the nginx container starts. Use this for site-specific redirects, custom location rules, or extra headers.

On first `./notACMS deploy`, the bootstrap seeds three files from `docs/demo/docker/nginx/`:

- **`redirects.conf`** — example SEO and legacy-URL redirects
- **`error-pages.conf`** — locale-aware error pages (serves `/pl/404.html` for Polish URLs, `/404.html` for everything else; add a block for each non-default locale)
- **`csp.conf`** — widened Content-Security-Policy for the demo theme's external origins (Phosphor Icons on `unpkg.com`, Inter / JetBrains Mono webfonts on `fonts.googleapis.com` / `fonts.gstatic.com`). Bare deploys don't need this file.

```nginx
# local/docker/nginx/redirects.conf — example
location = /old-page/ { return 301 /new-page/; }
location ~ "^\d{4}/\d{2}/\d{2}/([a-z0-9-]+)/$" { return 301 /blog/$1/; }
```

You can split config across multiple files — all `*.conf` files in the directory are included. Files are included in filesystem order, so prefix names with numbers if order matters (e.g. `10-redirects.conf`, `20-cache.conf`).

### Content-Security-Policy override

The core template emits a bare-theme-safe CSP via a `$csp` variable that is set *before* the `local/docker/nginx/*.conf` include runs, so any override file can redefine it. To widen the policy for your own external-origin assets, drop a file like `local/docker/nginx/csp.conf` with a single `set` directive:

```nginx
# local/docker/nginx/csp.conf
set $csp "default-src 'self'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' data: cdn.example.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data:; frame-src 'self'; connect-src 'self';";
```

The final `add_header Content-Security-Policy $csp always;` in the core template picks up whatever value `$csp` has at that point. Don't add a second `add_header Content-Security-Policy` of your own — browsers intersect multiple CSP headers (more restrictive, not wider).

> **Note:** the directory must exist before nginx starts. The deploy bootstrap (`scripts/deploy.sh`) ensures this automatically. If you start Docker Compose directly without the bootstrap, create the directory manually: `mkdir -p local/docker/nginx`.

---

## OG default image

The fallback `og:image` shown when a page has no featured image defaults to the core image at
`assets/images/og-default.jpg`.

**To replace it:** put your image at `local/assets/images/og-default.jpg`. The template reads
from that path first and falls back to the core default. The file is gitignored, so it's yours
to manage.

On first `./notACMS deploy` or `ddev build`, the bootstrap seeds `local/assets/images/og-default.jpg`
from the core default if it doesn't exist yet. Replace the seeded file with your own.

For a completely different approach (different format, dimensions, or logic), override the
`{% block og_default_image %}` block in `local/templates/base.html.twig`:

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block og_default_image %}
<meta property="og:image" content="{{ site_base_url ~ asset('local/images/og-default.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/jpeg">
{% endblock %}
```

---

## Local docs

`local/docs/` holds three site-specific reference files, all gitignored and seeded on first deploy:

**`local/docs/DESIGN.md`** — the machine-readable and human-readable design system: tokens, palette, typography, spacing, components, and design rationale. Follows the [`google-labs-code/design.md`](https://github.com/google-labs-code/design.md/blob/main/docs/spec.md) open specification (YAML front matter + markdown body). Seeded from `docs/demo/docs/DESIGN.md`.

**`local/docs/STYLEGUIDE.md`** — the component usage reference for this site: how to include Twig partials, which SCSS mixins to apply, and composition patterns. Does **not** duplicate tokens — it points to `DESIGN.md` for token values. Seeded from `docs/demo/docs/STYLEGUIDE.md`.

**`local/docs/EDITOR_GUIDE.md`** — the writing guide for this site: categories, approved tags, voice, and image generation styles. Seeded from `docs/demo/docs/EDITOR_GUIDE.md`.

All three files are freely editable after seeding — the bootstrap never overwrites them.

---

## Quick-reference cheat sheet

| Goal | Files to create |
|---|---|
| Override one template block | `local/templates/{path}.html.twig` extending `@base/{path}.html.twig` |
| Replace a template entirely | `local/templates/{path}.html.twig` (no `extends`) |
| Replace the entire base layout | `local/templates/base.html.twig` (no `extends`, written from scratch) + `local/assets/app.js` |
| Add JS, keep everything else | `local/assets/app.js` + `local/templates/base.html.twig` with `importmap('app')` and `importmap('app-local')` |
| Add CSS overrides on top of original | `local/assets/app.js` importing `./styles/app_local.scss` + `base.html.twig` loading both entrypoints |
| Replace all CSS | `local/assets/app.js` importing `./styles/app_local.scss` + `base.html.twig` with `importmap('app-local')` only |
| Add a new JS file | Place it in `local/assets/`, import from your local `app.js` |
| Override UI strings | `local/translations/messages.en.yaml` and/or `messages.pl.yaml` with only the keys to change |
| Seed or replace site content | `local/content/` (auto-seeded on first build from `docs/bare/content/`) |
| Use richer demo content | Copy `docs/demo/content/*` into `local/content/` |
| Add PHP listeners, Twig extensions, service decorators | `local/src/` with `namespace NotACms\Local\;` |
| Add nginx redirects or custom location rules | `local/docker/nginx/*.conf` (auto-seeded on first deploy from `docs/demo/docker/nginx/`) |
| Customise writing guide (voice, tags, image styles) | `local/docs/EDITOR_GUIDE.md` (auto-seeded on first deploy from `docs/demo/docs/EDITOR_GUIDE.md`) |
| Customise design system (tokens, colors, typography, rationale) | `local/docs/DESIGN.md` (auto-seeded on first deploy from `docs/demo/docs/DESIGN.md`) |
| Customise component usage guide (Twig includes, SCSS mixins) | `local/docs/STYLEGUIDE.md` (auto-seeded on first deploy from `docs/demo/docs/STYLEGUIDE.md`) |
| Replace default og:image | Override `{% block og_default_image %}` in `local/templates/base.html.twig` |

---

## Boilerplate

The `docs/bare/` directory is the default starting template for a new notACMS site.
On first `ddev build` (or `./notACMS rebuild`), the bootstrap copies it into `local/` if that
directory is empty. An optional richer demo is available in `docs/demo/`.

```
docs/bare/                      ← default seed (minimal content, system fonts)
├── content/
│   ├── _site.yaml
│   ├── _routes.yaml
│   ├── _tags.yaml
│   ├── blog/
│   └── pages/
└── docs/
    ├── DESIGN.md
    ├── EDITOR_GUIDE.md
    └── STYLEGUIDE.md

docs/demo/                      ← optional rich demo (populated docs pages,
├── content/                    ←   full pages, blog posts, translations)
│   ├── _site.yaml
│   ├── _routes.yaml
│   ├── _tags.yaml
│   ├── blog/
│   └── pages/
├── templates/                   ← Twig template overrides (empty by default)
├── translations/               ← UI string overrides (minimal)
├── assets/                       ← SCSS overrides, custom JS, images
│   └── images/
│       └── og-default.jpg
├── docker/
│   └── nginx/                    ← nginx config snippets
│       ├── redirects.conf
│       ├── error-pages.conf
│       └── csp.conf              ← Content-Security-Policy (demo-only)
├── docs/                         ← site-specific docs (seeded, then freely editable)
│   ├── EDITOR_GUIDE.md
│   └── STYLEGUIDE.md
└── src/                          ← PHP extensions (NotACms\Local\ namespace)
```

### Customisation patterns

**Add SCSS and JS on top of the original** — the lightest customisation:

```
local/
├── assets/
│   ├── app.js              # imports app_local.scss (and optionally extra JS)
│   └── styles/
│       └── app_local.scss  # @imports _variables.scss, then adds overrides
└── templates/
    └── base.html.twig      # extends @base/base.html.twig, loads both entrypoints
```

**Override specific Twig blocks** — extend the original and replace only what you need:

```
local/templates/
├── base.html.twig                  # extends @base/base.html.twig, overrides blocks
└── components/
    └── navigation.html.twig        # replaces just the nav component
```

The `@base` namespace always points to the original `templates/` directory, so
you can extend the real file without creating a circular reference.

**Full theme replacement** — replace the entire base layout:

Create `local/templates/base.html.twig` without `{% extends %}`. It becomes the root
layout for every page. You will typically also create `local/assets/app.js` that
loads your own SCSS/JS and point the `stylesheets` block to `importmap('app-local')`.

For a complete, production-ready full-theme replacement with all pages,
components, and custom assets, see `docs/demo/` — that is the reference
implementation that ships with the project.

**Override UI strings** — change translation keys without touching core files:

```
local/translations/
├── messages.en.yaml    # override any keys from translations/messages.en.yaml
└── messages.pl.yaml    # override any keys from translations/messages.pl.yaml
```

Put only the keys you want to change — omitted keys keep their core value.

---

## Working examples

The `docs/customization/` directory contains complete, copy-paste-ready examples. Each includes all necessary files and a README with exact `cp` commands.

| Example | Pattern | What it demonstrates |
|---|---|---|
| `custom-footer` | Full base replacement | Custom footer text — shows overriding the simple core footer |
| `custom-post-card` | Component replacement + SCSS | Horizontal card layout — shows `@base` extend, `app-local` importmap, and component override |
| `self-hosted-fonts` | Full base replacement + SCSS | Add custom fonts — shows preload, `@font-face`, and replacing system fonts |
| `php-service-decorator` | PHP `#[AsDecorator]` | Skip Turnstile validation — shows `NotACms\Local\` namespace and decorator pattern |
| `twig-filter` | PHP `#[AsTwigFilter]` | Add `|excerpt` filter — shows `NotACms\Local\` namespace and Twig attribute |
| `old-template` | Full standalone override | Restore old template after core redesign — templates, SCSS, JS, fonts, translations, images all self-contained |

---

## Upgrading from an older version

If you were running notACMS before the core redesign, the new default templates are incompatible with the old layout. The `docs/customization/old-template/` directory is a complete snapshot of the pre-redesign theme — all templates, SCSS, fonts, images, and translations — packaged as a ready-to-drop-in local override.

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

Your site will render exactly as it did before. You can then adopt new design elements selectively by removing individual files from `local/`.

If you had your own customisations on top of the old template (custom SCSS, component overrides, etc.), see `docs/customization/old-template/README.md` for migration notes.

---

## Directory setup

```bash
mkdir -p local/content local/templates local/assets/styles local/translations local/src
```

All files under `local/` are gitignored from this repository. You can version your entire
`local/` directory as a separate git repository — run `git init local/` (or clone your site
repo there) to manage content, templates, and extensions independently from the CMS upstream.
