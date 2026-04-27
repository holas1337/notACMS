---
title: "notACMS v1.0.0"
slug: "wpisy/release-1-0-0"
description: "Pierwsze stabilne wydanie — podstawowy pipeline treści, wielojęzyczny routing, wyszukiwarka Pagefind, przetwarzanie obrazów i środowisko lokalne DDEV."
date: 2026-04-09
category: releases
tags: [release, announcement]
template: blog/post
---

## Co zawiera v1.0.0

Po kilku miesiącach wewnętrznego użytkowania w projektach osobistych, oznaczam pierwsze stabilne wydanie. Podstawowy zestaw funkcji jest wystarczająco solidny, aby budować prawdziwe strony.

### Pipeline treści

Pipeline treści to serce notACMS. Odczytuje katalog `local/content/`, parsuje frontmatter i jedną komendą generuje kompletną stronę statyczną.

- Treść Markdown z frontmatter YAML
- Renderowanie CommonMark z permalinkami nagłówków
- Generowanie excerptów z treści prozatorskich
- Obliczanie czasu czytania

### Wielojęzyczny routing

Wersje językowe są definiowane w `_site.yaml`. Każda wersja językowa otrzymuje własną przestrzeń URL, z opcjonalnymi nadpisaniami ścieżek w `_routes.yaml`.

```yaml
locales:
  en:
    label: "English"
    date_format: "M d, Y"
  pl:
    label: "Polski"
    date_format: "d.m.Y"
```

### Wyszukiwarka Pagefind

Pełnotekstowe wyszukiwanie jest wbudowane w wynik statyczny przez Pagefind. Komenda budowania automatycznie generuje indeks wyszukiwania. Brak zewnętrznego API, brak wyszukiwania po stronie serwera — tylko statyczny indeks działający offline.

### Przetwarzanie obrazów

Obrazy przechowywane obok treści są przetwarzane podczas budowania. notACMS generuje warianty WebP w wielu szerokościach, automatycznie aktualizuje atrybuty `src` o responsywne `srcset` i obsługuje mapowanie ścieżek między katalogami treści i wynikowym.

### Środowisko lokalne DDEV

Środowisko deweloperskie jest w pełni skonteneryzowane z DDEV. `ddev start` daje PHP 8.5, Nginx i wszystkie narzędzia budowania. `ddev build` produkuje wynik statyczny. `ddev code-check` uruchamia PHPStan i PHP CS Fixer.

## Breaking changes

To pierwsze stabilne wydanie. Jeśli używałeś wersji sprzed 1.0, sprawdź schemat `_site.yaml` — klucz `social` zmienił się z listy na mapę.

## Aktualizacja

```bash
git pull
ddev composer install
ddev build
```

## Co dalej

v1.1.0 skupi się na systemie designu i dokumentacji. Ta strona — zbudowana z notACMS — stanie się oficjalną dokumentacją i referencją projektową.
