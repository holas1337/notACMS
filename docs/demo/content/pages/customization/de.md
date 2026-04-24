---
title: "Anpassung"
description: "Templates, Styles, PHP-Services und Server-Konfiguration überschreiben — alles aus dem local/-Verzeichnis."
template: page/doc
slug: customization
menu:
  weight: 40
  label: "Anpassung"
---

## Das `local/`-Override-System

Jede Anpassung lebt in `local/`. Dieses Verzeichnis ist deine site-spezifische Schicht. Die Core-`templates/`, `assets/` und `src/`-Verzeichnisse sind das Framework. Deine Overrides in `local/` haben Vorrang.

```
local/
├── assets/          # SCSS-Overrides + custom JS
│   ├── images/      # og-default.jpg überschreiben
│   └── app.js       # als zweiter Entrypoint geladen
├── content/         # all dein Content (Seiten, Blog, Config)
├── docker/          # custom Nginx-Config
│   └── nginx/       # .conf-Snippets (Redirects, Fehlerseiten)
├── docs/            # site-spezifische EDITOR_GUIDE.md + STYLEGUIDE.md
├── src/             # custom PHP-Services (NotACms\Local\ Namespace)
├── templates/       # Template-Overrides
└── translations/    # UI-String-Overrides (messages.*.yaml)
```

## Template-Overrides

Um ein Core-Template zu überschreiben, erstelle eine Datei am gleichen Pfad unter `local/templates/`:

```bash
# Standard-Page-Template überschreiben
cp templates/page/default.html.twig local/templates/page/default.html.twig
# Jetzt local/templates/page/default.html.twig editieren
```

Der Twig-Loader checkt zuerst `local/templates/`, dann Fallback auf `templates/`. Du kannst jedes Template überschreiben, ohne Core-Dateien zu touchieren.

### Einzelnen Block überschreiben

Verwende den `@base`-Namespace, um das **Original-Template** zu erweitern und nur spezifische Blöcke zu überschreiben:

```twig
{# local/templates/blog/post.html.twig #}
{% extends '@base/blog/post.html.twig' %}

{% block sidebar_bottom %}
    <div class="my-widget">...</div>
{% endblock %}
```

Der `@base`-Namespace zeigt immer auf das originale `templates/`-Verzeichnis, sodass du die echte Datei erweitern kannst, ohne einen Zirkelbezug zu erzeugen.

### Template vollständig ersetzen

Erstelle die Datei am gleichen Pfad ohne `extends` — sie ersetzt das Original komplett:

```twig
{# local/templates/components/navigation.html.twig #}
<nav class="my-nav">
    <a href="/">Home</a>
</nav>
```

**Häufige Overrides:**

| Core-Template | Zweck |
|---|---|
| `templates/base.html.twig` | Global Layout, Header, Footer |
| `templates/components/navigation.html.twig` | Nav-Links |
| `templates/page/default.html.twig` | Standard-Page-Layout |
| `templates/page/doc.html.twig` | Dokumentations-Layout |
| `templates/blog/post.html.twig` | Blog-Post-Layout |

## SCSS-Overrides

`local/assets/` wird als zweiter Importmap-Entrypoint (`app-local`) geladen. Erstelle ein `local/assets/app.js`, das dein SCSS importiert:

```js
// local/assets/app.js
import './styles/app_local.scss';
```

```scss
// local/assets/styles/app_local.scss
// WARNUNG: Die Root-Datei muss app_local.scss heißen — nicht app.scss oder ein anderer Name.
// sass-bundle erfordert eindeutige Basisnamen für alle Root-SCSS-Dateien.

// Core SCSS-Variablen (Farben, Abstände, Typografie) für die Verwendung in Overrides importieren:
@import '../../../../assets/styles/variables';

// CSS Custom Properties für deine Brand überschreiben:
:root {
    --accent:    #2563EB;   // auf blau wechseln
    --accent-bg: #EFF6FF;
}

// Custom Component Styles darunter
.my-custom-hero {
    background: var(--accent);
    color: #fff;
}
```

> **Wichtig:** Bei Verwendung von `app-local` musst du auch `base.html.twig` überschreiben, um beide Entrypoints zu laden. Erstelle `local/templates/base.html.twig`:
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
> Beide Entrypoints an `importmap()` zu übergeben garantiert die korrekte CSS-Ladereihenfolge: `app.css` (Original) zuerst, dann deine Overrides.

