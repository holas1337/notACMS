---
title: "Lokalisierung"
description: "Sprachen hinzufügen, Inhalte übersetzen und URL-Muster für jede Locale konfigurieren."
template: page/doc
slug: lokalisierung
menu:
  weight: 50
  label: "Lokalisierung"
---

## Eine neue Locale hinzufügen

`local/content/_site.yaml` öffnen und einen Eintrag zur `locales`-Map hinzufügen:

```yaml
site:
  locales:
    en:
      label: "English"
      og_locale: en_US
      date_format: "M d, Y"
      tagline: "static. simple. yours."
      # tagline_commands: ["git push", "ddev build"]  # optional: im Hero unter dem Tagline angezeigt
      # font_preload: true                                # optional: Inter + JetBrains Mono vorladen
    de:
      label: "Deutsch"
      og_locale: de_DE
      date_format: "d.m.Y"
      tagline: "statisch. einfach. deins."
    pl:                                 # diese Demo-Site enthält auch Polnisch
      label: "Polski"
      og_locale: pl_PL
      date_format: "d.m.Y"
      tagline: "statyczny. prosty. twój."
    fr:                                 # und Französisch
      label: "Français"
      og_locale: fr_FR
      date_format: "d/m/Y"
      tagline: "statique. simple. vôtre."
```

Das ist alles. notACMS wird:
- `de` zum Sprachumschalter hinzufügen
- `hreflang`-Tags für alle Seiten mit einer DE-Übersetzung generieren
- DE-URLs mit `/de/` präfixieren (oder deinen benutzerdefinierten Pfad verwenden)

Dasselbe gilt für `fr`, `pl` oder jede andere Locale, die du hinzufügst.

## Gemeinsame Ablage von Inhalten

Übersetzungen befinden sich neben dem Quellinhalt. Jede Locale ist eine separate Markdown-Datei im selben Verzeichnis:

```
pages/about/
├── en.md    ← Englisch
├── de.md    ← Deutsch
├── pl.md    ← Polnisch
└── fr.md    ← Französisch
```

Das `slug`-Feld im Frontmatter definiert den URL-Pfad für jede Locale. Wenn leer (oder bei Index-Seiten weggelassen), wird die Homepage-URL verwendet (`/` für die Standard-Locale, `/{locale}/` für andere). URLs stammen immer aus dem `slug`-Frontmatter-Feld — nicht aus Verzeichnispfaden.

## URL-Überschreibungen

Standardmäßig wird dem EN-Pfad das Locale-Präfix vorangestellt: `/de/about/`, `/pl/about/`, `/fr/about/`. Für einen vollständig benutzerdefinierten Pfad einen Eintrag in `_routes.yaml` hinzufügen:

```yaml
# local/content/_routes.yaml
routes:
  blog_list:
    de: /beitraege/
    pl: /wpisy/
    fr: /articles/
  blog_list_paginated:
    de: /beitraege/seite/{page}/
    pl: /wpisy/strona/{page}/
    fr: /articles/page/{page}/
```

URL-Überschreibungen auf Seitenebene werden im Frontmatter gesetzt:

```yaml
---
title: "Über uns"
slug: ueber-uns
---
```

Das ergibt `/de/ueber-uns/` anstelle von `/de/about/`.

## Tag-Übersetzungen

Tags können in `_tags.yaml` übersetzt werden:

```yaml
# local/content/_tags.yaml
release:
  de: veröffentlichung
  pl: wydanie
  fr: version
```

Der kanonische Tag (EN-Schlüssel) wird intern verwendet. Der übersetzte Wert erscheint in der Benutzeroberfläche für jede Locale. Tags, die in allen Locales identisch sind, brauchen keinen Eintrag — liste nur die abweichenden auf.

## Verhalten des Sprachumschalters

Der Sprachumschalter in der Navigation löst automatisch die korrekte URL für jede andere Locale über die Twig-Funktion `lang_switch_urls()` auf. Die Fallback-Kette, in dieser Reihenfolge:

1. **Translation-Map** — sucht eine übersetzte Version der aktuellen Seite über das `translation_map` Global
2. **Controller-Override** — verwendet eine explizite `lang_switch_url`, falls gesetzt (z. B. Tag-Seiten, bei denen der Tag in der Ziel-Locale möglicherweise nicht existiert)
3. **Archiv** — wenn auf einer Archivseite, verlinkt auf dasselbe Archiv in der Ziel-Locale
4. **Paginierte Blog-Liste** — wenn auf Seite 2+, verlinkt auf dieselbe Seite in der Ziel-Locale
5. **Blog-Liste** — wenn auf einer beliebigen Blog-Listenseite, verlinkt auf die Blog-Liste in der Ziel-Locale
6. **Startseite** — letzter Fallback, wenn kein Blog- oder Übersetzungskontext verfügbar ist

Der Umschalter erscheint nur, wenn 2 oder mehr konfigurierte Locales vorhanden sind und mindestens eine weitere Locale definiert ist.

## Fallback-Strategie

Wenn eine Seite auf EN, aber nicht auf DE existiert, wird notACMS:
- Keine DE-URL für diese Seite generieren
- Sie nicht in die DE-Sitemap aufnehmen
- Einen Link zur DE-Startseite im Sprachumschalter anzeigen (nicht zur EN-Version der Seite)

Das bedeutet, du kannst schrittweise übersetzen — EN ist immer vollständig, andere Locales wachsen mit der Zeit. Dasselbe gilt für FR oder jede andere Locale.

## hreflang und SEO

notACMS generiert automatisch `<link rel="alternate" hreflang="...">` Tags im `<head>` für alle Seiten mit Übersetzungen. Das `og_locale`-Feld in der Site-Konfiguration steuert das Open-Graph-Locale-Format (`en_US`, `de_DE` usw.).

```html
<link rel="alternate" hreflang="en" href="https://example.com/about/">
<link rel="alternate" hreflang="de" href="https://example.com/de/about/">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/about/">
<link rel="alternate" hreflang="x-default" href="https://example.com/about/">
```

> **Tipp:** Stets eine `x-default`-Übersetzung bereitstellen, die auf die primäre Sprache zeigt. notACMS übernimmt dies für die Standard-Locale automatisch.
