---
title: "Lokalizacja"
description: "Jak dodawać języki, tłumaczyć treści i konfigurować wzorce URL dla każdej wersji językowej."
template: page/doc
slug: locales
menu:
  weight: 50
  label: "Lokalizacja"
---

## Dodawanie nowego lokalu

Otwórz `local/content/_site.yaml` i dodaj wpis do mapy `locales`:

```yaml
site:
  locales:
    en:
      label: "English"
      og_locale: en_US
      date_format: "M d, Y"
      tagline: "static. simple. yours."
      # tagline_commands: ["git push", "ddev build"]  # opcjonalnie: wyświetlane w hero pod tagline
      # font_preload: true                                # opcjonalnie: wstępne ładowanie Inter + JetBrains Mono
    de:
      label: "Deutsch"
      og_locale: de_DE
      date_format: "d.m.Y"
      tagline: "statisch. einfach. deins."
    pl:                                 # ta strona demo zawiera też polski
      label: "Polski"
      og_locale: pl_PL
      date_format: "d.m.Y"
      tagline: "statyczny. prosty. twój."
    fr:                                 # oraz francuski
      label: "Français"
      og_locale: fr_FR
      date_format: "d/m/Y"
      tagline: "statique. simple. vôtre."
```

To wszystko. notACMS automatycznie:
- Doda `de` do przełącznika języków
- Wygeneruje tagi `hreflang` dla wszystkich stron z tłumaczeniem DE
- Poprzedzi adresy URL dla DE prefiksem `/de/` (lub użyje Twojej własnej ścieżki)

To samo dotyczy `fr`, `pl` lub dowolnej innej dodanej locale.

## Kolokacja treści

Tłumaczenia przechowywane są obok treści źródłowej. Każdy lokal to osobny plik Markdown w tym samym katalogu:

```
pages/about/
├── en.md    ← angielski
├── de.md    ← niemiecki
├── pl.md    ← polski
└── fr.md    ← francuski
```

Pole `slug` we frontmatter definiuje ścieżkę URL dla każdego lokalu. Jeśli jest puste (lub pominięte dla stron indeksowych), używany jest URL strony głównej (`/` dla domyślnego lokalu, `/{locale}/` dla innych). URLe zawsze pochodzą z pola frontmatter `slug` — nie ze ścieżek katalogów.

## Nadpisania URL

Domyślnie lokale inne niż domyślny poprzedzają angielską ścieżkę: `/de/about/`, `/pl/about/`, `/fr/about/`. Aby użyć całkowicie własnej ścieżki, dodaj wpis do `_routes.yaml`:

```yaml
# local/content/_routes.yaml
routes:
  blog_list:
    de: /beitraege/
    pl: /wpisy/
    fr: /articles/
  blog_list_paginated:
    de: /beitraege/seite/{page}/
    pl: /wpisy/strona/{page}/
    fr: /articles/page/{page}/
```

Nadpisania URL na poziomie strony ustawia się we frontmatter:

```yaml
---
title: "Über uns"
slug: ueber-uns
---
```

Daje to `/de/ueber-uns/` zamiast `/de/about/`.

## Tłumaczenia tagów

Tagi można tłumaczyć w `_tags.yaml`:

```yaml
# local/content/_tags.yaml
release:
  de: veröffentlichung
  pl: wydanie
  fr: version
```

Kanoniczny tag (klucz EN) jest używany wewnętrznie. Przetłumaczona wartość wyświetla się w UI dla każdego lokalu. Tagi identyczne we wszystkich lokalach nie wymagają wpisu — wystarczy wymienić tylko te różniące się.

## Działanie przełącznika języków

Przełącznik języków w nawigacji automatycznie rozwiązuje właściwy URL dla każdego innego lokalu przy użyciu funkcji Twig `lang_switch_urls()`. Łańcuch fallbacków, w kolejności:

1. **Mapa tłumaczeń** — szuka przetłumaczonej wersji bieżącej strony używając globalnej zmiennej `translation_map`
2. **Override kontrolera** — używa jawnego `lang_switch_url`, jeśli jest ustawiony (np. strony tagów, gdzie tag może nie istnieć w docelowym lokalu)
3. **Archiwum** — jeśli jesteś na stronie archiwalnej, prowadzi do tego samego archiwum w docelowym lokalu
4. **Paginowana lista wpisów** — jeśli jesteś na stronie 2+, prowadzi do tej samej strony w docelowym lokalu
5. **Lista wpisów** — jeśli jesteś na dowolnej stronie listy wpisów, prowadzi do listy wpisów w docelowym lokalu
6. **Strona główna** — ostateczny fallback, gdy nie ma dostępnego kontekstu bloga ani tłumaczenia

Przełącznik wyświetla się tylko wtedy, gdy skonfigurowane są co najmniej 2 lokale z co najmniej jednym innym zdefiniowanym lokalem.

## Strategia awaryjnego powrotu

Jeśli strona istnieje w wersji EN, ale nie DE, notACMS:
- Nie wygeneruje adresu URL dla DE dla tej strony
- Nie uwzględni jej w mapie strony dla DE
- Wyświetli link do strony głównej DE w przełączniku języków (nie do wersji EN tej strony)

Oznacza to, że możesz tłumaczyć stopniowo — EN jest zawsze kompletne, pozostałe lokale rozrastają się z czasem. To samo dotyczy FR lub każdego innego lokalu.

## hreflang i SEO

notACMS automatycznie generuje tagi `<link rel="alternate" hreflang="...">` w sekcji `<head>` dla wszystkich stron posiadających tłumaczenia. Pole `og_locale` w konfiguracji serwisu steruje formatem lokalu Open Graph (`en_US`, `de_DE` itp.).

```html
<link rel="alternate" hreflang="en" href="https://example.com/about/">
<link rel="alternate" hreflang="de" href="https://example.com/de/about/">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/about/">
<link rel="alternate" hreflang="x-default" href="https://example.com/about/">
```

> **Wskazówka:** Zawsze podawaj tłumaczenie `x-default` wskazujące na Twój podstawowy język. notACMS obsługuje to automatycznie dla domyślnego lokalu.
