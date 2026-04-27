---
title: "notACMS 1.1.1 — Deploy szanuje istniejącą treść"
slug: "wpisy/release-1-1-1"
description: "1.1.1 naprawia błąd, przez który ./notACMS deploy --prod nadpisywało istniejącą treść local/. Deploy teraz seeduje tylko gdy local/ brakuje lub jest puste — zachowanie zgodne z ddev build."
date: 2026-04-26
category: releases
tags: [release, announcement]
template: blog/post
---

## Błąd

Uruchomienie `./notACMS deploy --prod` **tworzyło kopię zapasową i nadpisywało** cały katalog `local/` zawartością `docs/demo/` przy każdym deployu — nawet gdy zawierał twoją treść, szablony i dostosowania. Logika seedowania nie rozróżniała jawnego żądania ponownego seedowania motywu (`--bare`/`--demo`) od zwykłego redeployu z istniejącą treścią.

## Naprawa

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
