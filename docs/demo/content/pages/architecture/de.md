---
title: "Architektur"
description: "Wie notACMS intern funktioniert — Inhalts-Pipeline, Routing-System und Build-Prozess."
template: page/doc
slug: architecture
menu:
  weight: 30
  label: "Architektur"
---

## Inhalts-Pipeline

Jeder Build folgt einer vierstufigen Pipeline:

```
1. sass:build         → SCSS zu CSS kompilieren
2. asset-map:compile  → Assets hashen und kopieren
3. app:build          → alle HTML-Seiten aus Inhalten + Templates rendern
4. pagefind            → Suchindex erstellen
```

Schritt 3 wird von `BuildStaticSiteCommand` in `src/Command/` gesteuert, das Inhalte scannt, Templates rendert, responsive Bilder generiert und statisches HTML in `public/static/` schreibt. Schritt 4 führt `npx pagefind` als separaten Prozess aus.

URLs werden aus dem `slug`-Frontmatter-Feld abgeleitet, nicht aus Verzeichnispfaden. Ein Post in `blog/releases/release-1-0-0/en.md` mit `slug: "blog/release-1-0-0"` erzeugt `/blog/release-1-0-0/`.

## Routing-System

Routen werden aus dem Inhaltsbaum generiert. Jedes `ContentItem` hat eine URL, die durch sein `slug`-Frontmatter-Feld definiert wird.

Der `LocalizedRouteLoader` liest `local/content/_routes.yaml` und registriert Symfony-Routen für jede Locale. Die Default-Locale (EN) hat keine Präfixe. Andere Locales bekommen ein `/{locale}/`-Präfix, sofern nicht überschrieben.

**Beispiel URL-Generierung:**

| Inhaltsdatei | Slug-Frontmatter | Locale | Generierte URL |
|---|---|---|---|
| `pages/about/en.md` | `about` | EN | `/about/` |
| `pages/about/de.md` | `about` | DE | `/de/about/` |
| `blog/releases/release-1-0-0/en.md` | `blog/release-1-0-0` | EN | `/blog/release-1-0-0/` |
| `blog/releases/release-1-0-0/pl.md` | `blog/release-1-0-0` | PL | `/wpisy/release-1-0-0/` |

## Core Services

### `ContentService`

Die Haupt-Fassade zum Laden und Abfragen von Inhalten. Delegiert Tree-Aufbau an `ContentTreeBuilder` und implementiert `ContentCacheInterface`, sodass `invalidateCache($locale)` gecachte Trees verwirft, wenn sich die Inhalte ändern.

```php
$posts = $contentService->getPosts($locale, 1, 5);
$recent = $contentService->getRecentPosts($locale, 5);
$item  = $contentService->findByUrl('/about/', $locale);
```

### `ContentItem`

Repräsentiert ein einzelnes Inhaltselement. Exponiert Methoden für Frontmatter-Felder:

```php
$item->title()          // string
$item->slug()           // string — letztes Segment des Slug-Frontmatter (z.B. "my-post" aus "blog/my-post")
$item->url()            // string (voller URL-Pfad, z.B. /blog/my-post/)
$item->date()           // ?DateTimeImmutable
$item->updatedDate()    // ?DateTimeImmutable
$item->description()    // string
$item->tags()           // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()       // string (Standardwert: title)
$item->template()       // string
$item->isDraft()        // bool
$item->isScheduled()    // bool
$item->isPinned()       // bool
$item->isDynamic()      // bool
$item->isFeatured()     // bool
$item->hasToc()         // bool
$item->series()         // ?string
$item->seriesOrder()    // ?int
$item->directoryKey()   // ?string
$item->relatedSlugs()   // string[]
$item->menuWeight()     // int
$item->isIndex()       // bool
$item->readingTime()    // int
$item->wordCount()      // int
$item->excerpt()        // string
$item->htmlContent      // gerendeter HTML-String (public property)
```

### `SiteConfigService`

Liest `local/content/_site.yaml` und exponiert Site-weite Einstellungen als Service. Wird über `SiteConfigExtension` in Controller und Twig-Globals injiziert.

