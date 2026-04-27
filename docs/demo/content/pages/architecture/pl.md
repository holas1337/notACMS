---
title: "Architektura"
description: "Jak działa notACMS wewnętrznie — pipeline treści, system routingu i proces budowania."
template: page/doc
slug: architecture
menu:
  weight: 30
  label: "Architektura"
---

## Pipeline treści

Każdy build przebiega według czteroetapowego pipeline:

```
1. sass:build         → kompiluj SCSS do CSS
2. asset-map:compile  → hashuj i kopiuj assety
3. app:build          → renderuj wszystkie strony HTML z treści + szablonów
4. pagefind            → zbuduj indeks wyszukiwania
```

Krok 3 jest sterowany przez `BuildStaticSiteCommand` w `src/Command/`, który skanuje treść, renderuje szablony, generuje responsywne obrazy i zapisuje statyczny HTML w `public/static/`. Krok 4 uruchamia `npx pagefind` jako osobny proces.

URL-e pochodzą z pola frontmatter `slug`, a nie ze ścieżek katalogów. Post w `blog/releases/release-1-0-0/en.md` z `slug: "blog/release-1-0-0"` generuje `/blog/release-1-0-0/`.

## System routingu

Trasy są generowane z drzewa treści. Każdy `ContentItem` ma URL zdefiniowany przez pole frontmatter `slug`.

`LocalizedRouteLoader` czyta `local/content/_routes.yaml` i rejestruje trasy Symfony dla każdej wersji językowej. Domyślna wersja językowa (EN) ma trasy bez prefiksu. Pozostałe wersje językowe otrzymują prefiks `/{locale}/`, chyba że nadpisano.

**Przykład generowania URL:**

| Plik treści | Slug frontmatter | Locale | Wygenerowany URL |
|---|---|---|---|
| `pages/about/en.md` | `about` | EN | `/about/` |
| `pages/about/de.md` | `about` | DE | `/de/about/` |
| `blog/releases/release-1-0-0/en.md` | `blog/release-1-0-0` | EN | `/blog/release-1-0-0/` |
| `blog/releases/release-1-0-0/pl.md` | `blog/release-1-0-0` | PL | `/wpisy/release-1-0-0/` |

## Serwisy rdzeniowe

### `ContentService`

Główna fasada do ładowania i zapytania o treść. Deleguje budowanie drzewa do `ContentTreeBuilder` i implementuje `ContentCacheInterface`, więc `invalidateCache($locale)` czyści zbuforowane drzewa, gdy treść się zmienia.

```php
$posts = $contentService->getPosts($locale, 1, 5);
$recent = $contentService->getRecentPosts($locale, 5);
$item  = $contentService->findByUrl('/about/', $locale);
```

### `ContentItem`

Reprezentuje pojedynczy element treści. Udostępnia metody dla pól frontmatter:

```php
$item->title()          // string
$item->slug()           // string — ostatni segment frontmatter slug (np. "my-post" z "blog/my-post")
$item->url()            // string (pełna ścieżka URL, np. /blog/my-post/)
$item->date()           // ?DateTimeImmutable
$item->updatedDate()    // ?DateTimeImmutable
$item->description()    // string
$item->tags()           // string[]
$item->category()      // ?string
$item->image()         // ?string
$item->imageAlt()       // string (domyślnie title)
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
$item->htmlContent      // wyrenderowany string HTML (właściwość public)
```

### `SiteConfigService`

Czyta `local/content/_site.yaml` i udostępnia ustawienia globalne jako serwis. Wstrzykiwany do kontrolerów i Twig globals przez `SiteConfigExtension`.

### `ContentTree`

Wewnętrzna reprezentacja hierarchii treści dla pojedynczej wersji językowej. Używana do nawigacji, obliczania prev/next i wykrywania serii. `ContentService` stanowi dla niej fasadę, więc te same nazwy metod wyszukiwania (`findByUrl`, `findPostBySlug`, `findScheduledPostBySlug`) istnieją w obu: wersja z serwisu przyjmuje `$locale` i deleguje do drzewa odpowiedniego locale.

