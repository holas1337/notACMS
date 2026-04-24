---
title: "notACMS v1.0.0"
slug: "beitraege/release-1-0-0"
description: "Das erste stabile Release — Core Content Pipeline, mehrsprachiges Routing, Pagefind-Suche, Bildverarbeitung und DDEV-basierte lokale Entwicklung."
date: 2026-04-09
category: releases
tags: [release, announcement]
template: blog/post
---

## Was in v1.0.0 steckt

Nach mehreren Monaten internem Einsatz in persönlichen Projekten markiere ich das erste stabile Release. Der Kern-Funktionsumfang ist solide genug, um damit echte Seiten zu bauen.

### Content-Pipeline

Die Content-Pipeline ist das Herzstück von notACMS. Sie liest das `local/content/`-Verzeichnis, parst Frontmatter und generiert mit einem einzigen Befehl eine vollständige statische Seite.

- Markdown-Inhalte mit YAML-Frontmatter
- CommonMark-Rendering mit Überschriften-Permalinks
- Excerpt-Generierung aus Fließtext
- Berechnung der Lesezeit

### Mehrsprachiges Routing

Locales werden in `_site.yaml` definiert. Jede Locale erhält ihren eigenen URL-Raum, mit optionalen Pfadüberschreibungen in `_routes.yaml`. `hreflang`-Tags werden automatisch generiert.

```yaml
locales:
  en:
    label: "English"
    date_format: "M d, Y"
  pl:
    label: "Polski"
    date_format: "d.m.Y"
```

### Pagefind-Suche

Volltextsuche ist über [Pagefind](https://pagefind.app/) in die statische Ausgabe eingebaut. Der Build-Befehl generiert den Suchindex automatisch. Keine externe API, keine serverseitige Suche — nur ein statischer Index, der auch offline funktioniert.

### Bildverarbeitung

Bilder, die sich neben den Inhalten befinden, werden beim Build verarbeitet. notACMS generiert WebP-Varianten in mehreren Breiten, aktualisiert `src`-Attribute automatisch mit responsivem `srcset` und übernimmt die Pfadzuordnung zwischen Inhalts- und Ausgabeverzeichnissen.

### DDEV lokale Entwicklung

Die Entwicklungsumgebung ist mit DDEV vollständig containerisiert. `ddev start` stellt PHP 8.5, Nginx und alle Build-Werkzeuge bereit. `ddev build` erzeugt die statische Ausgabe. `ddev code-check` führt PHPStan und PHP CS Fixer aus.

## Breaking Changes

Dies ist das erste stabile Release. Wer eine Pre-1.0-Version verwendet hat: Das `_site.yaml`-Schema prüfen — der `social`-Schlüssel wurde von einer Liste in eine Map geändert.

## Upgrade

```bash
git pull
ddev composer install
ddev build
```

## Was als nächstes kommt

v1.1.0 wird sich auf das Design-System und die Dokumentation konzentrieren. Diese Seite — gebaut mit notACMS — wird zur offiziellen Dokumentation und Design-Referenz.
