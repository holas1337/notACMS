---
title: "Theme-Entwicklung"
description: "Ein vollständiges Theme für notACMS erstellen — Template-Schichten, Twig-API, Kontext-Verträge und Portabilitätsregeln."
template: page/doc
slug: theme-entwicklung
menu:
  weight: 45
  label: "Theme-Entwicklung"
---

## Template-Schichten

notACMS löst Templates aus drei Schichten auf, angewendet nach Priorität:

1. **`local/templates/`** — site-spezifische Overrides (höchste Priorität)
2. **Theme-Templates** — registriert via `twig.paths` in `local/config/packages/twig.yaml`
3. **Core-Bare-Templates** in `templates/`

Jede Datei in `local/templates/` überschreibt das Äquivalent im registrierten Theme oder Core. Das Bare-Theme liefert einen minimalen, vollständigen Template-Satz, der alle Content-Typen ohne externe Abhängigkeiten rendert. Das Demo-Theme (diese Seite) fügt Styling und Komponenten hinzu.

## Template ↔ Kontext-Verträge

Jedes Template erhält die [Twig-Globals](#twig-globals) plus seitenspezifischen Kontext. `locale` ist immer vorhanden.

### Seiten-Templates

| Route | Template | Wichtiger Kontext |
|---|---|---|
| `home_{locale}` | `page/home.html.twig` | `content` (`?ContentItem`), `recentPosts` (`ContentItem[]`), `card_layouts` |
| `static_page_{locale}` | Frontmatter `template:` | `content` (`ContentItem`) |
| `contact_{locale}` | `page/contact.html.twig` | `content`, `form`, `contact_form_available` (`bool`) |
| `projects_{locale}` | `page/projects.html.twig` | `content`, `featured_projects`, `total_projects` |
| `search_{locale}` | `search/index.html.twig` | nur `locale` |
| Fehlerseiten | `page/error.html.twig` | `status_code`, `status_text`, `locale`, `content` (null) |

### Blog-Templates

| Route | Template | Wichtiger Kontext |
|---|---|---|
| Blog-Liste + paginiert | `blog/list.html.twig` | `posts`, `current_page`, `total_pages`, `total_posts`, `filter_type`, `filter_value`, `index_content`, `card_layouts` |
| Einzelner Post | `blog/post.html.twig` | `content`, `related_posts`, `prev_post`, `next_post`, `series_posts`, `series_current_part`, `card_layouts` |
| Geplanter Post | `page/coming-soon.html.twig` | `post`, `card_layouts` |

`filter_type`-Werte: `'category'`, `'tag'`, `'archive'`. `card_layouts` rotiert durch `['layout-top', 'layout-right', 'layout-text', 'layout-left']`.

### Feed- & E-Mail-Templates

| Template | Kontext |
|---|---|
| `feed/rss.xml.twig` | `posts` (`ContentItem[]`), `locale` |
| `feed/sitemap.xml.twig` | `items_by_locale` (`array<locale, ContentItem[]>`) |
| `feed/llms.txt.twig` | `posts_by_locale`, `pages` |
| `feed/robots.txt.twig` | nur Globals |
| `email/contact.html.twig` | `contact` (Array: `name`, `email`, `website`, `subject`, `message`), `locale` |

## Twig-Globals

In jedem Template verfügbar:

| Global | Typ | Quelle |
|---|---|---|
| `site_name`, `site_base_url`, `site_description` | `string` | `_site.yaml` |
| `site_social` | `array` | `_site.yaml` `social:` (Form ist site-definiert) |
| `site_author` | `array` | `_site.yaml` `author:` — immer mit `\|default()` absichern |
| `site_locales` | `array<locale, array>` | pro Locale: `label`, `og_locale`, `date_format`, … |
| `site_locales_list` | `string[]` | Locale-Codes, erster = Standard |
| `site_default_locale` | `string` | erster Schlüssel von `locales:` |
| `image_variant_widths` | `int[]` | `_site.yaml` (Standard `[640, 960]`) |
| `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` | `int` | `_site.yaml` |
| `translation_map` | `array<directoryKey, array<locale, url>>` | nur veröffentlichte Übersetzungen; Schlüssel sind vollständige relative Content-Pfade |
| `cf_analytics_token`, `turnstile_site_key`, `notacms_project_url` | `string` | `twig.yaml` Umgebungsvariablen |

## Twig-Funktionen & Filter

| Funktion / Filter | Signatur | Rückgabe |
|---|---|---|
| `content_item(key, locale)` | `(string, string): ?ContentItem` | Item nach Directory-Key — nullable, immer absichern |
| `content_url(key, locale)` | `(string, string): string` | Item-URL; `'/'` wenn nicht gefunden |
| `breadcrumbs(content, locale, options)` | `(?ContentItem, string, array): Breadcrumb[]` | `.label` und `.url` (null = aktuelle Seite) |
| `lang_switch_urls(other_locales)` | `(string[]): array<locale, url>` | Sprachwechsler-URLs |
| `og_image_url(content, site_base_url)` | `(?ContentItem, string): string` | Featured-Image-URL oder Standard-OG-Bild |
| `post_badge(post, new_post_days)` | `(ContentItem, int): ?string` | `'new'`, `'updated'` oder null |
| `sidebar_data(locale)` | `(string): SidebarData` | `.recentPosts`, `.categories`, `.tags`, `.archiveMonths` |
| `structured_data()` | `(): StructuredDataBuilderInterface` | Schema.org-Builder — `webSite()`, `blogPosting()`, `breadcrumbList()`, etc. |
| `json_ld(data)` | `(array): string` | `<script type="application/ld+json">`-Block |
| `srcset_media` (Filter) | `(string): string` | Fügt `srcset`/`sizes` in `<img>`-Tags ein — Standard-Body-Pipeline: `content.htmlContent\|srcset_media\|raw` |

## ContentItem-API

Methoden, die auf jedem `ContentItem` in Templates verfügbar sind:

```php
$item->title()         // string
$item->description()   // string
$item->slug()          // string — letztes Segment des Slug-Frontmatter
$item->url()           // string — voller Pfad inkl. Locale-Präfix
$item->date()          // ?DateTimeImmutable — vor |date() absichern
$item->updatedDate()   // ?DateTimeImmutable
$item->tags()          // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()      // string (Standard: title)
$item->excerpt()       // string
$item->readingTime()   // int
$item->wordCount()     // int
$item->menuLabel()     // string
$item->menuWeight()    // int
$item->series()        // ?string
$item->seriesOrder()   // ?int
$item->directoryKey()  // ?string — vollständiger relativer Content-Pfad
$item->isDraft()       // bool
$item->isScheduled()   // bool
$item->isPinned()      // bool
$item->isFeatured()    // bool
$item->hasToc()        // bool
$item->htmlContent     // string (public property) — gerendertes HTML
$item->locale          // string (public property)
```

## Erforderliche Übersetzungsschlüssel

Jeder Theme-Katalog muss diese Schlüssel in jeder Locale definieren:

| Schlüssel | Verwendet von |
|---|---|
| `blog.title` | Fallback von `blog_filter_title()` wenn keine Blog-Index-Seite existiert |
| `contact.form.error`, `contact.form.success` | JSON-Antworten der Kontakt-API |
| `contact.form.name`, `.email`, `.website`, `.subject`, `.message` | Formular-Feldbezeichnungen |
| `contact.form.unavailable` | Angezeigt wenn Kontaktformular nicht konfiguriert ist |

Schlüssel, die von Templates verwendet werden, die dein Theme nicht überschreibt, müssen ebenfalls in deinen Katalogen vorhanden sein — der Übersetzer greift nur für selbst nicht definierte Schlüssel auf Core-`translations/` zurück.

## Theme-Portabilitätsregeln

1. **`site_locales_list` iterieren** — niemals Locale-Codes hardcoden oder genau zwei Locales annehmen.
2. **Nullable-Lookups absichern** — `content_item()` gibt null zurück; `site_author.*`-Schlüssel sind site-definiert (`site_author.name|default(site_name)`); `content.date()` kann null sein.
3. **`directoryKey()` nicht mit Literalen vergleichen** außer du kontrollierst das Content-Layout — es gibt den vollständigen relativen Pfad zurück.
4. **Jeder `|trans`-Schlüssel in deinen Templates muss in jedem Locale-Katalog existieren** — fehlende Schlüssel werden als rohe Schlüssel-Strings gerendert.
5. **Keine URL-Pattern-Matching für Bereichserkennung** wenn Content-Metadaten die Frage beantworten können.
6. **Kein `|raw` auf Übersetzungskatalog-Output** — `|sanitize_html('app.trans_inline')` verwenden wenn ein Theme echtes Inline-Markup in übersetztem Text benötigt.

## Assets

Theme-Assets unter `local/assets/` werden als `asset('local/<file>')` referenziert. Core-Assets verwenden `asset('<file>')`. Content-Bilder werden unter `/media/{basename}/{filename}` ausgeliefert. Die responsive Bild-Pipeline ist nur WebP: Varianten-URLs sind `<base>-<width>w.webp` für jeden `image_variant_widths`-Eintrag.

Der vollständige API-Vertrag — einschließlich aller Kontextfelder, Value-Object-Formen und Randfallhinweise — wird in `docs/THEME_BUILDING.md` gepflegt. Breaking Changes sind in `UPGRADE-X.Y.md` dokumentiert.
