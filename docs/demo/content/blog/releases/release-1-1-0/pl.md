---
title: "notACMS 1.1.0 — Minimalny rdzeń, motyw demo, wybierz swój"
slug: "wpisy/release-1-1-0"
description: "1.1.0 dzieli notACMS na minimalny rdzeń i opcjonalny motyw demo. Wybierz punkt startowy flagą --bare lub --demo; personalizuj wszystko przez local/ bez dotykania rdzenia."
date: 2026-04-24
category: releases
tags: [release, announcement]
template: blog/post
---

## Dwa motywy, jeden wybór przy pierwszym budowaniu

Najważniejsza zmiana w 1.1.0: notACMS zawiera teraz dwa motywy, a ty wybierasz jeden już przy pierwszym buildzie:

```bash
./notACMS deploy --demo    # domyślny — projekt amber-phosphor, który widzisz tutaj
./notACMS deploy --bare    # minimalny wireframe: fonty systemowe, jasny tryb, ~200 linii CSS
```

`ddev build` obsługuje te same flagi. Cokolwiek wybierzesz, działa każda funkcja: treści wielojęzyczne, blog, RSS, sitemap, wyszukiwarka, formularz kontaktowy, obrazy z wariantami responsywnymi, czas czytania, wskaźnik postępu czytania. Różnica leży jedynie w wyglądzie — i, co najważniejsze, w tym, ile dziedziczysz, zanim zaczniesz dopasowywać.

### Minimalny rdzeń

Rdzeń w `templates/`, `assets/`, `translations/` jest teraz wireframem. Został świadomie zminimalizowany, żeby nadpisanie pojedynczego bloku nie ciągnęło za sobą języka wizualnego, z którym musiałbyś walczyć. Jeśli budujesz projekt szyty na miarę, startuj z bare i od pierwszego dnia kontrolujesz cały wygląd.

### Motyw demo

Demo znajduje się w `docs/demo/` i kopiowane jest do `local/`, kiedy wybierzesz `--demo`. To pełny projekt amber-phosphor, który właśnie czytasz — ciemny tryb, overlay wyszukiwania, sidebar dokumentacji, przełącznik motywu, wszystko razem. Startuj stąd, jeśli chcesz dopracowany projekt już dziś i planujesz dopracowywać, a nie przebudowywać.

## System nadpisań `local/`

Oba motywy korzystają z tego samego mechanizmu: każdy plik w `local/` ma pierwszeństwo przed odpowiadającą mu ścieżką w rdzeniu.

| Warstwa | Lokalizacja nadpisania | Resolver |
|---|---|---|
| Szablony Twig | `local/templates/*.html.twig` | Kernel Symfony |
| Entrypoint SCSS | `local/assets/styles/app_local.scss` | Sass-bundle |
| Entrypoint JS | `local/assets/app.js` | Importmapa AssetMappera |
| Tłumaczenia | `local/translations/messages.*.yaml` | Translator Symfony |
| Treści | `local/content/**` | Parametr `notacms_content` |
| Snippety Nginx | `local/docker/nginx/*.conf` | Entrypoint kontenera |

Rdzeń nie jest nigdy edytowany. `git pull` pozostaje czysty. Personalizacje znajdują się w repozytorium twojej strony, nie w forku tego repo.

## Aktualizacja z 1.0.0

Jeśli używałeś 1.0.0 i chcesz, żeby twoja strona wyglądała dokładnie jak wcześniej, pakiet kompatybilności to jedna linijka:

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

To wrzuca kompletny motyw sprzed 1.1.0 — szablony, SCSS, fonty, obrazy, tłumaczenia — do `local/`. Twoja strona renderuje się dokładnie jak przedtem. Później możesz selektywnie usuwać pliki z `local/`, w miarę jak adaptujesz nowe elementy projektu.

Faktyczne breaking changes, które cię dotkną, jeśli dostosowywałeś stary rdzeń:

- **Zmiana nazwy entrypointa SCSS**: `local/assets/styles/local.scss` → `local/assets/styles/app_local.scss`. Zmień nazwę pliku i zaktualizuj import w `local/assets/app.js`.
- **Zmienne SCSS kolorów w rdzeniu zastąpione CSS custom properties**: `$color-body` → `var(--text)`, `$color-link` → `var(--accent)` itd. Pełna mapa w [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md).
- **Klucze tłumaczeń usunięte** z rdzenia (nadal obecne w motywie demo): `header.tagline`, `nav.projects`, `nav.search`, `blog.published_on`, `blog.comments_disabled`. Jeśli twoje szablony wołają `'...'|trans` na tych kluczach, zaktualizuj je.
- **Katalog `docs/examples/` usunięty**: przydatne części trafiły do `docs/customization/old-template/`; przykłady nadpisań ad hoc zostały wyparte przez model bare/demo.

Zobacz [UPGRADE-1.1.md](https://github.com/holas1337/notACMS/blob/main/UPGRADE-1.1.md) po pełną listę ze snippetami przed/po.

## Co jeszcze nowego w 1.1.0

- **Czas czytania** i **wskaźnik postępu czytania** na wpisach, dokumentacji i releasach.
- **Przełącznik języka** jako rozszerzenie Twiga (`lang_switch_urls`) z poprawnymi łańcuchami fallbacków dla archiwów, list z paginacją i stron głównych.
- **Zajawki wpisów usuwają kotwice nagłówków** — żadnych zbłąkanych `#` na kartach list.
- **Szkielet zestawu testów** w `tests/Unit/`, `tests/Integration/`, `tests/Fixtures/` z początkowym pokryciem i komendą `test` uruchamianą z hosta.
- **Skrypty agenta AI** w `.claude/skills/` do pracy nad repo: dodawanie wersji językowych, sprawdzanie alignmentu dokumentacji, site-sweepy, tłumaczenia i generowanie przewodników aktualizacji.

## Pełna lista zmian

Każda zmiana ze swoją kategorią: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#110---2026-04-24).
