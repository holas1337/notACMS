---
title: "notACMS 1.1.1 — Etykiety nawigacji z frontmatter i deploy bez nadpisywania treści"
slug: "wpisy/release-1-1-1"
description: "1.1.1 wprowadza etykiety nawigacji oparte na frontmatter (zamiast kluczy tłumaczeń) i naprawia błąd deploy --prod nadpisujący local/."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Etykiety nawigacji z frontmatter

Etykiety w nawigacji dla stron treści pochodzą teraz z frontmatter — nie z kluczy tłumaczeń. Wystarczy ustawić `menu.label` w frontmatter strony, a to ono stanie się etykietą w menu; jeśli go nie ma, użyty zostanie `title`.

```yaml
---
title: "Przewodnik po architekturze"
slug: "architecture-guide"
menu:
  label: "Architektura"
  weight: 30
---
```

Nowa funkcja Twig `content_item()` pozwala pobrać dowolną stronę według klucza katalogu:

```twig
{{ content_item('architecture-guide', 'pl').menuLabel() }}
{# → "Architektura" (lub tytuł strony, jeśli menu.label nie jest ustawione) #}
```

**Dlaczego to ważne:** Wcześniej pliki `messages.*.yaml` w każdej lokalizacji musiały zawierać tłumaczenia dla `nav.home`, `nav.blog`, `nav.about`, `nav.contact`, `nav.privacy_policy` (core) oraz `site.releases`, `site.about`, `site.manual`, `site.architecture`, `site.customization`, `site.locales`, `site.design_reference`, `site.contact` (demo). Dodanie strony oznaczało aktualizację N plików tłumaczeń. Teraz wystarczy jedno pole w frontmatter. Własne szablony odwołujące się do usuniętych kluczy powinny przejść na `content_item('key', locale).menuLabel()`.

## Deploy nie nadpisuje już istniejącego `local/`

### Błąd

Uruchomienie `./notACMS deploy --prod` **tworzyło kopię zapasową i nadpisywało** cały katalog `local/` zawartością `docs/demo/` przy każdym deployu — nawet gdy zawierał twoją treść, szablony i dostosowania. Logika seedowania nie rozróżniała jawnego żądania ponownego seedowania motywu (`--bare`/`--demo`) od zwykłego redeployu z istniejącą treścią.

### Naprawa

Deploy działa teraz dokładnie tak samo jak `ddev build`:

- **Brak flagi `--bare` / `--demo`** → seeduje `local/` tylko jeśli nie istnieje lub jest puste; pomija, jeśli zawiera treść.
- **Przekazano `--bare` lub `--demo`** → tworzy kopię zapasową istniejącego `local/` i seeduje wybrany motyw.

```bash
./notACMS deploy --prod          # zachowuje istniejące local/
./notACMS deploy --prod --demo   # wymusza ponowne seedowanie z docs/demo/
./notACMS deploy --prod --bare   # wymusza ponowne seedowanie z docs/bare/
```

`--prod` kontroluje teraz tylko flagi Composera (`--no-dev`) i środowisko uruchomieniowe (`APP_ENV=prod`). Nigdy nie modyfikuje `local/`.

## Co jeszcze w 1.1.1

- **Aktualizacja zależności:** pakiety `symfony/polyfill-*` zaktualizowane z v1.36.0 do v1.37.0.
- **Treść demo w języku polskim, niemieckim i francuskim** zweryfikowana i ulepszona na wszystkich stronach i wpisach bloga.
- **Przewodniki stylu tłumaczeń** dodane do systemu umiejętności agenta AI dla języków polskiego, niemieckiego i francuskiego, aby zapewnić spójną jakość przyszłych tłumaczeń.

## Pełna lista zmian

Wszystkie zmiany z kategoriami: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#111---2026-04-26).
