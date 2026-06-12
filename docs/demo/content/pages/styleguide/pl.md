---
title: "Dokumentacja projektowa"
description: "System projektowy notACMS — tokeny kolorów, typografia, komponenty i klasy SCSS."
template: page/styleguide
slug: design-reference
menu:
  weight: 70
  label: "Design"
---

Ta strona to żywa dokumentacja systemu projektowego notACMS. Każdy pokazany tu komponent odpowiada nazwanej klasie SCSS. Nadpisz tokeny w `local/assets/styles/`, aby zastosować własny branding. Nazwy komponentów i klas CSS są identyfikatorami technicznymi — pozostają w języku angielskim.

## Color Tokens

Custom properties CSS zdefiniowane w `assets/styles/_tokens.scss`. Nadpisanie dowolnego z nich w `local/assets/styles/` przefarbowuje całą stronę. Tabela poniżej pokazuje najczęściej nadpisywane tokeny — pełny zestaw (w tym grupy `--hero-*`, `--success-*`, `--danger-*` i `--code-*`) znajdziesz w `_tokens.scss`.

| Token | Jasny | Ciemny | Zastosowanie |
|---|---|---|---|
| `--bg` | `#ffffff` | `#0e0d0b` | Tło strony |
| `--bg-surface` | `#f8f9fa` | `#161410` | Powierzchnie wyniesione, sidebar |
| `--bg-elevated` | `#f1f3f5` | `#1e1c17` | Przyciski, tło kodu w tekście |
| `--text` | `#212529` | `#f8f9fa` | Tekst podstawowy |
| `--text-muted` | `#6c757d` | `#9ca3af` | Tekst drugorzędny, podpisy |
| `--border` | `#dee2e6` | `#2d2a24` | Linie podziału, obramowania kart |
| `--accent` | `#B45309` | `#FFB000` | Linki, stany aktywne, CTA |
| `--accent-bg` | `#fffbeb` | `rgba(255,176,0,.08)` | Tła akcentowe |
| `--sidebar-bg` | `#f8f9fa` | `#111009` | Sidebar dokumentacji |
| `--code-bg` | `#f6f8fa` | `#0d1117` | Tło bloków kodu |
| `--nav-bg` | ciemne szkło | ciemne szkło | Zawsze ciemny |
| `--footer-bg` | `#0e0d0b` | `#0e0d0b` | Zawsze ciemny |

## Typografia

Skala typograficzna jest zdefiniowana jako zmienne SCSS w `assets/styles/_variables.scss`.

| Zmienna | Rozmiar | Zastosowanie |
|---|---|---|
| `$fs-xs` | 11px | Etykiety, odznaki, metadane mono |
| `$fs-sm` | 13px | Podpisy, drobny tekst |
| `$fs-base` | 16px | Domyślny tekst interfejsu |
| `$fs-lg` | 17px | Tekst treści (prose) |
| `$fs-xl` | 22px | Nagłówki H2 |
| `$fs-xxl` | 28px | Mniejszy wariant H1 |

**Fonty:** `Inter` (interfejs/treść) + `JetBrains Mono` (kod, etykiety, elementy mono). Ładowane z CDN Google Fonts.

## Komponenty prose

Cała treść Markdown jest opakowana w `.prose`. Te klasy są zdefiniowane w `assets/styles/_prose.scss`.

### Nagłówki

Nagłówki H2 mają dolne obramowanie oraz `scroll-margin-top` dla poprawnego kotwiczenia z TOC. H3 i H4 są stopniowo mniejsze i lżejsze.

### Bloki kodu

```bash
# Blok kodu z etykietą języka bash
ddev build
```

```yaml
# Przykładowa konfiguracja YAML
site:
  name: "Moja strona"
  locales:
    pl:
      label: "Polski"
```

### Wyróżnienia (callouts)

Wyróżnienia korzystają z komponentu `.callout` (zdefiniowanego w `assets/styles/_components.scss`):

> **Wskazówka:** używaj wyróżnień do podkreślania ważnych informacji. Element blockquote renderuje się w prose notACMS jako ostylowany callout.

### Tabele

Zobacz tabelę Color Tokens powyżej. Tabele mają font mono w wierszu nagłówka i kolor akcentu w pierwszej kolumnie danych.

## Komponenty nawigacji

### `.sidebar-nav` — sidebar dokumentacji

```
.docs-sidebar
  .sidebar-section
    .sidebar-label       ← nagłówek „Dokumentacja"
    .sidebar-nav
      li > a             ← link domyślny
      li > a.is-active   ← strona aktywna
    .sidebar-icon        ← kontener ikony
  .sidebar-divider
```

### `.toc-list` — TOC w prawym sidebarze

Generowany automatycznie z nagłówków h2/h3 w `.docs-content .prose` przez `docs-toc.js`. Aktywna pozycja jest śledzona przez `IntersectionObserver`.

### `.prev-next-nav` — poprzednia/następna

```
.prev-next-nav
  .prev-next-card               ← link do poprzedniej
  .prev-next-card.prev-next-card--next  ← link do następnej
    .prev-next-dir              ← etykieta „Poprzednia" / „Następna"
    .prev-next-title            ← tytuł strony
```

## Komponenty kart

### `.feature-card`

Używana w siatce funkcji na stronie głównej. Zawiera `.feature-icon` + tytuł + opis.

### `.release-card`

Używana na liście wydań. Zawiera `.release-card-meta`, tytuł, zajawkę i link `.read-more`.

### `.stat-card`

Używana w sekcji „Czym jest notACMS?" na stronie głównej. Zawiera `.stat-card-num`, `.stat-card-label`, `.stat-card-desc`.

### `.prev-next-card` (wpisy na blogu)

Ten sam komponent `.prev-next-nav` jest używany do nawigacji poprzedni/następny we wpisach.

## Ikony Phosphor

Ikony są ładowane z CDN Phosphor Icons. Składnia: `<i class="ph ph-{nazwa}"></i>`.

| Ikona | Klasa | Zastosowanie |
|---|---|---|
| Otwarta książka | `ph-book-open` | Link „Podręcznik" w sidebarze |
| Struktura drzewa | `ph-tree-structure` | Link „Architektura" w sidebarze |
| Suwaki | `ph-sliders` | Link „Dostosowywanie" w sidebarze |
| Globus | `ph-globe` | Link „Języki" w sidebarze |
| Paleta | `ph-palette` | Link „Design" w sidebarze |
| Rakieta | `ph-rocket` | Link „Wydania" w sidebarze |
| Logo GitHub | `ph-github-logo` | Linki do GitHuba |
| Strzałka w prawo | `ph-arrow-right` | CTA, etykiety „następna" |
| Strzałka w lewo | `ph-arrow-left` | Etykiety „poprzednia" |
| Strzałka w kwadracie | `ph-arrow-square-out` | Linki zewnętrzne |
| Księżyc | `ph-moon` | Przełącznik motywu (tryb jasny) |
| Słońce | `ph-sun` | Przełącznik motywu (tryb ciemny) |
| Żarówka | `ph-lightbulb` | Wyróżnienia ze wskazówką |
| Strzałka w dół | `ph-caret-down` | Rozwijane menu dokumentacji |
| Błyskawica | `ph-lightning` | Statystyki, szybkość |
| Lupa | `ph-magnifying-glass` | Wyszukiwanie |
| Kod | `ph-code` | Funkcje kodu/PHP |
| Logo Markdown | `ph-markdown-logo` | Funkcja Markdown |
| Lista | `ph-list` | Mobilny przełącznik sidebara |
