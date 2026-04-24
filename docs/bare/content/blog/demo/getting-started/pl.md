---
title: "Pierwsze kroki z notACMS"
slug: "wpisy/getting-started"
description: "Dowiedz się, jak zainstalować notACMS i zbudować swoją pierwszą statyczną stronę."
date: 2025-04-15
category: demo
tags: [demo]
image: /media/getting-started/featured.webp
image_alt: "Obraz zastępczy dla przewodnika pierwszych kroków"
updated: 2025-05-01
toc: true
template: blog/post
---

## Pierwsze kroki z notACMS

Ten przewodnik pokazuje, jak zainstalować notACMS, stworzyć pierwszą stronę i wdrożyć statyczną witrynę.

## Instalacja

notACMS wymaga **PHP 8.5**, **Symfony** oraz **DDEV** do lokalnego rozwoju.

```bash
git clone https://github.com/holas1337/notACMS.git
cd notACMS
ddev start
```

Po uruchomieniu kontenerów zainstaluj zależności PHP:

```bash
ddev exec composer install
```

## Pisanie treści

Treść znajduje się w katalogu `content/` w plikach Markdown z frontmatter YAML:

```markdown
---
title: "Mój pierwszy wpis"
slug: "wpisy/first-post"
date: 2025-04-15
category: general
tags: [general]
---

Treść wpisu.
```

## Wdrożenie

Zbuduj statyczny HTML jednym poleceniem:

```bash
./notACMS deploy
```

Wynik trafia do katalogu `public/static/` — zwykłe pliki HTML gotowe do serwowania przez nginx lub wrzucenia na dowolny hosting statyczny.

## Następne kroki

- Przeczytaj [dokumentację architektury](https://github.com/holas1337/notACMS/blob/main/docs/ARCHITECTURE.md)
- Dostosuj szablony w `local/templates/`
- Dodaj tłumaczenia w `local/translations/`
