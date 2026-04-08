# Customisation Guide

notACMS is designed to be customised without touching core files. Everything goes
in a `local/` directory at the project root — it is gitignored, so your changes
are never overwritten by upstream updates.

```
local/
├── content/                    # site content (auto-seeded on first build)
│   ├── _site.yaml              # site name, locales, author, contact form
│   ├── _routes.yaml            # translated URL path segments per locale
│   ├── _tags.yaml              # tag slug translations between locales
│   ├── blog/
│   │   ├── community/
│   │   ├── projects/
│   │   └── tutorials/
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
│       └── local.scss          # override / extend SCSS
├── docker/
│   └── nginx/                  # nginx config snippets (auto-seeded on first deploy)
│       ├── redirects.conf      # site-specific redirects (SEO, legacy URLs)
│       └── error-pages.conf    # locale-aware error pages (auto-seeded on first deploy)
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
{% endblock %}
```

Available blocks are defined in `templates/base.html.twig`:
`title`, `meta_description`, `robots`, `stylesheets`, `structured_data`,
`navigation`, `body`, `sidebar_top`, `sidebar_bottom`, `javascripts`, and more.

---

## JavaScript and CSS overrides

The `app-local` importmap entrypoint points to `local/assets/app.js` when that
file exists. To activate it, override the `stylesheets` block in
`local/templates/base.html.twig`:

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block stylesheets %}
    {{ importmap(['app', 'app-local']) }}
{% endblock %}
```

Passing both entrypoints to `importmap()` guarantees the correct CSS load order:
`app.css` (original) is emitted first, then `local.css` (your overrides).

### JS-only changes (keep all original styles)

```js
// local/assets/app.js
import './my-feature.js';        // your new JS (local/assets/my-feature.js)
```

### Add CSS overrides on top of the original

```js
// local/assets/app.js
import './styles/local.scss';    // your overrides (no @import of original needed)
// import './my-feature.js';     // optional extra JS
```

```scss
/* local/assets/styles/local.scss */

/* Optional: import original SCSS variables for use in your rules */
@import '../../../../assets/styles/variables';

/* Your overrides — these load after the original CSS */
:root {
    --color-accent: #e85d04;
}

.post-body {
    font-size: 1.0625rem;
    color: $color-primary;    /* SCSS variable from imported _variables.scss */
}
```

---

## SCSS overrides

Create `local/assets/styles/local.scss`. It is compiled automatically by
`sass:build` alongside the original — no configuration needed.

> **Note:** the local SCSS root file **must be named `local.scss`**, not `app.scss`.
> `sass-bundle` requires all root SCSS files to have unique basenames; both the
> original and a file named `app.scss` would conflict at compile time.

> **Note:** when you provide a `local.scss` you must also provide a local
> `app.js` that imports it, and a `local/templates/base.html.twig` that loads
> both entrypoints — otherwise the compiled CSS is never linked in the page.

### Override — keep original styles, add on top

Write only your additions and overrides. The original CSS is loaded separately
by the `app` entrypoint, so there is no double-loading. Optionally import
`_variables.scss` to access SCSS variables like `$color-primary` at compile time.

```scss
/* local/assets/styles/local.scss */
@import '../../../../assets/styles/variables';   /* SCSS variables — no CSS rules */

/* your additions / overrides below */
:root {
    --color-accent: #e85d04;
}

.my-component {
    border: 1px solid $color-primary;
}
```

### Replace — completely new styles

```scss
/* local/assets/styles/local.scss — written from scratch */
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
automatically seeds it from `docs/examples/content/`. After that, all content changes go in
`local/content/`.

```
local/content/
├── _site.yaml          # site name, locales, author, contact form
├── _routes.yaml        # translated URL path segments per locale
├── _tags.yaml          # tag slug translations between locales
├── blog/               # blog posts, co-located by locale
│   ├── community/
│   ├── projects/
│   └── tutorials/
└── pages/              # static pages
    ├── about/
    ├── contact/
    ├── home/
    ├── privacy-policy/
    └── projects/
```

See `docs/EDITOR_GUIDE.md` for full content authoring reference.

---

## Translation overrides

Create `local/translations/messages.en.yaml` and/or `local/translations/messages.pl.yaml`
with only the keys you want to change. They are merged on top of the core translation files,
so any key you omit keeps its original value.

```yaml
# local/translations/messages.en.yaml
nav:
  blog: "Articles"

footer:
  copyright: "All rights reserved — My Site"
```

