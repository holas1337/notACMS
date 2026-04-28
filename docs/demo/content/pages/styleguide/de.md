---
title: "Design-Referenz"
description: "Das notACMS Design-System — Farbtokens, Typografie, Komponenten und SCSS-Klassenreferenz."
template: page/styleguide
slug: design-referenz
menu:
  weight: 70
  label: "Design-Referenz"
---

Diese Seite ist eine fortlaufend aktualisierte Referenz für das notACMS Design-System. Jede hier gezeigte Komponente entspricht einer benannten SCSS-Klasse. Überschreibe Tokens in `local/assets/styles/`, um Dein Branding anzuwenden.

## Farbtokens

CSS Custom Properties, definiert in `assets/styles/_tokens.scss`. Überschreibe beliebige Werte in `local/assets/styles/`, um das gesamte Erscheinungsbild der Seite anzupassen. Die folgende Tabelle zeigt die am häufigsten überschriebenen Tokens — die vollständige Menge inklusive der Gruppen `--hero-*`, `--success-*`, `--danger-*` und `--code-*` findest Du in `_tokens.scss`.

| Token | Hell | Dunkel | Verwendung |
|---|---|---|---|
| `--bg` | `#ffffff` | `#0e0d0b` | Seitenhintergrund |
| `--bg-surface` | `#f8f9fa` | `#161410` | Angehobene Flächen, Seitenleiste |
| `--bg-elevated` | `#f1f3f5` | `#1e1c17` | Schaltflächen, Inline-Code-Hintergrund |
| `--text` | `#212529` | `#f8f9fa` | Primärer Text |
| `--text-muted` | `#6c757d` | `#9ca3af` | Sekundärer Text, Bildunterschriften |
| `--border` | `#dee2e6` | `#2d2a24` | Trennlinien, Kartenränder |
| `--accent` | `#B45309` | `#FFB000` | Links, aktive Zustände, CTAs |
| `--accent-bg` | `#fffbeb` | `rgba(255,176,0,.08)` | Akzent-Hintergründe |
| `--sidebar-bg` | `#f8f9fa` | `#111009` | Dokumentations-Seitenleiste |
| `--code-bg` | `#f6f8fa` | `#0d1117` | Code-Block-Hintergrund |
| `--nav-bg` | dunkles Glas | dunkles Glas | Immer dunkel |
| `--footer-bg` | `#0e0d0b` | `#0e0d0b` | Immer dunkel |

## Typografie

Die Schriftgrößenskala ist als SCSS-Variablen in `assets/styles/_variables.scss` definiert.

| Variable | Größe | Verwendung |
|---|---|---|
| `$fs-xs` | 11px | Labels, Badges, Mono-Metadaten |
| `$fs-sm` | 13px | Bildunterschriften, kleiner Text |
| `$fs-base` | 16px | Standard-UI-Text |
| `$fs-lg` | 17px | Fließtext |
| `$fs-xl` | 22px | H2-Überschriften |
| `$fs-xxl` | 28px | H1-Kleinvariante |

**Schriften:** `Inter` (UI/Fließtext) + `JetBrains Mono` (Code, Labels, Mono-Elemente). Geladen über Google Fonts CDN.

## Fließtext-Komponenten

Sämtlicher Markdown-Inhalt wird in `.prose` eingebettet. Diese Klassen sind in `assets/styles/_prose.scss` definiert.

### Überschriften

H2-Überschriften besitzen eine untere Rahmenlinie und `scroll-margin-top` für die genaue Positionierung von Inhaltsverzeichnis-Ankern. H3 und H4 sind fortschreitend kleiner und heller.

### Code-Blöcke

```bash
# Ein Code-Block mit bash-Sprachkennzeichnung
ddev build
```

```yaml
# YAML-Konfigurationsbeispiel
site:
  name: "My Site"
  locales:
    en:
      label: "English"
```

### Hinweisboxen

Hinweisboxen verwenden die `.callout`-Komponente (definiert in `assets/styles/_components.scss`):

