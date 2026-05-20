---
title: "notACMS 1.1.3 — pliki JSON Schema i aktualizacja bezpieczeństwa Symfony 7.4.12"
slug: "wpisy/release-1-1-3"
description: "1.1.3 dostarcza pliki JSON Schema dla całej konfiguracji i frontmatter, naprawia nieaktualne fragmenty Pagefind oraz aktualizuje Symfony 7.4.12 + Twig 3.26.0, zamykając 24 CVE."
date: 2026-05-20
category: releases
tags: [release, security, symfony]
template: blog/post
---

## Pliki JSON Schema dla konfiguracji i frontmatter

Sześć plików JSON Schema draft-07 trafiło do `config/schema/`:

| Plik schematu | Opisuje |
|---|---|
| `site.schema.json` | `_site.yaml` — ustawienia witryny, lokalizacje, linki społecznościowe, formularz kontaktowy |
| `routes.schema.json` | `_routes.yaml` — nadpisania ścieżek URL per lokalizacja |
| `tags.schema.json` | `_tags.yaml` — mapa tłumaczeń tagów |
| `post.frontmatter.schema.json` | Frontmatter wpisów blogowych |
| `page.frontmatter.schema.json` | Frontmatter stron statycznych |
| `category.frontmatter.schema.json` | Frontmatter indeksów kategorii |

Wszystkie szablonowe pliki YAML (`_site.yaml`, `_routes.yaml`, `_tags.yaml`) w `docs/bare/`, `docs/demo/` i `docs/customization/old-template/` zawierają teraz komentarz `# yaml-language-server: $schema=` wskazujący na URL na gałęzi main. VS Code (i każdy edytor z YAML Language Server) wykrywa je automatycznie — walidacja i autouzupełnianie działają bez żadnej dodatkowej konfiguracji projektu.

Schematy są zoptymalizowane pod kątem wspomaganego przez AI tworzenia treści: opisy zawierają wartości domyślne, ograniczenia i wyjaśnienie zachowań, dzięki czemu asystent AI pracujący w innym projekcie może pobrać schemat i wiedzieć dokładnie, co robi każde pole.

Pobranie dowolnego schematu bezpośrednio z gałęzi main:

```
https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/<name>.schema.json
```

## Aktualizacja bezpieczeństwa: Symfony 7.4.12 i Twig 3.26.0

**Zaktualizuj natychmiast.** Uruchom `composer update` w swoim projekcie, a następnie przebuduj.

### Symfony 7.4.12 (21 CVE)

Najbardziej istotne dla wdrożeń CMS:

- **CVE-2026-45073** — SQL injection w `Cache` przez niezwalidowany `$prefix` w `PdoAdapter::doClear()`.
- **CVE-2026-45071** — XXE / ujawnienie lokalnych plików w `DomCrawler::addXmlContent()` przy włączonym `validateOnParse`.
- **CVE-2026-45075** — żądania HEAD omijają filtr `methods` w atrybutach `#[IsGranted]`, `#[IsCsrfTokenValid]` i `#[IsSignatureValid]`.
- **CVE-2026-45072** — XSS w `CodeExtension::fileExcerpt()` w `TwigBridge`.
- **CVE-2026-45068** — wstrzyknięcie nagłówka w `SendmailTransport`; adresy zaczynające się od myślnika są teraz odrzucane.
- **CVE-2026-45067** — adresy e-mail zawierające znaki nowej linii akceptowane przez `Mime\Address` — teraz odrzucane.
- **CVE-2026-45305 / 45304 / 45133** — katastrofalne backtracking i nieograniczona rekurencja w parserze YAML.
- **CVE-2026-45066 / 45064 / 45753** — trzy obejścia `HtmlSanitizer`: znaki BiDi, różnice parserów URL oraz niesanityzowane atrybuty `action`/`formaction`/`poster`/`cite`.

Pełna lista: [symfony.com/blog/symfony-7-4-12-released](https://symfony.com/blog/symfony-7-4-12-released).

### Twig 3.26.0 (4 CVE)

Wszystkie cztery to obejścia sandboxa. Jeśli Twój motyw używa sandboxa Twiga dla szablonów dostarczanych przez użytkownika, są krytyczne:

- **CVE-2026-46635** — obejście listy dozwolonych właściwości przez filtr `column` (`array_column` na obiektach).
- **CVE-2026-46638** — `{% sandbox %}{% include %}` pomija `checkSecurity()` na zbuforowanych szablonach; niekompletna poprawka dla CVE-2024-45411.
- **CVE-2026-24425** — obejście sandboxa przy użyciu polityki źródłowej.
- **CVE-2026-47732** — wielokrotne obejścia polityki `__toString()` przez niezabezpieczone punkty koercji stringów.

## Pozostałe zmiany w 1.1.3

- **Naprawione nieaktualne fragmenty Pagefind** — `scripts/rebuild-content.sh` usuwa teraz katalog `public/pagefind/` przed ponownym indeksowaniem. Wcześniej usunięte lub przemianowane treści pozostawiały osierocone pliki fragmentów, które Pagefind serwował razem z aktualnymi wynikami.
- **Usprawnienia DESIGN.md** — zakodowane na stałe wartości hex zastąpiono odniesieniami do tokenów w plikach `DESIGN.md` bare i demo; dodano alias koloru `primary`; dodano token komponentu `card-hover` do motywu demo.

## Jak zaktualizować

```bash
composer update
ddev build   # lub odpowiednik komendy budowania w Twoim środowisku
```

Nie są wymagane żadne zmiany konfiguracji ani kroki migracji.

## Pełny changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#113---2026-05-20)