Kluczowe metody:
- `getAdjacentPosts()` — zwraca value object `AdjacentPosts` z `->prev` i `->next`
- `getSeriesPosts()` — zwraca posty w tej samej serii posortowane wg `series_order`

### Pozostałe serwisy

| Serwis | Przeznaczenie |
|--------|---------------|
| `ImageResizer` | Nakładka na ImageMagick: `resize()` generuje warianty responsywne; `optimize()` rekompresuje w miejscu |
| `ResponsiveImageService` | Oblicza szerokości wariantów responsywnych i buduje wartości atrybutów `srcset` |
| `SrcsetExtension` | Filtr Twig `srcset_media` — przetwarza HTML, wstrzykując atrybuty `srcset`/`sizes` do tagów `<img>` |
| `MediaFileResolver` | Rozwiązuje ścieżki plików multimedialnych między locale |
| `RelatedPostsService` | Znajduje powiązane posty przez wspólne tagi i ręczne frontmatter `related` |
| `SessionToggleService` | Ogólny przełącznik logiczny oparty na sesji; współdzielona zależność obu serwisów podglądu poniżej |
| `DraftPreviewService` | Zarządza podglądem draftów treści za pomocą przełączników sesji |
| `ScheduledPreviewService` | Zarządza podglądem zaplanowanych, przyszłych postów |
| `TranslationMapBuilder` | Buduje mapy tłumaczeń URL między locale dla przełącznika języka |
| `ContactFormConfig` | Dostarcza konfigurację formularza kontaktowego (odbiorca, Turnstile, itp.) |
| `MarkdownParser` | Konwertuje Markdown do HTML przez CommonMark |
| `SidebarDataProvider` | Składa dane paska bocznego (kategorie, tagi, ostatnie posty) |
| `TagTranslationService` | Tłumaczy slugi tagów między locale |

### Rozszerzenia Twig

Pięć rozszerzeń Twig udostępnia dane w szablonach:

| Rozszerzenie | Typ | Udostępnia |
|---|---|---|
| `SiteConfigExtension` | Globals | `site_name`, `site_base_url`, `site_description`, `site_social`, `site_author`, `site_locales`, `site_locales_list`, `site_default_locale`, `image_variant_widths`, `new_post_days`, `coming_soon_reveal_days`, `meta_description_length` |
| `TranslationMapTwigExtension` | Global | `translation_map` — mapowanie `{directoryKey: {locale: url}}` dla przełącznika języków i tagów hreflang |
| `ContentTwigExtension` | Funkcja | `content_url(directoryKey, locale)` — rozwiązuje URL dla elementu treści po kluczu katalogu |
| `LangSwitcherExtension` | Funkcja | `lang_switch_urls(otherLocales)` — rozwiązuje URL przełącznika języków dla każdego locale: mapa tłumaczeń, override kontrolera, następnie fallbacki (archiwum → paginacja → lista wpisów → strona główna) |
| `SrcsetExtension` | Filter | `srcset_media` — przetwarza HTML, wstrzykując atrybuty `srcset`/`sizes` do tagów `<img>` w celu responsywności |

Dodatkowo, `cf_analytics_token` jest rejestrowany jako Twig global w `config/packages/twig.yaml`, powiązany ze zmienną środowiskową `CF_ANALYTICS_TOKEN`.

## Przepływ żądań (tryb na żywo)

```
Przeglądarka → nginx → PHP-FPM → Symfony Kernel → LocaleListener → Router
             → Controller → ContentService → Twig → HTML-Response
            → Twig-Renderer (zastosuj szablon)
            → HTML-Response
```

Kontrolery są lekkie. Cała logika biznesowa znajduje się w serwisach.

## Strategia testowania

```bash
ddev test           # Uruchom pełny zestaw testów
ddev code-check     # PHPStan poziom 6 + PHP CS Fixer
```

