---
title: "Handbuch"
description: "Komplette Anleitung zur Installation, Konfiguration und Bereitstellung von notACMS."
template: page/doc
slug: manual
menu:
  weight: 20
  label: "Handbuch"
---

> ## Quick Start
> 
> ```bash
> # 1. Klonen
> git clone https://github.com/holas1337/notACMS my-site && cd my-site
> 
> # 2. Produktion konfigurieren
> echo "APP_SECRET=$(php -r 'echo bin2hex(random_bytes(32));')" >> .env.local
> echo "URL=yourdomain.com" >> .env.local
> > # 3. Deployen
> ./notACMS deploy --prod
> ```
> 
> Site läuft unter `https://yourdomain.com`. Content in `local/content/` bearbeiten.

---

## Installation (Entwicklung)

notACMS verwendet [DDEV](https://ddev.readthedocs.io/) für lokale Entwicklung.

**Voraussetzungen:** Docker Desktop (oder Orbstack auf Mac), DDEV 1.22+, Git.

```bash
git clone https://github.com/holas1337/notACMS my-site
cd my-site
ddev start
ddev composer install
ddev build
```

Site läuft unter `https://my-site.ddev.site`.

---

## Content-Struktur

```
local/content/
├── _site.yaml          # Site-Konfiguration
├── _routes.yaml        # URL-Überschreibungen
├── _tags.yaml          # Tag-Übersetzungen
├── pages/              # Statische Seiten
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

## Seiten-Frontmatter

| Feld | Typ | Erforderlich | Beschreibung |
|---|---|---|---|
| `title` | string | **Ja** | Seitentitel |
| `slug` | string | Ja | URL-Pfad |
| `description` | string | Nein | Meta-Beschreibung |
| `date` | date | Posts | Veröffentlichungsdatum |
| `updated` | date | Nein | Letzes Änderungsdatum |
| `category` | string | Posts | Kategorie-Slug |
| `tags` | list | Nein | Tag-Liste |
| `template` | string | Nein | Twig-Template |
| `image` | string | Nein | Pfad zum Vorschaubild |
| `image_alt` | string | Erforderlich mit `image` | Alt-Text des Bilds (Standardwert: Titel, wenn weggelassen) |
| `draft` | bool | Nein | In Produktion ausblenden |
| `pinned` | date oder false | Nein | Heftet den Post bis zu diesem Datum (inklusive) an den Anfang aller Listen; wird automatisch entpinnt, sobald das Datum vorbei ist. Nur Posts. |
| `featured` | bool | Nein | Nur Projekte, Anzeige im kuratierten Grid |
| `dynamic` | bool | Nein | Statisches Pre-Rendering überspringen |
| `series` | string | Nein | Serien-Schlüssel |
| `series_order` | int | Nein | Position innerhalb der Serie |
| `toc` | bool | Nein | Inhaltsverzeichnis anzeigen |
| `related` | list | Nein | Manuelle verwandte Post-Slugs |
| `menu` | object | Nein | Nav-Konfiguration: `weight` (Sortierreihenfolge) und `label` (Überschreibung) |

---

## Bilder

Bilder im `files/`-Unterverzeichnis neben dem Content platzieren:

```
blog/releases/my-post/
├── en.md
└── files/
    └── featured.webp
```

Im Frontmatter referenzieren: `image: /media/my-post/featured.webp`

> **Tipp:** Alle Bilder müssen WebP sein. ImageMagick verwenden: `ddev exec convert input.jpg -quality 82 -strip -define webp:method=6 output.webp`

---

## Build-Befehle

| Befehl | Zweck |
|--------|-------|
| `ddev build` | Voller Dev-Build: Cache + Assets + Seiten + Suche |
| `ddev code-check` | PHPStan + PHP CS Fixer |
| `ddev code-fix` | Code-Style automatisch fixen |
| `./notACMS deploy --prod` | Produktions-Deploy (Docker + statischer Build) |
| `./notACMS rebuild --prod` | Nur Content-Update (schnell, kein Docker-Rebuild) |

---

## Produktions-Deployment

### Voraussetzungen

- Docker + Docker Compose
- Domain mit DNS A-Record, der auf Server zeigt
- Ports 80/443 verfügbar (oder custom via `NGINX_PORT`)

### Erstes Deploy

```bash
./notACMS deploy --prod
```

Was passiert:
1. Seedet `local/content/` aus `docs/demo/content/` (falls leer)
2. Baut PHP-Docker-Image
3. Installiert Composer-Deps (`--no-dev` in prod)
4. Kompiliert SCSS + Assets
5. Generiert statisches HTML → `public/static/`
6. Baut Pagefind-Suchindex → `public/pagefind/`
7. Startet nginx-Container

### Content-Updates

Nach dem Bearbeiten von Markdown-Dateien:

```bash
./notACMS rebuild --prod
```

Überspringt Docker-Rebuild — regeneriert nur HTML + Suchindex (~10s statt ~2-3min).

### Container-Befehle

| Befehl | Zweck |
|--------|-------|
| `./notACMS deploy up` | Container starten (kein Rebuild) |
| `./notACMS deploy down` | Container stoppen |
| `./notACMS deploy restart` | Neustart ohne Rebuild |

---

## Umgebungsvariablen

`.env.local` erstellen (niemals committed, gitignored):

| Variable | Erforderlich | Setzen via | Zweck |
|----------|--------------|------------|-------|
| `APP_SECRET` | **Ja** | `php -r "echo bin2hex(random_bytes(32));"` | Symfony Secret |
| `URL` | **Ja** | `yourdomain.com` | Muss `base_url` in `_site.yaml` entsprechen |
| `NGINX_PORT` | Nein | `8123` | Host-Port (Default: 8123) |
| `RUNTIME_PHP_ENABLED` | Nein | `true`/`false` | PHP für Kontaktformular aktivieren |
| `MAILER_DSN` | Nein* | `smtp://user:pass@host:587` | *Erforderlich wenn Kontaktformular aktiv |
| `TURNSTILE_SITE_KEY` | Nein | Cloudflare Key | CAPTCHA (optional) |
| `TURNSTILE_SECRET_KEY` | Nein | Cloudflare Secret | CAPTCHA (optional) |
| `CERTRESOLVER` | Nein | `le` oder `dummy` | SSL-Resolver (Default: `le`) |
| `UID` / `GID` | Nein | `1000` | Host-User matchen (`id`) |

---

## Fehlerbehebung

### Port bereits in Verwendung

**Fehler:** `bind: address already in use`  
**Fix:**
```bash
echo "NGINX_PORT=8080" >> .env.local
./notACMS deploy --prod
```

### Berechtigung verweigert

**Fehler:** 403 bei statischen Dateien  
**Fix:**
```bash
echo "UID=$(id -u)" >> .env.local
echo "GID=$(id -g)" >> .env.local
./notACMS deploy restart
```

### Kontaktformular 404

**Fix:** PHP-Runtime aktivieren:
```bash
echo "RUNTIME_PHP_ENABLED=true" >> .env.local
./notACMS deploy restart
```

### SSL-Zertifikat schlägt fehl

**Fehler:** Traefik kann Zertifikat nicht holen  
**Fix:** Prüfen ob Ports 80/443 offen sind, DNS auflöst. Oder `CERTRESOLVER=dummy` zum Testen verwenden.

### Styles werden nicht aktualisiert

**Fix:** Cache leeren und neu bauen:
```bash
rm -rf var/ public/assets/
./notACMS deploy --prod
```

### Build hängt

**Fix:** Sicherstellen, dass Docker genügend Speicher (4GB+) für Asset-Kompilierung hat.