```yaml
# local/translations/messages.pl.yaml
nav:
  blog: "Artykuły"

footer:
  copyright: "Wszelkie prawa zastrzeżone — Moja strona"
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
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: NotACms\Service\SiteConfigService::class)]
final class MySiteConfigDecorator implements NotACms\Service\SiteConfigServiceInterface
{
    public function __construct(private readonly NotACms\Service\SiteConfigServiceInterface $inner) {}
    // override only the methods you need
}
```

---

## nginx overrides

Any `.conf` file placed in `local/docker/nginx/` is included inside the `server {}` block when the nginx container starts. Use this for site-specific redirects, custom location rules, or extra headers.

On first `./notACMS deploy`, the bootstrap seeds two files from `docs/examples/nginx/`:

- **`redirects.conf`** — example SEO and legacy-URL redirects
- **`error-pages.conf`** — locale-aware error pages (serves `/pl/404.html` for Polish URLs, `/404.html` for everything else; add a block for each non-default locale)

```nginx
# local/docker/nginx/redirects.conf — example
location = /old-page/ { return 301 /new-page/; }
location ~ "^\d{4}/\d{2}/\d{2}/([a-z0-9-]+)/$" { return 301 /blog/$1/; }
```

You can split config across multiple files — all `*.conf` files in the directory are included. Files are included in filesystem order, so prefix names with numbers if order matters (e.g. `10-redirects.conf`, `20-cache.conf`).

> **Note:** the directory must exist before nginx starts. The deploy bootstrap (`scripts/deploy.sh`) ensures this automatically. If you start Docker Compose directly without the bootstrap, create the directory manually: `mkdir -p local/docker/nginx`.

---

## OG default image

The fallback `og:image` shown when a page has no featured image defaults to `# notACMS` on a dark background (1200×630 JPEG).

**To replace it:** put your image at `local/assets/images/og-default.jpg`. That's it — the template reads from that path and it is gitignored, so it's yours to manage.

On first `./notACMS deploy` or `ddev build`, the bootstrap seeds `local/assets/images/og-default.jpg` from the core default if it doesn't exist yet. Replace the seeded file with your own.

For a completely different approach (different format, dimensions, or logic), override the `{% block og_default_image %}` block in `local/templates/base.html.twig`:

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

`local/docs/` holds two site-specific reference files, both gitignored and seeded on first deploy:

**`local/docs/EDITOR_GUIDE.md`** — the writing guide for this site: categories, approved tags, voice, and image generation styles. Seeded from `docs/examples/docs/EDITOR_GUIDE.md`. System-level content mechanics (frontmatter fields, URL structure, series, drafts, etc.) stay in `docs/EDITOR_GUIDE.md`.

**`local/docs/STYLEGUIDE.md`** — the design reference for this site: design tokens, color palette, typography, and component list. Seeded from `docs/examples/docs/STYLEGUIDE.md`. Since a custom theme can completely replace the design system, this file reflects the *active* token values for this installation. Styleguide mechanics (the `/styleguide/` dev page, SCSS conventions, component update checklist) stay in `docs/STYLEGUIDE.md`.

Both files are freely editable after seeding — the bootstrap never overwrites them.

---

## Quick-reference cheat sheet

| Goal | Files to create |
|---|---|
| Override one template block | `local/templates/{path}.html.twig` extending `@base/{path}.html.twig` |
| Replace a template entirely | `local/templates/{path}.html.twig` (no `extends`) |
| Replace the entire base layout | `local/templates/base.html.twig` (no `extends`, written from scratch) |
| Add JS, keep everything else | `local/assets/app.js` + `local/templates/base.html.twig` with `importmap(['app', 'app-local'])` |
| Add CSS overrides on top of original | `local/assets/app.js` importing `./styles/local.scss` + `base.html.twig` with `importmap(['app', 'app-local'])` |
| Replace all CSS | `local/assets/app.js` importing `./styles/local.scss` + `base.html.twig` with `importmap('app-local')` |
| Add a new JS file | Place it in `local/assets/`, import from your local `app.js` |
| Override UI strings | `local/translations/messages.en.yaml` and/or `messages.pl.yaml` with only the keys to change |
| Seed or replace site content | `local/content/` (auto-seeded on first build from `docs/examples/content/`) |
| Add PHP listeners, Twig extensions, service decorators | `local/src/` with `namespace NotACms\Local\;` |
| Add nginx redirects or custom location rules | `local/docker/nginx/*.conf` (auto-seeded on first deploy from `docs/examples/nginx/`) |
| Customise writing guide (voice, tags, image styles) | `local/docs/EDITOR_GUIDE.md` (auto-seeded on first deploy from `docs/examples/docs/EDITOR_GUIDE.md`) |
| Customise design reference (colors, tokens, components) | `local/docs/STYLEGUIDE.md` (auto-seeded on first deploy from `docs/examples/docs/STYLEGUIDE.md`) |
| Replace default og:image (shown when post has no featured image) | Override `{% block og_default_image %}` in `local/templates/base.html.twig` |