> **Tipp:** Überschreibe immer CSS Custom Properties (`--accent`, `--bg`, etc.) statt direkter Hex-Werte. Das stellt sicher, dass Hell- und Dunkel-Modus mit deinen Markenfarben korrekt funktionieren. Die vollständige Token-Liste findest du in der [Design-Referenz](/de/design-reference/).

## Custom PHP-Services

Erstelle ein Service-Interface in `src/Service/`, implementiere es, und Symfony autowired es automatisch. Für site-spezifische Erweiterungen platziere Implementierungen in `local/src/` mit dem `NotACms\Local\`-Namespace:

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

Inject in einen Controller via Constructor Injection — Symfony's DI-Container erledigt den Rest.

### Erweitert: Event-Listener, Twig-Filter, Service-Dekoratoren

Neben einfachen Services kannst du auch Event-Listener, Twig-Filter und Service-Dekoratoren in `local/src/` erstellen:

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
    // nur die Methoden überschreiben, die du brauchst
}
```

## Custom Nginx-Config

Für Produktions-Deployments platziere Nginx-Konfigurations-Snippets in `local/docker/nginx/`. Jede `.conf`-Datei in diesem Verzeichnis wird automatisch in den `server {}`-Block eingebunden.

Beim ersten Deploy seedet der Bootstrap drei Dateien aus `docs/demo/docker/nginx/`:

- **`redirects.conf`** — SEO-Redirects (www → non-www, Legacy-URLs)
- **`error-pages.conf`** — locale-aware Fehlerseiten
- **`csp.conf`** — erweiterte Content-Security-Policy für externe Origins des Demo-Themes (Phosphor Icons, Google Fonts)

```nginx
# local/docker/nginx/redirects.conf — Beispiel
location = /old-page/ { return 301 /new-page/; }
location ~ "^\d{4}/\d{2}/\d{2}/([a-z0-9-]+)/$" { return 301 /blog/$1/; }
```

Konfiguration kann über mehrere Dateien aufgeteilt werden — alle `*.conf`-Dateien werden eingebunden. Präfixe mit Nummern, wenn die Reihenfolge wichtig ist (z.B. `10-redirects.conf`, `20-cache.conf`).

### Content-Security-Policy-Override

Das Core-Template emittiert eine bare-theme-sichere CSP über eine `$csp`-Variable, die *vor* dem `local/docker/nginx/*.conf`-Include gesetzt wird, sodass jede Override-Datei sie neu definieren kann. Um die Policy für eigene externe Assets zu erweitern, lege eine Datei wie `local/docker/nginx/csp.conf` mit einer einzigen `set`-Direktive an:

```nginx
# local/docker/nginx/csp.conf — erweitert CSP um externe Origins
set $csp "default-src 'self'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' data: cdn.example.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data:; frame-src 'self'; connect-src 'self';";
```

Füge kein zweites `add_header Content-Security-Policy` hinzu — Browser schneiden mehrere CSP-Header (restriktiver, nicht weiter).

## Übersetzungs-Overrides

Erstelle `local/translations/messages.en.yaml` und/oder `messages.pl.yaml`, `messages.de.yaml` mit nur den Schlüsseln, die du ändern möchtest. Sie werden über die Core-Übersetzungsdateien gemerged, sodass weggelassene Schlüssel ihren Originalwert behalten.

```yaml
# local/translations/messages.de.yaml
nav:
  blog: "Artikel"

footer:
  copyright: "Alle Rechte vorbehalten — Meine Seite"
```

Du kannst jeden Schlüssel aus `translations/messages.*.yaml` überschreiben. Verschachtelte Schlüssel verwenden die gleiche eingerückte YAML-Struktur wie die Originaldateien.

## OG-Standardbild

Das Fallback-`og:image`, das angezeigt wird wenn eine Seite kein Beitragsbild hat, verwendet standardmäßig einen gebrandeten Platzhalter. Um es zu ersetzen, platziere dein Bild unter `local/assets/images/og-default.jpg`. Das Template liest automatisch von diesem Pfad.

Beim ersten `./notACMS deploy` oder `ddev build` seedet der Bootstrap diese Datei aus dem Core-Standard, falls sie noch nicht existiert. Ersetze die geseedete Datei mit deiner eigenen.

Für einen komplett anderen Ansatz (anderes Format, andere Dimensionen oder Logik), überschreibe den `{% block og_default_image %}`-Block in `local/templates/base.html.twig`:

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

## Lokale Docs

`local/docs/` enthält zwei site-spezifische Referenzdateien, beide gitignored und beim ersten Deploy geseedt:

**`local/docs/EDITOR_GUIDE.md`** — der Schreibleitfaden für deine Site: Kategorien, genehmigte Tags, Stimme und Bildgenerierungsstile. Geseedt aus `docs/demo/docs/EDITOR_GUIDE.md`. System-Level-Content-Mechaniken (Frontmatter-Felder, URL-Struktur, Serien, Entwürfe) bleiben in `docs/EDITOR_GUIDE.md`.

**`local/docs/STYLEGUIDE.md`** — die Design-Referenz für deine Site: Design-Tokens, Farbpalette, Typografie und Komponentenliste. Geseedt aus `docs/demo/docs/STYLEGUIDE.md`. Styleguide-Mechaniken (die `/styleguide/`-Dev-Seite, SCSS-Konventionen) bleiben in `docs/STYLEGUIDE.md`.

Beide Dateien sind nach dem Seeding frei editierbar — der Bootstrap überschreibt sie nie.

## Build erweitern

Füge custom Build-Schritte hinzu, indem du einen Symfony Console Command in `local/src/Command/` erstellst:

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

Dann füge ihn deinem `ddev build`-Alias hinzu oder rufe explizit auf mit `ddev exec bin/console local:sitemap`.

## Praxisbeispiele

Das Verzeichnis `docs/customization/` enthält vollständige, kopierfertige Beispiele. Jedes enthält alle benötigten Dateien und ein README mit exakten `cp`-Befehlen.

| Beispiel | Muster | Was es demonstriert |
|---|---|---|
| `custom-footer` | Vollständiger Basis-Ersatz | Benutzerdefinierter Footer — zeigt, wie man den einfachen Core-Footer anpasst |
| `custom-post-card` | Komponenten-Ersatz + SCSS | Horizontales Karten-Layout — zeigt `@base`-Extend, `app-local`-Importmap und Komponenten-Override |
| `self-hosted-fonts` | Vollständiger Basis-Ersatz + SCSS | Eigene Schriftarten laden — zeigt Preload, `@font-face` und Ersetzen der System-Schriftarten |
| `php-service-decorator` | PHP `#[AsDecorator]` | Turnstile-Validierung überspringen — zeigt `NotACms\Local\`-Namespace und Dekorator-Muster |
| `twig-filter` | PHP `#[AsTwigFilter]` | `|excerpt`-Filter hinzufügen — zeigt `NotACms\Local\`-Namespace und Twig-Attribut |

---

## Produktions-Anpassung

### Umgebungsvariablen

Erstelle `.env.local` im Projekt-Root (gitignored):

```bash
# Required
APP_SECRET=changeme-generate-new
URL=example.com

# Network
NGINX_PORT=8123
CERTRESOLVER=le  # oder 'dummy' zum Testen

# Optional: Kontaktformular
RUNTIME_PHP_ENABLED=true
MAILER_DSN=smtp://user:pass@smtp.example.com:587
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
```

### Docker Compose Overrides

Erstelle `docker-compose.override.yaml` für lokale Anpassungen:

```yaml
services:
  nginx:
    ports:
      - "8080:80"  # NGINX_PORT für diese Maschine überschreiben
```

### Security-Header

Zufügen zu `local/docker/nginx/redirects.conf`:

```nginx
# Security-Header
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Statische Assets cachen
location ~* \.(css|js|webp|ico|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### SSL / HTTPS mit Traefik

Das Docker Compose hat Traefik-Labels für automatisches SSL.

Requirements:
- Externes Netzwerk: `docker network create web`
- DNS A-Record zeigt auf Server-IP
- Ports 80/443 offen

`.env.local`:
```bash
URL=yourdomain.com
CERTRESOLVER=le  # Let's Encrypt
#CERTRESOLVER=dummy  # Self-signed zum Testen
```

Für custom Traefik-Config, Labels zu `docker-compose.override.yaml` hinzufügen.

### Custom Build-Schritte

Post-Build-Commands hinzufügen durch Editieren von `scripts/rebuild-content.sh` oder Wrapper erstellen:

```bash
#!/bin/bash
# custom-deploy.sh
./notACMS deploy --prod
# Custom: Zu S3 sync, CDN purgen, etc.
aws s3 sync public/static/ s3://my-bucket/ --delete
```

### Datei-Berechtigungen

Korrekte Ownership für Produktion sicherstellen:

```bash
# Berechtigungen vor dem Deploy fixen
sudo chown -R $USER:$USER .
chmod 755 local/docker/nginx/
```
