---
title: "Dostosowywanie"
description: "Nadpisz szablony, style, serwisy PHP i konfigurację serwera — wszystko z katalogu local/."
template: page/doc
slug: dostosowywanie
menu:
  weight: 40
  label: "Dostosowywanie"
---

## System nadpisywania `local/`

Każda modyfikacja znajduje się w katalogu `local/`. Ten katalog to twoja warstwa site-specific. Core `templates/`, `assets/` i `src/` to framework. Twoje nadpisywania w `local/` mają pierwszeństwo.

```
local/
├── assets/          # Nadpisywania SCSS + własny JS
│   ├── images/      # nadpisanie og-default.jpg
│   └── app.js       # ładowany jako drugi entrypoint
├── content/         # cała twoja treść (strony, blog, config)
├── docker/          # własna konfiguracja Nginx
│   └── nginx/       # fragmenty .conf (redirects, strony błędów)
├── docs/            # site-specific EDITOR_GUIDE.md + STYLEGUIDE.md
├── src/             # własne serwisy PHP (namespace NotACms\Local\)
├── templates/       # nadpisywania szablonów
└── translations/    # nadpisywania stringów UI (messages.*.yaml)
```

## Nadpisywanie szablonów

Aby nadpisać szablon core, utwórz plik w tej samej ścieżce pod `local/templates/`:

```bash
# Nadpisz domyślny szablon strony
cp templates/page/default.html.twig local/templates/page/default.html.twig
# Teraz edytuj local/templates/page/default.html.twig
```

Loader Twig sprawdza najpierw `local/templates/`, potem sięga do `templates/`. Możesz nadpisać dowolny szablon bez dotykania plików core.

### Nadpisywanie pojedynczego bloku

Użyj namespace `@base`, aby rozszerzyć **oryginalny** szablon i nadpisać tylko konkretne bloki:

```twig
{# local/templates/blog/post.html.twig #}
{% extends '@base/blog/post.html.twig' %}

{% block sidebar_bottom %}
    <div class="my-widget">...</div>
{% endblock %}
```

Namespace `@base` zawsze wskazuje na oryginalny katalog `templates/`, więc możesz rozszerzyć prawdziwy plik bez tworzenia odwołania cyklicznego.

### Pełne zastąpienie szablonu

Utwórz plik w tej samej ścieżce bez `extends` — zastępuje on oryginał w całości:

```twig
{# local/templates/components/navigation.html.twig #}
<nav class="my-nav">
    <a href="/">Home</a>
</nav>
```

**Częste nadpisywania:**

| Szablon Core | Cel |
|---|---|
| `templates/base.html.twig` | Globalny layout, header, footer |
| `templates/components/navigation.html.twig` | Linki nawigacyjne |
| `templates/page/default.html.twig` | Domyślny layout strony |
| `templates/page/doc.html.twig` | Layout dokumentacji |
| `templates/blog/post.html.twig` | Layout posta bloga |

## Nadpisywanie SCSS

`local/assets/` jest ładowane jako drugi entrypoint importmap (`app-local`). Utwórz `local/assets/app.js`, który importuje twój SCSS:

```js
// local/assets/app.js
import './styles/app_local.scss';
```

```scss
// local/assets/styles/app_local.scss
// UWAGA: plik główny musi nazywać się app_local.scss — nie app.scss ani inaczej.
// sass-bundle wymaga unikalnych nazw bazowych dla wszystkich głównych plików SCSS.

// Importuj zmienne SCSS core (kolory, odstępy, typografia) do użycia w nadpisaniach:
@import '../../../../assets/styles/variables';

// Nadpisz CSS custom properties dla swojej marki:
:root {
    --accent:    #2563EB;   // przełącz na niebieski
    --accent-bg: #EFF6FF;
}

// Dodaj własne style komponentów poniżej
.my-custom-hero {
    background: var(--accent);
    color: #fff;
}
```

> **Ważne:** Przy użyciu `app-local` musisz także nadpisać `base.html.twig`, aby załadować oba entrypointy. Utwórz `local/templates/base.html.twig`:
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
> Przekazanie obu entrypointów do `importmap()` gwarantuje poprawną kolejność ładowania CSS: `app.css` (oryginał) najpierw, potem twoje nadpisywania.

> **Wskazówka:** Zawsze nadpisuj CSS custom properties (`--accent`, `--bg`, etc.) zamiast surowych wartości hex. Dzięki temu tryb jasny i ciemny będą działać poprawnie z kolorami Twojej marki. Pełną listę tokenów znajdziesz w [Design Reference](/pl/design-reference/).

