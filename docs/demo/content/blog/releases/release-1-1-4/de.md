---
title: "notACMS 1.1.4 — Sicherheitsupdate Symfony 7.4.13 und Twig 3.27.0"
slug: "beitraege/release-1-1-4"
description: "1.1.4 ist ein reines Sicherheitsrelease: Symfony 7.4.13 und Twig 3.27.0 schließen 11 CVEs, darunter eine Firewall-Umgehung, eine SSRF-Umgehung und fünf Twig-Sandbox-Umgehungen."
date: 2026-05-28
category: releases
tags: [release, security, symfony]
template: blog/post
---

## Sicherheitsupdate: Symfony 7.4.13 und Twig 3.27.0

**Sofort aktualisieren.** Führe `composer update` im Projekt aus und rebuild anschließend.

### Symfony 7.4.13 (6 CVEs)

- **CVE-2026-48489** — Firewall-Umgehung: der `failure_forward`-Handler wertete einen vom Angreifer kontrollierten `_failure_path`-Parameter im internen Subrequest aus und ermöglichte so das Umgehen der Firewall.
- **CVE-2026-48736** — SSRF-Umgehung in `NoPrivateNetworkHttpClient` und `IpUtils::PRIVATE_SUBNETS` über IPv6-Übergangsadressformen (IPv4-mapped, IPv4-compatible, 6to4, Teredo).
- **CVE-2026-48761** — `HtmlSanitizer` bereinigt URL-Attribute auf `<object>`, `<applet>`, `<iframe>`, `<img>` und die URL im `<meta http-equiv="refresh">`-Inhalt nicht.
- **CVE-2026-48760** — `HtmlSanitizer` akzeptierte prozentkodierte BiDi-Zeichen und Unicode-Whitespace in URLs und ermöglichte so eine Sanitizer-Umgehung durch visuelles Spoofing.
- **CVE-2026-48784** — `UrlGenerator` kodierte verkettete `../`- und `./`-Segmente falsch und erzeugte URLs, die aus dem beabsichtigten Pfad herausführen konnten.
- **CVE-2026-48747** — Mailer: Der Webhook-Signaturalgorithmus von Mailomat war nicht auf SHA-256 festgelegt, was Algorithm-Substitution-Angriffe ermöglichte.

Vollständige Liste: [symfony.com/blog/symfony-7-4-13-released](https://symfony.com/blog/symfony-7-4-13-released).

### Twig 3.27.0 (5 CVEs)

Alle fünf sind Sandbox-Umgehungen. Wenn dein Theme benutzergesteuerte Templates in einer Sandbox rendert, sind diese kritisch:

- **CVE-2026-46636** — Umgehung der Allowlist für Filter, Tags und Funktionen, wenn sich der Sandbox-Status zwischen Renders in langlebigen Prozessen ändert (z. B. FrankenPHP, RoadRunner).
- **CVE-2026-48808** — `column`-Filter umgeht die Property-Allowlist unter `SourcePolicyInterface`.
- **CVE-2026-48806** — `__toString()`-Policy-Umgehung über dynamische Mapping-Keys: `{% set arr = {(obj): "value"} %}`.
- **CVE-2026-48807** — `__toString()`-Policy-Umgehung über `Traversable`-Objekte in den Filtern `join`/`replace` und den Operatoren `in`/`not in`.
- **CVE-2026-48805** — Sandbox-Statusregression in veralteten internen Wrappern (`twig_check_arrow_in_sandbox()`, `twig_array_some()`, `twig_array_every()`).

## Weitere Änderungen in 1.1.4

- `symfony/polyfill-*` von v1.37.0 auf v1.38.1 aktualisiert.
- Dev-Abhängigkeiten aktualisiert: `phpstan/phpstan` 2.1.55 → 2.2.1, `phpunit/phpunit` 13.1.10 → 13.1.13, `rector/rector` 2.4.4 → 2.4.5.

## So aktualisierst du

```bash
composer update
ddev build   # oder das entsprechende Build-Kommando in deiner Umgebung
```

Es sind keine Konfigurationsänderungen oder Migrationsschritte erforderlich.

## Vollständiges Changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#114---2026-05-28)
