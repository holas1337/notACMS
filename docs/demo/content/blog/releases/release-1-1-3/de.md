---
title: "notACMS 1.1.3 — JSON-Schema-Dateien und Sicherheitsupdate Symfony 7.4.12"
slug: "beitraege/release-1-1-3"
description: "1.1.3 liefert JSON-Schema-Dateien für alle Konfigurationen und Frontmatter, behebt veraltete Pagefind-Fragmente und aktualisiert Symfony 7.4.12 + Twig 3.26.0 mit 24 CVE-Fixes."
date: 2026-05-20
category: releases
tags: [release, security, symfony]
template: blog/post
---

## JSON-Schema-Dateien für Konfiguration und Frontmatter

Sechs JSON-Schema-Draft-07-Dateien liegen nun unter `config/schema/`:

| Schema-Datei | Beschreibt |
|---|---|
| `site.schema.json` | `_site.yaml` — Site-Einstellungen, Locales, Social-Links, Kontaktformular |
| `routes.schema.json` | `_routes.yaml` — locale URL-Pfad-Überschreibungen |
| `tags.schema.json` | `_tags.yaml` — Tag-Übersetzungstabelle |
| `post.frontmatter.schema.json` | Frontmatter für Blog-Posts |
| `page.frontmatter.schema.json` | Frontmatter für statische Seiten |
| `category.frontmatter.schema.json` | Frontmatter für Kategorie-Index-Dateien |

Alle Template-YAML-Dateien (`_site.yaml`, `_routes.yaml`, `_tags.yaml`) in `docs/bare/`, `docs/demo/` und `docs/customization/old-template/` enthalten jetzt einen `# yaml-language-server: $schema=`-Kommentar mit dem URL auf den main-Branch. VS Code (und jeder Editor mit YAML Language Server) erkennt diese automatisch — Validierung und Autovervollständigung funktionieren ohne zusätzliche Projektkonfiguration.

Die Schemas sind für KI-gestütztes Authoring optimiert: Beschreibungen enthalten Standardwerte, Constraints und Verhaltensdetails, sodass ein KI-Agent in einem anderen Projekt ein Schema abrufen und genau wissen kann, was jedes Feld bewirkt.

Ein Schema direkt vom main-Branch abrufen:

```
https://raw.githubusercontent.com/holas1337/notACMS/main/config/schema/<name>.schema.json
```

## Sicherheitsupdate: Symfony 7.4.12 und Twig 3.26.0

**Sofort aktualisieren.** Führe `composer update` im Projekt aus und rebuild anschließend.

### Symfony 7.4.12 (21 CVEs)

Die relevantesten für CMS-Deployments:

- **CVE-2026-45073** — SQL-Injection in `Cache` durch nicht validierten `$prefix` in `PdoAdapter::doClear()`.
- **CVE-2026-45071** — XXE / lokale Dateioffenlegung in `DomCrawler::addXmlContent()` bei aktiviertem `validateOnParse`.
- **CVE-2026-45075** — HEAD-Anfragen umgehen den `methods`-Filter in den Attributen `#[IsGranted]`, `#[IsCsrfTokenValid]` und `#[IsSignatureValid]`.
- **CVE-2026-45072** — XSS in `CodeExtension::fileExcerpt()` in `TwigBridge`.
- **CVE-2026-45068** — Header-Injection in `SendmailTransport`; Adressen, die mit einem Bindestrich beginnen, werden nun abgelehnt.
- **CVE-2026-45067** — E-Mail-Adressen mit Zeilenumbrüchen wurden von `Mime\Address` akzeptiert — werden nun abgelehnt.
- **CVE-2026-45305 / 45304 / 45133** — Katastrophales Backtracking und unbegrenzte Rekursion im YAML-Parser.
- **CVE-2026-45066 / 45064 / 45753** — Drei `HtmlSanitizer`-Umgehungen: BiDi-Override-Zeichen, URL-Parser-Differenzen sowie nicht bereinigte `action`/`formaction`/`poster`/`cite`-Attribute.

Vollständige Liste: [symfony.com/blog/symfony-7-4-12-released](https://symfony.com/blog/symfony-7-4-12-released).

### Twig 3.26.0 (4 CVEs)

Alle vier sind Sandbox-Umgehungen. Wenn dein Theme Twigs Sandbox für benutzerbereitgestellte Templates verwendet, sind diese kritisch:

- **CVE-2026-46635** — Umgehung der Property-Allowlist über den `column`-Filter (`array_column` auf Objekten).
- **CVE-2026-46638** — `{% sandbox %}{% include %}` überspringt `checkSecurity()` bei gecachten Templates; unvollständige Behebung von CVE-2024-45411.
- **CVE-2026-24425** — Sandbox-Umgehung bei Verwendung einer Source-Policy.
- **CVE-2026-47732** — Mehrfache `__toString()`-Policy-Umgehungen über ungesicherte String-Koerzionspunkte.

## Weitere Änderungen in 1.1.3

- **Veraltete Pagefind-Fragmente behoben** — `scripts/rebuild-content.sh` löscht jetzt `public/pagefind/` vor der Neuindizierung. Zuvor hinterließen entfernte oder umbenannte Inhalte verwaiste Fragment-Dateien, die Pagefind neben aktuellen Ergebnissen auslieferte.
- **DESIGN.md-Verbesserungen** — Hartcodierte Hex-Werte in den `DESIGN.md`-Dateien von bare und demo durch Token-Referenzen ersetzt; `primary`-Farbalias hinzugefügt; `card-hover`-Komponenten-Token zum Demo-Theme hinzugefügt.

## So aktualisierst du

```bash
composer update
ddev build   # oder das entsprechende Build-Kommando in deiner Umgebung
```

Es sind keine Konfigurationsänderungen oder Migrationsschritte erforderlich.

## Vollständiges Changelog

[CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#113---2026-05-20)