## Własne serwisy PHP

Utwórz interfejs serwisu w `src/Service/`, zaimplementuj go, a Symfony wstrzykuje go automatycznie. Dla rozszerzeń site-specific umieść implementacje w `local/src/` z namespace `NotACms\Local\`:

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

Wstrzyknij do odpowiedniego kontrolera przez konstruktor — kontener DI Symfony załatwia resztę.

### Zaawansowane: nasłuchiwacze zdarzeń, filtry Twig, dekoratory serwisów

Oprócz podstawowych serwisów, możesz też tworzyć nasłuchiwacze zdarzeń, filtry Twig i dekoratory serwisów w `local/src/`:

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
    // nadpisz tylko metody, których potrzebujesz
}
```

## Konfiguracja Nginx

Dla deploymentów produkcyjnych umieść fragment konfiguracji Nginx w `local/docker/nginx/`. Każdy plik `.conf` w tym katalogu jest automatycznie dołączany wewnątrz bloku `server {}`.

Przy pierwszym wdrożeniu bootstrap kopiuje trzy pliki z `docs/demo/docker/nginx/`:

- **`redirects.conf`** — przekierowania SEO (www → non-www, stare URLe)
- **`error-pages.conf`** — strony błędów uwzględniające locale
- **`csp.conf`** — rozszerzona Content-Security-Policy dla zewnętrznych originów motywu demo (Phosphor Icons, Google Fonts)

```nginx
# local/docker/nginx/redirects.conf — przykład
location = /old-page/ { return 301 /new-page/; }
location ~ "^\d{4}/\d{2}/\d{2}/([a-z0-9-]+)/$" { return 301 /blog/$1/; }
```

Konfigurację można podzielić na wiele plików — wszystkie `*.conf` są dołączane. Użyj prefiksów z numerami, jeśli kolejność ma znaczenie (np. `10-redirects.conf`, `20-cache.conf`).

### Nadpisanie Content-Security-Policy

Szablon core emituje CSP bezpieczne dla motywu bare poprzez zmienną `$csp` ustawianą *przed* includem `local/docker/nginx/*.conf`, więc dowolny plik nadpisania może ją przedefiniować. Aby rozszerzyć policy dla własnych zewnętrznych assetów, utwórz plik typu `local/docker/nginx/csp.conf` z pojedynczą dyrektywą `set`:

```nginx
# local/docker/nginx/csp.conf — rozszerza CSP o zewnętrzne originy
set $csp "default-src 'self'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' data: cdn.example.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data:; frame-src 'self'; connect-src 'self';";
```

Nie dodawaj drugiego `add_header Content-Security-Policy` — przeglądarki biorą część wspólną wielu nagłówków CSP (bardziej restrykcyjne, nie szersze).

## Nadpisywanie tłumaczeń

Utwórz `local/translations/messages.en.yaml` i/lub `messages.pl.yaml`, `messages.de.yaml` z tylko kluczami, które chcesz zmienić. Są scalane z plikami tłumaczeń rdzenia, więc pominięte klucze zachowują swoją oryginalną wartość.

```yaml
# local/translations/messages.pl.yaml
nav:
  blog: "Artykuły"

footer:
  copyright: "Wszelkie prawa zastrzeżone — Moja strona"
```

Możesz nadpisać dowolny klucz z `translations/messages.*.yaml`. Zagnieżdżone klucze używają tej samej wciętej struktury YAML co pliki oryginalne.

## Domyślny obraz OG

Obraz zastępczy `og:image` wyświetlany, gdy strona nie ma obrazka wyróżniającego, domyślnie używa markowego placeholdera. Aby go zastąpić, umieść swój obraz w `local/assets/images/og-default.jpg`. Szablon automatycznie odczytuje z tej ścieżki.

Przy pierwszym `./notACMS deploy` lub `ddev build` bootstrap kopiuje ten plik z domyślnego rdzenia, jeśli jeszcze nie istnieje. Zastąp skopiowany plik własnym.

Dla zupełnie innego podejścia (inny format, wymiary lub logika), nadpisz blok `{% block og_default_image %}` w `local/templates/base.html.twig`:

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

## Lokalna dokumentacja

`local/docs/` zawiera dwa pliki referencyjne site-specific, oba wykluczone z gita i tworzone przy pierwszym wdrożeniu:

**`local/docs/EDITOR_GUIDE.md`** — przewodnik po pisaniu dla twojej strony: kategorie, zatwierdzone tagi, głos i style generowania obrazów. Kopiowany z `docs/demo/docs/EDITOR_GUIDE.md`. Mechaniki treści systemowej (pola frontmatter, struktura URL, serie, szkice) pozostają w `docs/EDITOR_GUIDE.md`.

**`local/docs/STYLEGUIDE.md`** — referencja projektowa dla twojej strony: tokeny projektowe, paleta kolorów, typografia i lista komponentów. Kopiowany z `docs/demo/docs/STYLEGUIDE.md`. Mechaniki styleguide (strona dev `/styleguide/`, konwencje SCSS) pozostają w `docs/STYLEGUIDE.md`.

Oba pliki są swobodnie edytowalne po inicjalizacji — bootstrap nigdy ich nie nadpisuje.

## Rozszerzanie procesu budowania

Dodaj niestandardowe kroki budowania, tworząc komendę Symfony Console w `local/src/Command/`:

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

Potem dodaj do swojego aliasu `ddev build` lub wywołaj bezpośrednio z `ddev exec bin/console local:sitemap`.

## Gotowe przykłady

Katalog `docs/customization/` zawiera kompletne, gotowe do skopiowania przykłady. Każdy zawiera wszystkie niezbędne pliki i README z dokładnymi komendami `cp`.

| Przykład | Wzorzec | Co demonstruje |
|---|---|---|
| `custom-footer` | Pełna zamiana bazy | Własny footer — pokazuje jak dostosować prosty footer core |
| `custom-post-card` | Zamiana komponentu + SCSS | Pozioma karta posta — pokazuje `@base` extend, importmap `app-local` i nadpisanie komponentu |
| `self-hosted-fonts` | Pełna zamiana bazy + SCSS | Własne czcionki — pokazuje preload, `@font-face` i zastępowanie systemowych czcionek |
| `php-service-decorator` | PHP `#[AsDecorator]` | Pomijanie walidacji Turnstile — pokazuje namespace `NotACms\Local\` i wzorzec dekoratora |
| `twig-filter` | PHP `#[AsTwigFilter]` | Dodanie filtra `|excerpt` — pokazuje namespace `NotACms\Local\` i atrybut Twig |

---

## Dostosowywanie produkcyjne

### Zmienne środowiskowe

Utwórz `.env.local` w głównym katalogu projektu (wykluczony z gita):

```bash
# Wymagane
APP_SECRET=changeme-generate-new
URL=example.com

# Sieć
NGINX_PORT=8123
CERTRESOLVER=le  # lub 'dummy' do testowania

# Opcjonalne: Formularz kontaktowy
RUNTIME_PHP_ENABLED=true
MAILER_DSN=smtp://user:pass@smtp.example.com:587
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
```

### Nadpisywanie Docker Compose

Utwórz `docker-compose.override.yaml` dla lokalnych dostosowań:

```yaml
services:
  nginx:
    ports:
      - "8080:80"  # Nadpisz NGINX_PORT dla tej maszyny
```

### Nagłówki bezpieczeństwa

Dodaj do `local/docker/nginx/redirects.conf`:

```nginx
# Nagłówki bezpieczeństwa
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Cache statycznych assetów
location ~* \.(css|js|webp|ico|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### SSL / HTTPS z Traefik

Dołączony Docker Compose ma etykiety Traefik dla automatycznego SSL.

Wymagania:
- Sieć zewnętrzna: `docker network create web`
- Rekord DNS A wskazujący na IP serwera
- Porty 80/443 otwarte

`.env.local`:
```bash
URL=yourdomain.com
CERTRESOLVER=le  # Let's Encrypt
#CERTRESOLVER=dummy  # Self-signed do testowania
```

Dla własnej konfiguracji Traefik, dodaj etykiety do `docker-compose.override.yaml`.

### Niestandardowe kroki budowania

Dodaj komendy post-build edytując `scripts/rebuild-content.sh` lub tworząc wrapper:

```bash
#!/bin/bash
# custom-deploy.sh
./notACMS deploy --prod
# Custom: Sync do S3, purge CDN, etc.
aws s3 sync public/static/ s3://my-bucket/ --delete
```

### Uprawnienia plików

Sprawdź uprawnienia przed wdrożeniem produkcyjnym:

```bash
# Napraw uprawnienia przed deploy
sudo chown -R $USER:$USER .
chmod 755 local/docker/nginx/
```