### `ContentTree`

Interne Repräsentation der Inhaltshierarchie für ein einzelnes Locale. Wird für Navigation, Vor-/Zurück-Berechnung und Series-Erkennung verwendet. `ContentService` umschließt sie als Fassade, daher existieren dieselben Lookup-Namen (`findByUrl`, `findPostBySlug`, `findScheduledPostBySlug`) auf beiden Ebenen: die Service-Variante nimmt `$locale` entgegen und delegiert an den Tree des entsprechenden Locale.

Wichtige Methoden:
- `getAdjacentPosts()` — gibt das Value Object `AdjacentPosts` mit `->prev` und `->next` zurück
- `getSeriesPosts()` — gibt Posts derselben Series zurück, sortiert nach `series_order`

### Weitere Services

| Service | Zweck |
|---------|-------|
| `ImageResizer` | ImageMagick-Wrapper: `resize()` erzeugt responsive Varianten; `optimize()` recomprimiert in-place |
| `ResponsiveImageService` | Berechnet responsive Variantenbreiten und erstellt `srcset`-Attributwerte |
| `SrcsetExtension` | Twig-Filter `srcset_media` — post-processiert HTML um `srcset`/`sizes`-Attribute in `<img>`-Tags einzufügen |
| `MediaFileResolver` | Löst Mediendateipfade über Locales hinweg auf |
| `RelatedPostsService` | Findet verwandte Posts über gemeinsame Tags und manuelles `related`-Frontmatter |
| `SessionToggleService` | Generischer session-basierter Boolean-Toggle; gemeinsame Abhängigkeit beider Preview-Services unten |
| `DraftPreviewService` | Verwaltet Draft-Content-Vorschau mit session-basierten Toggles |
| `ScheduledPreviewService` | Verwaltet Vorschau für geplante, zukünftige Posts |
| `TranslationMapBuilder` | Erstellt URL-Übersetzungskarten über Locales für den Sprachwechsler |
| `ContactFormConfig` | Stellt Kontaktformular-Konfiguration bereit (Empfänger, Turnstile, etc.) |
| `MarkdownParser` | Konvertiert Markdown zu HTML via CommonMark |
| `SidebarDataProvider` | Stellt Sidebar-Daten zusammen (Kategorien, Tags, letzte Posts) |
| `TagTranslationService` | Übersetzt Tag-Slugs zwischen Locales |

### Twig-Erweiterungen

Fünf Twig-Erweiterungen stellen Daten für Templates bereit:

| Erweiterung | Typ | Stellt bereit |
|---|---|---|
| `SiteConfigExtension` | Globals | `site_name`, `site_base_url`, `site_description`, `site_social`, `site_author`, `site_locales`, `site_locales_list`, `site_default_locale`, `image_variant_widths`, `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` |
| `TranslationMapTwigExtension` | Global | `translation_map` — `{directoryKey: {locale: url}}` Zuordnung für Sprachwechsler und hreflang-Tags |
| `ContentTwigExtension` | Funktion | `content_url(directoryKey, locale)` — löst URL für ein Content-Element nach Directory-Key auf |
| `LangSwitcherExtension` | Funktion | `lang_switch_urls(otherLocales)` — löst Sprachwechsler-URLs pro Locale auf: Translation-Map, Controller-Override, dann Fallbacks (Archiv → paginiert → Blog-Liste → Startseite) |
| `SrcsetExtension` | Filter | `srcset_media` — fügt `srcset`/`sizes`-Attribute in `<img>`-Tags ein für responsive Bilder |

Zusätzlich wird `cf_analytics_token` als Twig-Global in `config/packages/twig.yaml` registriert, gebunden an die `CF_ANALYTICS_TOKEN`-Umgebungsvariable.

## Request Flow (Live Mode)

```
Browser → nginx → PHP-FPM → Symfony Kernel → LocaleListener → Router
           → Controller → ContentService → Twig → HTML-Response
```

Controller sind dünn. Alle Geschäftslogik liegt in Services.