- **Setki metod testowych** w zestawach integracyjnych i jednostkowych.
- **PHPStan:** Analiza statyczna na poziomie 6. Wszystkie serwisy są w pełni otypowane.
- **PHP CS Fixer:** Wymusza PSR-12 + standardy kodowania Symfony.
- **Testy integracyjne:** Obejmują routing, ładowanie treści, kontrolery, output builda, przetwarzanie obrazów, serwisy podglądu i rozszerzenia Twig.
- **Testy jednostkowe:** Obejmują `ContentItem`, `ContentTree`, obiekty wartości, `RelatedPostsService`, `TagTranslationService` i `ResponsiveImageService`.

Zobacz `docs/TESTING.md` po szczegóły.

## Dodawanie funkcjonalności

notACMS stosuje wzorzec kontenera serwisów Symfony. Aby dodać nowy typ treści lub krok budowania:

1. Utwórz interfejs w `src/Service/` (dla wkładu w core) lub `local/src/` (dla rozszerzeń specyficznych dla strony)
2. Utwórz klasę implementacji w tym samym katalogu
3. Symfony auto-discovery i autowiring — ręczna rejestracja w `services.yaml` nie jest potrzebna
4. Wstrzyknij do odpowiedniego kontrolera lub komendy
5. Nadpisz szablony w `local/templates/` jeśli potrzeba

> **Wskazówka:** Wszystkie serwisy to najpierw interfejsy. To ułatwia zamianę implementacji — na przykład zamianę lokalnego loadera filesystem na źródło S3.

## Runtime PHP (opcjonalny)

Domyślnie zbudowana strona jest czysto statyczna — Nginx serwuje `public/static/` bezpośrednio i PHP nie jest zaangażowany.

Konfiguracja Docker produkcyjnego w `local/docker/` zawiera opcjonalny kontener PHP. Ustaw `RUNTIME_PHP_ENABLED=true` w swoim środowisku produkcyjnym, aby go uruchomić. Po włączeniu, Nginx kieruje żądania `/api/` (i `/{locale}/api/`) do kontenera PHP — reszta nadal jest serwowana jako pliki statyczne.

Domyślny przypadek użycia to **formularz kontaktowy** (`ContactController` przez SMTP), dostępny pod `/api/contact`.

> **Uwaga:** Lokalny development (`ddev start`) uruchamia pełną aplikację Symfony dynamicznie. To jest dev-only wygoda — nie jest częścią produkcyjnego runtime PHP.

---

## Architektura wdrożenia

### Obsługa statyczna (static-first)

Nginx serwuje wstępnie zbudowane pliki bezpośrednio z `public/static/`. PHP jest zaangażowany tylko dla:
- `/api/contact` — jeśli `RUNTIME_PHP_ENABLED=true`
- Trybu deweloperskiego (`ddev start`)

Przepływ ruchu produkcyjnego:
```
Przeglądarka → Nginx → public/static/ (HTML, CSS, JS)
                    → Proxy do kontenera PHP (tylko dla /api/*)
```

### Budowanie vs runtime

| Faza | Komenda | Wynik | Czas trwania |
|------|---------|--------|--------------|
| 1. Kompilacja | `sass:build` | `public/assets/app-<hash>.css` | ~2s |
| 2. Assety | `asset-map:compile` | Zahashowane kopie assetów w `public/assets/` | ~1s |
| 3. Build | `app:build` (BuildStaticSiteCommand) | `public/static/**/*.html` | ~5s |
| 4. Indeks | `pagefind` | `public/pagefind/` indeks wyszukiwania | ~3s |

Całkowite pierwsze wdrożenie: ~2-3 minuty (Docker build)
Aktualizacja treści: ~10 sekund

### Profile kontenerów

| Profil | Kontenery | Przypadek użycia |
|----------|-----------|------------------|
| (none) | tylko nginx | Strony statyczne, bez formularza kontaktowego |
| `--profile runtime` | nginx + php | Formularz kontaktowy włączony |

Profile przełączane przez ustawienie `RUNTIME_PHP_ENABLED` w `.env.local`.
