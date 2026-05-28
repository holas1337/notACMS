---
title: "notACMS 1.1.4 — aktualizacja bezpieczeństwa Symfony 7.4.13 i Twig 3.27.0"
slug: "wpisy/release-1-1-4"
description: "1.1.4 to wyłącznie aktualizacja bezpieczeństwa: Symfony 7.4.13 i Twig 3.27.0 zamykają 11 CVE, w tym obejście firewalla, obejście SSRF i pięć obejść sandboxa Twiga."
date: 2026-05-28
category: releases
tags: [release, security, symfony]
template: blog/post
---

## Aktualizacja bezpieczeństwa: Symfony 7.4.13 i Twig 3.27.0

**Zaktualizuj natychmiast.** Uruchom `composer update` w swoim projekcie, a następnie przebuduj.

### Symfony 7.4.13 (6 CVE)

- **CVE-2026-48489** — obejście firewalla bezpieczeństwa: handler `failure_forward` honorował parametr `_failure_path` dostarczony przez atakującego w wewnętrznym podżądaniu, umożliwiając pominięcie firewalla.
- **CVE-2026-48736** — obejście SSRF w `NoPrivateNetworkHttpClient` i `IpUtils::PRIVATE_SUBNETS` przez formy przejściowe adresów IPv6 (IPv4-mapped, IPv4-compatible, 6to4, Teredo).
- **CVE-2026-48761** — `HtmlSanitizer` nie sanityzował atrybutów URL w elementach `<object>`, `<applet>`, `<iframe>`, `<img>` oraz URL w treści `<meta http-equiv="refresh">`.
- **CVE-2026-48760** — `HtmlSanitizer` akceptował procentowo zakodowane znaki BiDi i białe znaki Unicode w URL-ach, umożliwiając obejście sanityzatora przez wizualne podszywanie się.
- **CVE-2026-48784** — `UrlGenerator` błędnie kodował łańcuchowe segmenty `../` i `./`, generując URL-e mogące wychodzić poza zamierzony ścieżkę.
- **CVE-2026-48747** — Mailer: algorytm podpisu webhooka Mailomat nie był przypięty do SHA-256, umożliwiając ataki podstawienia algorytmu.

Pełna lista: [symfony.com/blog/symfony-7-4-13-released](https://symfony.com/blog/symfony-7-4-13-released).

### Twig 3.27.0 (5 CVE)

Wszystkie pięć to obejścia sandboxa. Jeśli Twój motyw renderuje szablony kontrolowane przez użytkownika w sandboxie, są krytyczne:

- **CVE-2026-46636** — obejście listy dozwolonych filtrów, tagów i funkcji przy zmianie stanu sandboxa między renderowaniami w długo działających procesach (np. FrankenPHP, RoadRunner).
- **CVE-2026-48808** — filtr `column` omijał listę dozwolonych właściwości pod `SourcePolicyInterface`.
- **CVE-2026-48806** — obejście polityki `__toString()` przez dynamiczne klucze mapowania: `{% set arr = {(obj): "value"} %}`.
- **CVE-2026-48807** — obejście polityki `__toString()` przez obiekty `Traversable` w filtrach `join`/`replace` i operatorach `in`/`not in`.
- **CVE-2026-48805** — regresja stanu sandboxa w przestarzałych wewnętrznych wrapperach (`twig_check_arrow_in_sandbox()`, `twig_array_some()`, `twig_array_every()`).

## Pozostałe zmiany w 1.1.4

- `symfony/polyfill-*` zaktualizowano z v1.37.0 do v1.38.1.
- Zależności deweloperskie: `phpstan/phpstan` 2.1.55 → 2.2.1, `phpunit/phpunit` 13.1.10 → 13.1.13, `rector/rector` 2.4.4 → 2.4.5.

## Jak zaktualizować

```bash
composer update
ddev build   # lub odpowiednik komendy budowania w Twoim środowisku
```

Nie są wymagane żadne zmiany konfiguracji ani kroki migracji.

## Pełny changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#114---2026-05-28)
