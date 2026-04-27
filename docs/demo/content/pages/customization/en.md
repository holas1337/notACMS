---
title: "Customization"
description: "Override templates, styles, PHP services, and server config — all from the local/ directory."
template: page/doc
slug: customization
menu:
  weight: 40
  label: "Customization"
---

## The `local/` Override System

Every customization lives inside `local/`. This directory is your site-specific layer. The core `templates/`, `assets/`, and `src/` directories are the framework. Your overrides in `local/` take precedence.

```
local/
├── assets/          ← SCSS overrides + custom JS
│   ├── images/      ← og-default.jpg override
│   └── app.js       ← loaded as second importmap entrypoint
├── content/         ← all your content (pages, blog, config)
├── docker/          ← custom Nginx config
│   └── nginx/       ← .conf snippets (redirects, error pages)
├── docs/            ← site-specific EDITOR_GUIDE.md + STYLEGUIDE.md
├── src/             ← custom PHP services (NotACms\Local\ namespace)
├── templates/       ← template overrides
└── translations/    ← UI string overrides (messages.*.yaml)
```

## Template Overrides

To override a core template, create a file at the same path under `local/templates/`:

```bash
# Override the default page template
cp templates/page/default.html.twig local/templates/page/default.html.twig
# Now edit local/templates/page/default.html.twig
```

The Twig loader checks `local/templates/` first, then falls back to `templates/`. You can override any template without touching the core files.

### Override a single block

Use the `@base` namespace to extend the **original** template and override only specific blocks:

```twig
{# local/templates/blog/post.html.twig #}
{% extends '@base/blog/post.html.twig' %}

{% block sidebar_bottom %}
    <div class="my-widget">...</div>
{% endblock %}
```

The `@base` namespace always points to the original `templates/` directory, so you can extend the real file without creating a circular reference.

### Replace a template entirely

Create the file at the same path without `extends` — it replaces the original completely:

```twig
{# local/templates/components/navigation.html.twig #}
<nav class="my-nav">
    <a href="/">Home</a>
</nav>
```

**Common overrides:**

| Core template | Purpose |
|---|---|
| `templates/base.html.twig` | Global layout, header, footer |
| `templates/components/navigation.html.twig` | Nav links |
| `templates/page/default.html.twig` | Default page layout |
| `templates/page/doc.html.twig` | Documentation layout |
| `templates/blog/post.html.twig` | Blog post layout |

## SCSS Overrides

`local/assets/` is loaded as a second importmap entrypoint (`app-local`). Create a `local/assets/app.js` that imports your SCSS:

```js
// local/assets/app.js
import './styles/app_local.scss';
```

```scss
// local/assets/styles/app_local.scss
// WARNING: the root file must be named app_local.scss — not app.scss or any other name.
// sass-bundle requires all root SCSS files to have unique basenames.

// Import core SCSS variables (colors, spacing, typography) for use in your overrides:
@import '../../../../assets/styles/variables';

// Override CSS custom properties for your brand:
:root {
    --accent:    #2563EB;   // switch to blue
    --accent-bg: #EFF6FF;
}

// Add custom component styles below
.my-custom-hero {
    background: var(--accent);
    color: #fff;
}
```

> **Important:** When using `app-local`, you must also override `base.html.twig` to load both entrypoints. Create `local/templates/base.html.twig`:
>
> ```twig
> {# local/templates/base.html.twig #}
> {% extends '@base/base.html.twig' %}
>
> {% block stylesheets %}
>     {{ importmap('app') }}
>     {{ importmap('app-local') }}
> {% endblock %}
> ```
>
> Passing both entrypoints to `importmap()` guarantees the correct CSS load order: `app.css` (original) first, then your overrides.

> **Tip:** Always override CSS custom properties (`--accent`, `--bg`, etc.) rather than raw hex values. This ensures both light and dark mode work correctly with your brand colors. See the [Design Reference](/design-reference/) for the full list of tokens.

## Custom PHP Services

