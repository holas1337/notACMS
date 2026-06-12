---
title: "Budowanie Motywu"
description: "Budowanie pełnego motywu dla notACMS — warstwy szablonów, API Twig, kontrakty kontekstu i zasady przenośności."
template: page/doc
slug: budowanie-motywu
menu:
  weight: 45
  label: "Budowanie Motywu"
---

## Hierarchia Warstw Szablonów

notACMS rozwiązuje szablony z trzech warstw, stosowanych według priorytetu:

1. **`local/templates/`** — nadpisania specyficzne dla strony (najwyższy priorytet)
2. **Szablony motywu** — rejestrowane przez `twig.paths` w `local/config/packages/twig.yaml`
3. **Szablony bare core** w `templates/`

Każdy plik w `local/templates/` nadpisuje odpowiednik w zarejestrowanym motywie lub core. Motyw bare dostarcza minimalny, kompletny zestaw szablonów renderujący wszystkie typy treści bez zewnętrznych zależności. Motyw demo (ta strona) dodaje stylowanie i komponenty na wierzch.

## Kontrakty Szablon ↔ Kontekst

Każdy szablon otrzymuje [globalne Twig](#globalne-twig) plus kontekst specyficzny dla strony. `locale` jest zawsze obecny.

### Szablony Stron

| Route | Szablon | Kluczowy kontekst |
|---|---|---|
| `home_{locale}` | `page/home.html.twig` | `content` (`?ContentItem`), `recentPosts` (`ContentItem[]`), `card_layouts` |
| `static_page_{locale}` | frontmatter `template:` | `content` (`ContentItem`) |
| `contact_{locale}` | `page/contact.html.twig` | `content`, `form`, `contact_form_available` (`bool`) |
| `projects_{locale}` | `page/projects.html.twig` | `content`, `featured_projects`, `total_projects` |
| `search_{locale}` | `search/index.html.twig` | tylko `locale` |
| strony błędów | `page/error.html.twig` | `status_code`, `status_text`, `locale`, `content` (null) |

### Szablony Bloga

| Route | Szablon | Kluczowy kontekst |
|---|---|---|
| lista bloga + paginacja | `blog/list.html.twig` | `posts`, `current_page`, `total_pages`, `total_posts`, `filter_type`, `filter_value`, `index_content`, `card_layouts` |
| pojedynczy wpis | `blog/post.html.twig` | `content`, `related_posts`, `prev_post`, `next_post`, `series_posts`, `series_current_part`, `card_layouts` |
| zaplanowany wpis | `page/coming-soon.html.twig` | `post`, `card_layouts` |

Wartości `filter_type`: `'category'`, `'tag'`, `'archive'`. `card_layouts` rotuje przez `['layout-top', 'layout-right', 'layout-text', 'layout-left']`.

### Szablony Feedów i E-mail

| Szablon | Kontekst |
|---|---|
| `feed/rss.xml.twig` | `posts` (`ContentItem[]`), `locale` |
| `feed/sitemap.xml.twig` | `items_by_locale` (`array<locale, ContentItem[]>`) |
| `feed/llms.txt.twig` | `posts_by_locale`, `pages` |
| `feed/robots.txt.twig` | tylko globalne |
| `email/contact.html.twig` | `contact` (tablica: `name`, `email`, `website`, `subject`, `message`), `locale` |

## Globalne Twig

Dostępne w każdym szablonie:

| Zmienna globalna | Typ | Źródło |
|---|---|---|
| `site_name`, `site_base_url`, `site_description` | `string` | `_site.yaml` |
| `site_social` | `array` | `_site.yaml` `social:` (format zdefiniowany przez stronę) |
| `site_author` | `array` | `_site.yaml` `author:` — zawsze zabezpiecz przez `\|default()` |
| `site_locales` | `array<locale, array>` | dla każdego locale: `label`, `og_locale`, `date_format`, … |
| `site_locales_list` | `string[]` | kody locale, pierwszy = domyślny |
| `site_default_locale` | `string` | pierwszy klucz `locales:` |
| `image_variant_widths` | `int[]` | `_site.yaml` (domyślnie `[640, 960]`) |
| `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` | `int` | `_site.yaml` |
| `translation_map` | `array<directoryKey, array<locale, url>>` | tylko opublikowane tłumaczenia; klucze to pełne relatywne ścieżki treści |
| `cf_analytics_token`, `turnstile_site_key`, `notacms_project_url` | `string` | zmienne środowiskowe `twig.yaml` |

## Funkcje i Filtry Twig

| Funkcja / filtr | Sygnatura | Zwraca |
|---|---|---|
| `content_item(key, locale)` | `(string, string): ?ContentItem` | Element po kluczu katalogu — nullable, zawsze zabezpiecz |
| `content_url(key, locale)` | `(string, string): string` | URL elementu; `'/'` gdy nie znaleziono |
| `breadcrumbs(content, locale, options)` | `(?ContentItem, string, array): Breadcrumb[]` | `.label` i `.url` (null = bieżąca strona) |
| `lang_switch_urls(other_locales)` | `(string[]): array<locale, url>` | URL-e przełącznika językowego |
| `og_image_url(content, site_base_url)` | `(?ContentItem, string): string` | URL wyróżnionego obrazu lub domyślny obraz OG |
| `post_badge(post, new_post_days)` | `(ContentItem, int): ?string` | `'new'`, `'updated'` lub null |
| `sidebar_data(locale)` | `(string): SidebarData` | `.recentPosts`, `.categories`, `.tags`, `.archiveMonths` |
| `structured_data()` | `(): StructuredDataBuilderInterface` | Builder Schema.org — `webSite()`, `blogPosting()`, `breadcrumbList()`, itd. |
| `json_ld(data)` | `(array): string` | Blok `<script type="application/ld+json">` |
| `srcset_media` (filtr) | `(string): string` | Wstrzykuje `srcset`/`sizes` do tagów `<img>` — standardowy pipeline treści: `content.htmlContent\|srcset_media\|raw` |

## API ContentItem

Metody dostępne na każdym `ContentItem` w szablonach:

```php
$item->title()         // string
$item->description()   // string
$item->slug()          // string — ostatni segment frontmatter slug
$item->url()           // string — pełna ścieżka wraz z prefiksem locale
$item->date()          // ?DateTimeImmutable — zabezpiecz przed |date()
$item->updatedDate()   // ?DateTimeImmutable
$item->tags()          // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()      // string (domyślnie title)
$item->excerpt()       // string
$item->readingTime()   // int
$item->wordCount()     // int
$item->menuLabel()     // string
$item->menuWeight()    // int
$item->series()        // ?string
$item->seriesOrder()   // ?int
$item->directoryKey()  // ?string — pełna relatywna ścieżka treści
$item->isDraft()       // bool
$item->isScheduled()   // bool
$item->isPinned()      // bool
$item->isFeatured()    // bool
$item->hasToc()        // bool
$item->htmlContent     // string (właściwość public) — wyrenderowany HTML
$item->locale          // string (właściwość public)
```

## Wymagane Klucze Tłumaczeń

Każdy katalog motywu musi definiować te klucze w każdym locale:

| Klucz | Używany przez |
|---|---|
| `blog.title` | Fallback `blog_filter_title()` gdy nie istnieje strona indeksu bloga |
| `contact.form.error`, `contact.form.success` | Odpowiedzi JSON API kontaktowego |
| `contact.form.name`, `.email`, `.website`, `.subject`, `.message` | Etykiety pól formularza |
| `contact.form.unavailable` | Wyświetlane gdy formularz kontaktowy nie jest skonfigurowany |

Klucze używane przez szablony, których motyw nie nadpisuje, muszą również istnieć w katalogach — tłumacz sięga do core `translations/` tylko dla kluczy, których sam nie definiujesz.

## Zasady Przenośności Motywu

1. **Iteruj po `site_locales_list`** — nigdy nie koduj na stałe kodów locale ani nie zakładaj dokładnie dwóch locale.
2. **Zabezpieczaj nullable lookups** — `content_item()` zwraca null; klucze `site_author.*` są zdefiniowane przez stronę (`site_author.name|default(site_name)`); `content.date()` może być null.
3. **Nie porównuj `directoryKey()` do literałów** chyba że kontrolujesz układ treści — zwraca pełną relatywną ścieżkę.
4. **Każdy klucz `|trans` w szablonach musi istnieć w każdym katalogu locale, który dostarczasz** — brakujące klucze renderowane są jako surowe ciągi kluczy.
5. **Nie używaj dopasowywania wzorców URL do wykrywania sekcji** gdy metadane treści mogą odpowiedzieć na to pytanie.
6. **Brak `|raw` na wyjściu katalogu tłumaczeń** — używaj `|sanitize_html('app.trans_inline')` gdy motyw naprawdę potrzebuje inline markup w tłumaczonym tekście.

## Assety

Assety motywu w `local/assets/` są referencjonowane jako `asset('local/<file>')`. Assety core używają `asset('<file>')`. Obrazy treści są serwowane pod `/media/{basename}/{filename}`. Pipeline obrazów responsywnych jest wyłącznie WebP: URL-e wariantów to `<base>-<width>w.webp` dla każdego wpisu `image_variant_widths`.

Pełny kontrakt API — w tym wszystkie pola kontekstu, kształty obiektów wartości i uwagi o przypadkach brzegowych — jest utrzymywany w `docs/THEME_BUILDING.md`. Zmiany niekompatybilne wstecz są dokumentowane w `UPGRADE-X.Y.md`.
