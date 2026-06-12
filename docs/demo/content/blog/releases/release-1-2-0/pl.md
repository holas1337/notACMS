---
title: "notACMS 1.2.0 — wydanie po audycie"
slug: "wpisy/release-1-2-0"
description: "Kompletny audyt kodu, bezpieczeństwa i szablonów w jednej wersji: ~170 poprawek, solidniejszy pipeline budowania, dokumentacja API motywów i kilka niekompatybilnych zmian."
date: 2026-06-12
category: releases
tags: [release, security, refactoring]
template: blog/post
---

## Wydanie po audycie

1.2.0 to wynik kompletnego audytu notACMS — architektury, bezpieczeństwa, jakości kodu i obu drzew szablonów. Naprawiono około 170 problemów. Najważniejsze zmiany:

### Odporność

- Plik markdown z błędnym frontmatterem nie psuje już całej wersji językowej ani nie przerywa builda — zostaje pominięty i zgłoszony ze ścieżką oraz treścią błędu.
- Brakujący `slug:` nie może już po cichu zastąpić strony głównej; `posts_per_page: 0` nie wysypuje bloga; zduplikowane adresy URL, niejednoznaczne klucze katalogów i nieprawidłowe tagi generują teraz czytelne ostrzeżenia builda.
- Szkice i zaplanowane **strony** są teraz naprawdę pomijane przy budowaniu, nie trafiają do menu ani do `/llms.txt` — wcześniej filtrowane były tylko wpisy.
- `app:build -o <katalog>` bez flagi `--force` odmówi wyczyszczenia katalogu innego niż skonfigurowany katalog statyczny.
- Pierwsze wejście na `/pl/` nie przekierowuje już do wersji angielskiej — adres URL jest traktowany jako wybór języka i zapisywany w cookie. Cookie jest teraz warunkowo oznaczane jako `Secure`, dzięki czemu mechanizm działa też w środowiskach HTTP.
- Wyniki wyszukiwania wyświetlają teraz wyróżnienia jako prawdziwe znaczniki `<mark>`, a nie jako dosłowny tekst `&lt;mark&gt;`.
- Bazowy `_site.yaml` szablonu startowego zawiera teraz przykładowe wartości `contact_form` — świeża instalacja nie kończy się już błędem builda.

### Bezpieczeństwo

- ImageMagick działa teraz przez `Symfony\Process` z tablicami argumentów — `exec()` znikło.
- Turnstile weryfikuje hostname odpowiedzi względem `base_url` i loguje błąd, gdy w produkcji aktywne są testowe klucze. Przy starcie sprawdzane jest też, czy `APP_SECRET` nie jest wciąż placeholderem.
- Output JSON-LD jest hex-escapowany (`</script>` w tytule nie wyłamie ze skryptu), katalogi tłumaczeń nie zawierają już HTML-a, a nagłówki bezpieczeństwa nginx obejmują teraz też odpowiedzi `/assets/` i `/media/`.
- Lokalizowane API formularza kontaktowego było nieosiągalne w deploymencie Docker przez błąd w regexie nginx — naprawione.

### Dla twórców motywów

- Nowy [THEME_BUILDING.md](https://github.com/holas1337/notACMS/blob/main/docs/THEME_BUILDING.md): pełny kontrakt API motywów — konteksty szablonów per trasa, funkcje i zmienne globalne Twig, API ContentItem oraz klucze tłumaczeń wymagane przez każdy motyw.
- `#[LocalizedRoute]` działa teraz w `local/src/Controller/`, przełączanie języków działa poprawnie przy 3 i więcej językach, a pipeline budowania statycznego został rozbity na wielokrotnie używalne serwisy.

### Nowości

- Build statyczny generuje teraz plik `/llms.txt` dla każdej wersji językowej — lista najnowszych wpisów w formacie czytelnym dla modeli językowych. Konfigurowane przez `llms_limit` w `_site.yaml` (domyślnie: 5). Szablon jest nadpisywalny per-motyw przez przestrzeń nazw `@base` Twig.

### Breaking changes

Kilka — każda z jednolinijkową migracją. `directoryKey()` zwraca teraz pełną ścieżkę treści (szukanie po samej nazwie nadal działa), `getTree()` przeniesione do `ContentTreeProviderInterface`, `blogPosting()` przyjmuje nazwaną mapę, klucz kontekstu `lang_switch_url` znikł, usunięto cztery nieużywane klucze tłumaczeń, a pakiet `old-template` z wersji 1.0 został wycofany. Pełny przewodnik migracji: [UPGRADE-1.2.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.2.md).
