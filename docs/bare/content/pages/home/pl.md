---
title: "Witaj w notACMS"
slug: ""
description: "Patrzysz na goły motyw wireframe. Każda funkcja działa — minimalny styl, zero opinii."
template: page/home
---

## Uruchamiasz goły motyw

To jest **goły wireframe** — każda funkcja notACMS działa (blog, strony, formularz kontaktowy, wyszukiwarka, paginacja, wiele języków), ale z celowo minimalnym stylem. Brak trybu ciemnego, brak wyszukiwarki w overlayu, brak ozdobnego footera. Tylko fonty systemowe.

To jest twój punkt wyjścia do budowania własnego designu.

## Czym jest notACMS?

notACMS to CMS typu static-first zbudowany na Symfony. Piszesz w Markdown, definiujesz strukturę w YAML, uruchamiasz jedno polecenie i dostajesz czysty statyczny HTML. Brak bazy danych, brak serwera, brak niespodzianek.

- **Treść w Markdown** z frontmatter YAML
- **Routing wielojęzyczny** z automatycznymi tagami hreflang
- **Pagefind search** wbudowane w statyczny wynik
- **Przetwarzanie obrazów** — warianty WebP, responsywny srcset
- **DDEV development** — skonteneryzowane PHP 8.5, Nginx, narzędzia budowania
- **Budowanie statyczne** — jedno polecenie produkuje gotowy do wdrożenia HTML

## Chcesz pełny motyw demo?

Motyw demo zawiera tryb ciemny, overlay wyszukiwarki, sidebar dokumentacji, design amber-phosphor i trzy języki. Przełącz się:

```bash
./notACMS deploy --demo
```

## Dokumentacja

- [Architektura](https://github.com/holas1337/notACMS/blob/main/docs/ARCHITECTURE.md) — jak notACMS działa pod spodem
- [Dostosowanie](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md) — nadpisywanie szablonów, SCSS, tłumaczeń
- [Przewodnik redaktora](https://github.com/holas1337/notACMS/blob/main/docs/EDITOR_GUIDE.md) — pisanie treści, frontmatter, obrazy