## Test-Strategie

```bash
ddev test           # Gesamte Test-Suite ausführen
ddev code-check     # PHPStan Level 6 + PHP CS Fixer
```

- **Hunderte von Testmethoden** in Integrations- und Unit-Suites.
- **PHPStan:** Static Analysis auf Level 6. Alle Services sind vollständig getypt.
- **PHP CS Fixer:** Erzwingt PSR-12 + Symfony Coding Standards.
- **Integrationstests:** Decken Routing, Laden von Inhalten, Controller, Build-Output, Bildverarbeitung, Preview-Services und Twig-Extensions ab.
- **Unit-Tests:** Decken `ContentItem`, `ContentTree`, Value Objects, `RelatedPostsService`, `TagTranslationService` und `ResponsiveImageService` ab.

Siehe `docs/TESTING.md` für Details.

## Features hinzufügen

notACMS folgt dem Symfony Service-Container-Pattern. Um einen neuen Inhaltstyp oder Build-Schritt hinzuzufügen:

1. Interface in `src/Service/` erstellen (für Core-Beiträge) oder `local/src/` (für Site-spezifische Erweiterungen)
2. Implementierungsklasse im selben Verzeichnis erstellen
3. Symfony auto-discovered und autowired automatisch — keine manuelle `services.yaml`-Registrierung nötig
4. In den relevanten Controller oder Command injizieren
5. Templates in `local/templates/` überschreiben, falls nötig

> **Tipp:** Alle Core-Services nutzen Interfaces. Das macht es einfach, Implementierungen zu tauschen — zum Beispiel den lokalen Filesystem-Loader gegen eine S3-Quelle.

## PHP Runtime (Optional)

Standardmäßig ist die gebaute Site rein statisch — Nginx liefert `public/static/` direkt aus und PHP ist nicht involviert.

Das Produktions-Docker-Setup in `local/docker/` enthält einen optionalen PHP-Container. Setze `RUNTIME_PHP_ENABLED=true` in deiner Produktionsumgebung, um ihn zu starten. Wenn aktiviert, leitet Nginx `/api/` (und `/{locale}/api/`) an den PHP-Container weiter — alles andere wird weiterhin als statische Datei ausgeliefert.

Der Standard-Use-Case ist das **Kontaktformular** (`ContactController` via SMTP), das unter `/api/contact` erreichbar ist.

> **Hinweis:** Lokale Entwicklung (`ddev start`) führt die volle Symfony-Anwendung dynamisch aus. Das ist ein Dev-only-Komfort-Feature — es ist nicht Teil der Produktions-PHP-Runtime.

---

## Deployment-Architektur

### Static-First Serving

Nginx serviert vorgebaute Dateien direkt aus `public/static/`. PHP ist nur involviert für:
- `/api/contact` — wenn `RUNTIME_PHP_ENABLED=true`
- Entwicklungsmodus (`ddev start`)

Produktions-Datenfluss:
```
Browser → Nginx → public/static/ (HTML, CSS, JS)
                → Proxy zum PHP-Container (nur für /api/*)
```

### Build vs Runtime

| Phase | Kommando | Output | Dauer |
|-------|----------|--------|-------|
| 1. Kompilieren | `sass:build` | `public/assets/app-<hash>.css` | ~2s |
| 2. Assets | `asset-map:compile` | Gehashte Asset-Kopien in `public/assets/` | ~1s |
| 3. Build | `app:build` (BuildStaticSiteCommand) | `public/static/**/*.html` | ~5s |
| 4. Index | `pagefind` | `public/pagefind/` Suchindex | ~3s |

Gesamter erster Deploy: ~2-3 Minuten (Docker-Build)
Inhaltsaktualisierung: ~10 Sekunden

### Container-Profile

| Profil | Container | Anwendungsfall |
|----------|-----------|----------|
| (none) | nur nginx | Statische Sites, kein Kontaktformular |
| `--profile runtime` | nginx + php | Kontaktformular aktiviert |

Profile werden via `RUNTIME_PHP_ENABLED` in `.env.local` gewechselt.