Create a service interface in `src/Service/`, implement it, and Symfony auto-wires it. For site-specific extensions, place implementations in `local/src/` using the `NotACms\Local\` namespace:

```php
// local/src/Service/MyCustomService.php
namespace NotACms\Local\Service;

use NotACms\Service\Content\ContentServiceInterface;

final class MyCustomService
{
    public function __construct(
        private readonly ContentServiceInterface $content,
    ) {}

    /**
     * @return array<\NotACms\Content\ContentItem>
     */
    public function recentPosts(string $locale): array
    {
        return $this->content->getRecentPosts($locale, 5);
    }
}
```

Inject it into a controller via constructor injection — Symfony's DI container handles the rest.

### Advanced: Event listeners, Twig filters, service decorators

Beyond basic services, you can also create event listeners, Twig filters, and service decorators in `local/src/`:

```php
// local/src/EventListener/MyListener.php
namespace NotACms\Local\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class MyListener
{
    public function __invoke(MyEvent $event): void { /* ... */ }
}
```

```php
// local/src/Twig/MyExtension.php
namespace NotACms\Local\Twig;

use Twig\Attribute\AsTwigFilter;

final class MyExtension
{
    #[AsTwigFilter('my_filter')]
    public function myFilter(string $value): string { /* ... */ }
}
```

```php
// local/src/Service/MySiteConfigDecorator.php
namespace NotACms\Local\Service;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: SiteConfigServiceInterface::class)]
final class MySiteConfigDecorator implements SiteConfigServiceInterface
{
    public function __construct(private readonly SiteConfigServiceInterface $inner) {}
    // override only the methods you need
}
```

## Custom Nginx Config

For production deployments, place Nginx config snippets in `local/docker/nginx/`. Any `.conf` file in this directory is included inside the `server {}` block automatically.

On first deploy, the bootstrap seeds three files from `docs/demo/docker/nginx/`:

- **`redirects.conf`** — SEO redirects (www → non-www, legacy URLs)
- **`error-pages.conf`** — locale-aware error pages
- **`csp.conf`** — widened Content-Security-Policy for the demo theme's external origins (Phosphor Icons, Google Fonts)

```nginx
# local/docker/nginx/redirects.conf — example
location = /old-page/ { return 301 /new-page/; }
location ~ "^\d{4}/\d{2}/\d{2}/([a-z0-9-]+)/$" { return 301 /blog/$1/; }
```

You can split config across multiple files — all `*.conf` files are included. Prefix names with numbers if order matters (e.g. `10-redirects.conf`, `20-cache.conf`).

### Content-Security-Policy override

The core template emits a bare-theme-safe CSP through a `$csp` variable set *before* the `local/docker/nginx/*.conf` include, so any override file can redefine it. To widen the policy for your own external assets, create a file like `local/docker/nginx/csp.conf` with one `set` directive:

```nginx
# local/docker/nginx/csp.conf — widens CSP for external origins
set $csp "default-src 'self'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' data: cdn.example.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data:; frame-src 'self'; connect-src 'self';";
```

Don't add a second `add_header Content-Security-Policy` — browsers intersect multiple CSP headers (more restrictive, not wider).

## Translation Overrides

Create `local/translations/messages.en.yaml` (and/or `messages.pl.yaml`, `messages.de.yaml`) with only the keys you want to change. They are merged on top of the core translation files, so any key you omit keeps its original value.

```yaml
# local/translations/messages.en.yaml
blog:
  read_more: "Read on"

footer:
  copyright: "All rights reserved — My Site"
```

```yaml
# local/translations/messages.pl.yaml
blog:
  read_more: "Czytaj dalej"

footer:
  copyright: "Wszelkie prawa zastrzeżone — Moja strona"
```

You can override any key from `translations/messages.*.yaml`. Nested keys use the same indented YAML structure as the original files.

## OG Default Image

The fallback `og:image` shown when a page has no featured image defaults to a branded placeholder. To replace it, put your image at `local/assets/images/og-default.jpg`. The template reads from that path automatically.

On first `./notACMS deploy` or `ddev build`, the bootstrap seeds this file from the core default if it doesn't exist yet. Replace the seeded file with your own.

For a completely different approach (different format, dimensions, or logic), override the `{% block og_default_image %}` block in `local/templates/base.html.twig`:

```twig
{# local/templates/base.html.twig #}
{% extends '@base/base.html.twig' %}

{% block og_default_image %}
<meta property="og:image" content="{{ site_base_url ~ asset('images/og-default.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/jpeg">
{% endblock %}
```

## Local Docs

`local/docs/` holds two site-specific reference files, both gitignored and seeded on first deploy:

**`local/docs/EDITOR_GUIDE.md`** — the writing guide for your site: categories, approved tags, voice, and image generation styles. Seeded from `docs/demo/docs/EDITOR_GUIDE.md`. System-level content mechanics (frontmatter fields, URL structure, series, drafts) stay in `docs/EDITOR_GUIDE.md`.

**`local/docs/STYLEGUIDE.md`** — the design reference for your site: design tokens, color palette, typography, and component list. Seeded from `docs/demo/docs/STYLEGUIDE.md`. Styleguide mechanics (the `/styleguide/` dev page, SCSS conventions) stay in `docs/STYLEGUIDE.md`.

Both files are freely editable after seeding — the bootstrap never overwrites them.

## Extending the Build

Add custom build steps by creating a Symfony console command in `local/src/Command/`:

```php
// local/src/Command/GenerateSitemapCommand.php
namespace NotACms\Local\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'local:sitemap')]
class GenerateSitemapCommand extends Command
{
    // ...
}
```

Then add it to your `ddev build` alias or call it explicitly with `ddev exec bin/console local:sitemap`.

## Working Examples

The `docs/customization/` directory contains complete, copy-paste-ready examples. Each includes all necessary files and a README with exact `cp` commands.

| Example | Pattern | What it demonstrates |
|---|---|---|
| `custom-post-card` | Component replacement + SCSS | Horizontal card layout — shows `@base` extend, `app-local` importmap, and component override |
| `self-hosted-fonts` | Full base replacement + SCSS | Self-hosted custom fonts — shows preload, `@font-face`, and replacing system fonts |
| `php-service-decorator` | PHP `#[AsDecorator]` | Skip Turnstile validation — shows `NotACms\Local\` namespace and decorator pattern |
| `twig-filter` | PHP `#[AsTwigFilter]` | Add `|excerpt` filter — shows `NotACms\Local\` namespace and Twig attribute |

---

## Production Customization

### Environment Variables

Create `.env.local` in project root (gitignored):

```bash
# Required
APP_SECRET=changeme-generate-new
URL=example.com

# Network
NGINX_PORT=8123
CERTRESOLVER=le  # or 'dummy' for testing

# Optional: Contact form
RUNTIME_PHP_ENABLED=true
MAILER_DSN=smtp://user:pass@smtp.example.com:587
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
```

### Docker Compose Overrides

Create `docker-compose.override.yaml` for local customizations:

```yaml
services:
  nginx:
    ports:
      - "8080:80"  # Override NGINX_PORT for this machine
```

### Security Headers

Add to `local/docker/nginx/redirects.conf`:

```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Cache static assets
location ~* \.(css|js|webp|ico|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### SSL / HTTPS with Traefik

The included Docker Compose has Traefik labels for automatic SSL.

Requirements:
- External network: `docker network create web`
- DNS A-record pointing to server IP
- Ports 80/443 open

`.env.local`:
```bash
URL=yourdomain.com
CERTRESOLVER=le  # Let's Encrypt
#CERTRESOLVER=dummy  # Self-signed for testing
```

For custom Traefik config, add labels to `docker-compose.override.yaml`.

### Custom Build Steps

Add post-build commands by editing `scripts/rebuild-content.sh` or creating a wrapper:

```bash
#!/bin/bash
# custom-deploy.sh
./notACMS deploy --prod
# Custom: Sync to S3, purge CDN, etc.
aws s3 sync public/static/ s3://my-bucket/ --delete
```

### File Permissions

Ensure correct ownership for production:

```bash
# Fix permissions before deploy
sudo chown -R $USER:$USER .
chmod 755 local/docker/nginx/
```