---

## Examples

Ready-to-copy boilerplates live in `docs/examples/`. Each is a self-contained
`local/` directory you can copy straight to the project root.

### `templates/starter-extend/` — add SCSS and JS on top of the original

The lightest customisation: keep all original styles and scripts, add your own.

```
docs/examples/templates/starter-extend/local/
├── assets/
│   ├── app.js              # imports local.scss (and optionally extra JS)
│   └── styles/
│       └── local.scss      # @imports _variables.scss, then adds overrides
└── templates/
    └── base.html.twig      # overrides stylesheets block: importmap(['app', 'app-local'])
```

Copy to `local/` and add your own rules to the SCSS file.

### `templates/block-override/` — override specific Twig blocks

Extend the original base template and replace only the parts you need. No
risk of missing a new block added upstream — everything else inherits
automatically.

```
docs/examples/templates/block-override/local/templates/
├── base.html.twig                  # extends @base/base.html.twig, overrides blocks
└── components/
    └── navigation.html.twig        # replaces just the nav component
```

The `@base` namespace always points to the original `templates/` directory, so
you can extend the real file without creating a circular reference.

### `templates/starter-full-override/` — minimal full layout replacement

A bare-bones starting point for replacing the entire base layout. One file,
no assumptions about structure or CSS.

```
docs/examples/templates/starter-full-override/local/templates/
└── base.html.twig    # minimal HTML document, importmap('app-local'), all key blocks
```

Copy to `local/templates/` and fill in the blocks. Create `local/assets/app.js`
with your own SCSS/JS imports.

### `templates/full-override/` — complete base layout with all Symfony wiring

The same full replacement but with all production wiring included: hreflang,
locale-redirect, OG meta stubs, cookie banner, Cloudflare Analytics, and all
available Twig globals documented in the header comment.

### `translations/translation-override/` — override UI strings

Shows how to change a handful of translation keys without touching core files.
Two files, one per locale, each overriding three keys (`nav.blog`, `sidebar.contact_intro`,
`sidebar.contact_cta`, `footer.copyright`).

```
docs/examples/translations/translation-override/local/translations/
├── messages.en.yaml
└── messages.pl.yaml
```

Copy either or both files to `local/translations/` and edit the keys you need.

### `templates/material-cards/` — Material Design dark theme with left sidebar and card grid

A fully working theme override demonstrating three changes at once:
Material Design 2 dark surfaces and typography, sidebar moved to the left column,
and the homepage recent-posts section laid out as a two-column card grid.

```
docs/examples/templates/material-cards/local/
├── assets/
│   ├── app.js                     # imports local.scss only
│   └── styles/
│       └── local.scss             # Material dark overrides (no @import of original)
└── templates/
    ├── base.html.twig             # overrides stylesheets: importmap(['app', 'app-local'])
    └── page/
        └── home.html.twig         # adds home-posts-grid wrapper
```

**How it works:**

- `local/templates/base.html.twig` extends the original and overrides the
  `stylesheets` block to call `importmap(['app', 'app-local'])`. This guarantees
  `app.css` is emitted before `local.css` — so overrides actually override.
- `app.js` imports only `./styles/local.scss`. The original JS and CSS are handled
  by the `app` entrypoint; no double-loading.
- `styles/local.scss` defines Material Design 2 dark tokens (`$md-bg: #121212`,
  `$md-surface: #1e1e1e`, `$md-primary: #90caf9` Blue 200) and applies them via
  targeted CSS overrides covering body, nav, cards, sidebar, content, buttons,
  tags, pagination, footer, and code blocks.
- The sidebar is placed in the left column via explicit `grid-column` on
  `.content-main` and `.content-sidebar` — more reliable than `order: -1`
  for CSS Grid auto-placement.
- `home.html.twig` extends `base.html.twig` and wraps the post loop in
  `<div class="home-posts-grid">`, which the SCSS turns into a two-column grid
  from 600 px upward.

**To use:** copy the `local/` directory to your project root, then run
`ddev build` (or `ddev exec php bin/console sass:build`).

---

## Directory setup

```bash
mkdir -p local/content local/templates local/assets/styles local/translations local/src
```

All files under `local/` are gitignored from this repository. You can version your entire
`local/` directory as a separate git repository — run `git init local/` (or clone your site
repo there) to manage content, templates, and extensions independently from the CMS upstream.