> **Tipp:** Verwende Hinweisboxen, um wichtige Informationen hervorzuheben. Das Blockquote-Element wird im notACMS-Fließtext als gestaltete Hinweisbox dargestellt.

### Tabellen

Siehe die Farbtokens-Tabelle oben. Tabellen verwenden eine Monospace-Schrift für die Kopfzeile und die Akzentfarbe für die erste Datenspalte.

## Navigationskomponenten

### `.sidebar-nav` — Dokumentations-Seitenleiste

```
.docs-sidebar
  .sidebar-section
    .sidebar-label       ← Überschrift „Dokumentation"
    .sidebar-nav
      li > a             ← Standardlink
      li > a.is-active   ← aktive Seite
    .sidebar-icon        ← Icon-Wrapper
  .sidebar-divider
```

### `.toc-list` — Recht Seitenleiste Inhaltsverzeichnis

Automatisch aus h2/h3-Überschriften in `.docs-content .prose` durch `docs-toc.js` erzeugt. Das aktive Element wird über einen `IntersectionObserver` verfolgt.

### `.prev-next-nav` — Zurück/Weiter

```
.prev-next-nav
  .prev-next-card               ← Link zur vorherigen Seite
  .prev-next-card.prev-next-card--next  ← Link zur nächsten Seite
    .prev-next-dir              ← Label „Zurück" / „Weiter"
    .prev-next-title            ← Seitentitel
```

## Kartenkomponenten

### `.feature-card`

Verwendet im Feature-Raster der Startseite. Enthält `.feature-icon` + Titel + Beschreibung.

### `.release-card`

Verwendet auf der Release-Listenseite. Enthält `.release-card-meta`, Titel, Auszug und `.read-more`-Link.

### `.stat-card`

Verwendet im Abschnitt „Was ist notACMS?" auf der Startseite. Enthält `.stat-card-num`, `.stat-card-label`, `.stat-card-desc`.

### `.prev-next-card` (Blogbeiträge)

Dasselbe `.prev-next-nav`-Element wird auch für die Zurück/Weiter-Navigation bei Blogbeiträgen verwendet.

## Phosphor-Icon-Referenz

Icons werden über das Phosphor Icons CDN geladen. Verwende die Syntax `<i class="ph ph-{name}"></i>`.

| Icon | Klasse | Verwendung |
|---|---|---|
| Buch (offen) | `ph-book-open` | Handbuch-Link in der Seitenleiste |
| Baumstruktur | `ph-tree-structure` | Architektur-Link in der Seitenleiste |
| Schieberegler | `ph-sliders` | Anpassungs-Link in der Seitenleiste |
| Globus | `ph-globe` | Lokale-Link in der Seitenleiste |
| Palette | `ph-palette` | Design-Referenz-Link in der Seitenleiste |
| Rakete | `ph-rocket` | Releases-Link in der Seitenleiste |
| GitHub-Logo | `ph-github-logo` | GitHub-Links |
| Pfeil rechts | `ph-arrow-right` | CTAs, „Weiter"-Labels |
| Pfeil links | `ph-arrow-left` | „Zurück"-Labels |
| Pfeil (extern) | `ph-arrow-square-out` | Externe Links |
| Mond | `ph-moon` | Themenwechsel (Hellmodus-Zustand) |
| Sonne | `ph-sun` | Themenwechsel (Dunkelmodus-Zustand) |
| Glühbirne | `ph-lightbulb` | Tipp-Hinweisboxen |
| Einklapp-Pfeil | `ph-caret-down` | Dokumentations-Dropdown in der Navigation |
| Blitz | `ph-lightning` | Statistiken, Geschwindigkeit |
| Lupe | `ph-magnifying-glass` | Suche |
| Code | `ph-code` | Code/PHP-Features |
| Markdown-Logo | `ph-markdown-logo` | Markdown-Feature |
| Liste | `ph-list` | Mobile Seitenleiste ein-/ausblenden |
