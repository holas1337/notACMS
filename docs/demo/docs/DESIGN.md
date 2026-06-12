---
version: alpha
name: notACMS Amber
description: Terminal-inspired amber phosphor aesthetic with dark-mode support and always-dark nav, hero, and footer.
colors:
  accent: "#B45309"
  accent-h: "#92400E"
  accent-dark: "#FFB000"
  accent-h-dark: "#FFCA28"
  surface: "#ffffff"
  surface-secondary: "#f8f9fa"
  surface-elevated: "#f1f3f5"
  surface-dark: "#0e0d0b"
  surface-secondary-dark: "#161410"
  surface-elevated-dark: "#1e1c17"
  text: "#212529"
  text-dark: "#f8f9fa"
  text-muted: "#6c757d"
  text-muted-dark: "#9ca3af"
  border: "#dee2e6"
  border-dark: "#2d2a24"
  code-bg: "#f6f8fa"
  code-bg-dark: "#0d1117"
  success: "#276749"
  success-dark: "#9ae6b4"
  danger: "#e53e3e"
  danger-dark: "#fc8181"
  nav-bg: "rgba(14, 13, 11, 0.95)"
  nav-text: "#f8f9fa"
  nav-muted: "#9ca3af"
  terminal-bg: "#0d1117"
  terminal-bar-bg: "#161b22"
  terminal-border: "#21262d"
  terminal-text: "#8b949e"
  terminal-cmd: "#e6edf3"
  terminal-comment: "#4b5563"
  terminal-output: "#6b7280"
  terminal-dot-close: "#ff5f57"
  terminal-dot-min: "#ffbd2e"
  terminal-dot-max: "#28c840"
  overlay-bg: "#00000099"
  divider-on-dark: "#ffffff14"
  nav-sep: "#ffffff26"
  btn-outline-hover-border: "#ffffff4d"
  footer-bg: "#0e0d0b"
  footer-text: "#f8f9fa"
  hero-accent: "#FFB000"
  hero-accent-h: "#FFCA28"
  hero-text: "#f8f9fa"
  hero-muted: "#f8f9faa6"
  hero-glow: "#ffb0001f"
  hero-glow-2: "#ffb0000f"
  hero-grid: "#ffffff06"
  on-accent: "#0e0d0b"
  primary: "{colors.accent}"
  sidebar-bg: "#f8f9fa"
  sidebar-bg-dark: "#111009"
  accent-bg: "#fffbeb"
  accent-bg-dark: "rgba(255, 176, 0, 0.08)"
  accent-border: "rgba(180, 83, 9, 0.2)"
  accent-border-dark: "rgba(255, 176, 0, 0.2)"
  hero-bg: "#0e0d0b"
  footer-muted: "#9ca3af"
  footer-border: "rgba(255, 255, 255, 0.06)"
  nav-border: "rgba(255, 255, 255, 0.06)"
  nav-subtle-bg: "rgba(255, 255, 255, 0.05)"
  nav-hover-bg: "rgba(255, 255, 255, 0.07)"
  nav-active-bg: "rgba(255, 255, 255, 0.1)"
  nav-active-border: "rgba(255, 255, 255, 0.18)"
  code-border: "#d0d7de"
  code-border-dark: "#21262d"
  code-text: "#24292f"
  code-text-dark: "#c9d1d9"
  code-comment: "#6e7781"
  code-comment-dark: "#4b5563"
  code-label-bg: "#eaeef2"
  code-label-bg-dark: "#161b22"
typography:
  headline:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: 700
    lineHeight: 1.15
  body:
    fontFamily: Inter
    fontSize: 17px
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: 500
    lineHeight: 1
    letterSpacing: 0.1em
  code:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.6
rounded:
  sm: 4px
  md: 8px
  lg: 10px
  xl: 12px
  xxl: 16px
  pill: 50rem
spacing:
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  xxl: 48px
components:
  button-primary:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.on-accent}"
    rounded: "{rounded.md}"
    padding: 12px
  button-primary-hover:
    backgroundColor: "{colors.accent-h}"
    textColor: "{colors.on-accent}"
  button-primary-dark:
    backgroundColor: "{colors.accent-dark}"
    textColor: "{colors.on-accent}"
    rounded: "{rounded.md}"
    padding: 12px
  button-primary-dark-hover:
    backgroundColor: "{colors.accent-h-dark}"
    textColor: "{colors.on-accent}"
  card:
    backgroundColor: "{colors.surface}"
    borderColor: "{colors.border}"
    rounded: "{rounded.xl}"
    padding: 32px
  card-dark:
    backgroundColor: "{colors.surface-dark}"
    borderColor: "{colors.border-dark}"
    rounded: "{rounded.xl}"
    padding: 32px
  card-hover:
    borderColor: "{colors.accent}"
  tag-pill:
    backgroundColor: "{colors.surface-elevated}"
    textColor: "{colors.text-muted}"
    rounded: "{rounded.pill}"
    padding: 8px
  tag-pill-dark:
    backgroundColor: "{colors.surface-elevated-dark}"
    textColor: "{colors.text-muted-dark}"
    rounded: "{rounded.pill}"
    padding: 8px
  alert-success:
    backgroundColor: "rgba(72, 187, 120, 0.1)"
    textColor: "{colors.success}"
    borderColor: "#48bb7859"
    rounded: "{rounded.lg}"
    padding: 12px
  alert-success-dark:
    backgroundColor: "rgba(72, 187, 120, 0.08)"
    textColor: "{colors.success-dark}"
    borderColor: "#48bb7833"
    rounded: "{rounded.lg}"
    padding: 12px
  alert-danger:
    backgroundColor: "rgba(229, 62, 62, 0.08)"
    textColor: "{colors.danger}"
    borderColor: "#e53e3e4d"
    rounded: "{rounded.lg}"
    padding: 12px
  alert-danger-dark:
    backgroundColor: "rgba(229, 62, 62, 0.07)"
    textColor: "{colors.danger-dark}"
    borderColor: "#e53e3e33"
    rounded: "{rounded.lg}"
    padding: 12px
---

# Design System

## Overview

A focused, information-dense documentation site for a PHP static site generator. Terminal-inspired aesthetic: amber phosphor accent on always-dark surfaces (nav, hero, footer), light content areas with full dark-mode support via `[data-theme="dark"]`. Clean lines, low visual noise, high readability. The design says "developer tool, not marketing page."

The accent color shifts between modes: deep amber **{colors.accent}** in light, bright amber **{colors.accent-dark}** in dark. Nav, hero, and footer are always-dark regardless of theme toggle. All colors are CSS custom properties — override `local/assets/styles/` to retheme.

Typography is strictly dual: **Inter** for everything human-readable, **JetBrains Mono** for code, metadata, labels, and monospaced UI elements. No other font families.

---

## Colors

The palette is rooted in high-contrast neutrals and a single, evocative accent color.

- **Primary accent** (**{colors.accent}** light / **{colors.accent-dark}** dark): CTAs, links, active states, section labels, callout icons
- **On-accent** (**{colors.on-accent}**): Text placed on accent-colored backgrounds
- **Surface** (**{colors.surface}** light / **{colors.surface-dark}** dark): Page background, outermost layer
- **Surface-secondary** (**{colors.surface-secondary}** light / **{colors.surface-secondary-dark}** dark): Sidebar, cards, elevated sections
- **Surface-elevated** (**{colors.surface-elevated}** light / **{colors.surface-elevated-dark}** dark): Buttons, inline code background, tag pills
- **On-surface** (**{colors.text}** light / **{colors.text-dark}** dark): Primary text, headings, body copy
- **On-surface-muted** (**{colors.text-muted}** light / **{colors.text-muted-dark}** dark): Secondary text, captions, dates, meta labels
- **Outline** (**{colors.border}** light / **{colors.border-dark}** dark): Card borders, dividers, input borders, separator lines
- **Success** (**{colors.success}** light / **{colors.success-dark}** dark): Form validation, positive indicators
- **Error** (**{colors.danger}** light / **{colors.danger-dark}** dark): Validation errors, destructive actions

### Always-dark surfaces

These tokens are fixed and never change with the theme toggle. The nav background uses a glass effect (`backdrop-filter: blur`) with 95% opacity on top of **{colors.nav-bg}**.

| Token | Value | Usage |
|---|---|---|
| Nav background | **{colors.nav-bg}** with glass | Fixed top bar |
| Hero background | **{colors.nav-bg}** | Full-bleed dark section |
| Footer background | **{colors.footer-bg}** | Bottom bar |
| Hero accent | **{colors.hero-accent}** | Same as dark-mode accent |
| Hero text | **{colors.hero-text}** | Always-light text on dark |
| On-accent | **{colors.on-accent}** | Dark text on accent buttons |

### Terminal colors

The terminal block (`.terminal`) is always-dark and uses its own isolated palette independent of the theme toggle.

| Token | Value | Usage |
|---|---|---|
| `--terminal-bg` | **{colors.terminal-bg}** | Terminal window background |
| `--terminal-bar-bg` | **{colors.terminal-bar-bg}** | Title bar strip |
| `--terminal-border` | **{colors.terminal-border}** | Window border |
| `--terminal-text` | **{colors.terminal-text}** | Dimmed prompt / secondary output |
| `--terminal-cmd` | **{colors.terminal-cmd}** | Active command / primary output text |
| `--terminal-comment` | **{colors.terminal-comment}** | Comment / inactive text in terminal |
| `--terminal-output` | **{colors.terminal-output}** | Secondary output / indented result text |
| `--terminal-dot-close` | **{colors.terminal-dot-close}** | Red traffic-light dot |
| `--terminal-dot-min` | **{colors.terminal-dot-min}** | Yellow traffic-light dot |
| `--terminal-dot-max` | **{colors.terminal-dot-max}** | Green traffic-light dot |

### UI overlays and dividers

Utility tokens used on always-dark surfaces for interactive and decorative elements. All are theme-independent (defined once, not overridden per theme).

| Token | Value | Usage |
|---|---|---|
| `--overlay-bg` | `#00000099` | Search overlay backdrop |
| `--divider-on-dark` | `#ffffff14` | Separator lines on dark surfaces (e.g. hero stat strip) |
| `--nav-sep` | `#ffffff26` | Language switcher `/` separator in nav |
| `--btn-outline-hover-border` | `#ffffff4d` | `.btn-outline` border on hover (always-dark hero) |

### Accent variants

Light mode:
- `--accent`: **{colors.accent}** | `--accent-h`: **{colors.accent-h}**
- `--accent-bg`: `#fffbeb` (tinted background)
- `--accent-border`: `rgba(180, 83, 9, 0.2)`

Dark mode:
- `--accent`: **{colors.accent-dark}** | `--accent-h`: **{colors.accent-h-dark}**
- `--accent-bg`: `rgba(255, 176, 0, 0.08)`
- `--accent-border`: `rgba(255, 176, 0, 0.2)`

---

## Typography

The typography strategy leverages two distinct font families: **Inter** for narrative and **JetBrains Mono** for technical data.

- **Headlines:** Set in Inter Semi-Bold to establish an institutional and trustworthy voice. Size **{typography.headline.fontSize}** with tight **{typography.headline.lineHeight}** line height.
- **Body:** Inter Regular at **{typography.body.fontSize}** ensures contemporary professionalism and long-form readability. Line height **{typography.body.lineHeight}**.
- **Labels:** JetBrains Mono at **{typography.label.fontSize}** with **{typography.label.letterSpacing}** letter spacing. Strictly uppercase for section headers and metadata.
- **Code:** JetBrains Mono Regular at **{typography.code.fontSize}** for code blocks, inline code, and terminal output.

### Larger headings

H1 hero uses `clamp(2.5rem, 6vw, 4.5rem)`. Section headings use `clamp(1.75rem, 3.5vw, 2.5rem)`.

---

## Layout

The layout follows a **Fluid Grid** model for mobile (single column) and a **Fixed-Max-Width Grid** for desktop (max 1160px default, 1400px for docs pages).

A strict **{spacing.sm}** spacing scale (with **{spacing.xs}** half-step for micro-adjustments) maintains consistent rhythm. Components are grouped using containment principles — related items in cards with generous internal padding (**{spacing.lg}**) to emphasize the soft, approachable nature of the brand.

| Element | Max width | Notes |
|---|---|---|
| Default container | 1160px | Centered, single column on mobile |
| Docs container | 1400px | Wider for three-column docs layout |
| Sidebar | 240px | Collapses below 992px |
| TOC | 220px | Right-side table of contents |
| Nav height | 62px | Fixed top bar |
| Content padding | **{spacing.xl}** | Page horizontal margins |

---

## Elevation & Depth

Depth is achieved through **Tonal Layers** rather than heavy shadows. The background uses a soft off-white (**{colors.surface-secondary}**) in light mode or very dark (**{colors.surface-secondary-dark}**) in dark, while primary content sits on pure white (**{colors.surface}**) or near-black (**{colors.surface-dark}**) cards.

Shadows are reserved for specific floating-surface interactions only. The shadow scale exists in SCSS but is rarely used:
- `$shadow-sm`: 0 1px 2px rgba(0, 0, 0, .06) — unused
- `$shadow`: 0 2px 8px rgba(0, 0, 0, .08) — unused
- `$shadow-lg`: 0 8px 24px rgba(0, 0, 0, .12) — dropdowns (docs nav, language switcher)
- `$shadow-xl` — search overlay panel

The header uses a glass effect (`backdrop-filter: blur`) rather than shadows. Cards use 1px borders with `--card-border` / `--border` and `--card-bg` / `--bg-surface` backgrounds.

---

## Shapes

The shape language is defined by **Architectural Softness**. All interactive elements, containers, and inputs utilize a minimal corner radius sufficient to feel modern while maintaining a clean, engineered aesthetic.

| Role | Size | Token |
|---|---|---|
| Small | **{rounded.sm}** | Inputs, small badges |
| Default | **{rounded.md}** | Buttons, standard cards |
| Medium | **{rounded.lg}** | Callouts, feature cards |
| Large | **{rounded.xl}** | About profiles, stat cards |
| Pill | **{rounded.pill}** | Tags, pills, category labels |

---

## Components

### Navigation (`.site-header`)

Always-dark glass bar, **{spacing.xl}** height, fixed top. Background: **{colors.nav-bg}** with `backdrop-filter: blur` and 95% opacity. Nav links use **{colors.nav-muted}** color, active link uses **{colors.hero-accent}**. Mobile: hamburger toggle reveals vertical menu. Language switcher shows locale codes (EN / DE / PL) with `/` separators.

### Sidebar (`.docs-sidebar`, `.sidebar-nav`)

Background: `--sidebar-bg` (light gray in light mode, near-black in dark). Contains `.sidebar-section` groups with `.sidebar-label` headings. Active link gets `.is-active` class with **{colors.accent}** color. Collapses on mobile below 992px breakpoint.

### Table of Contents (`.toc-list`)

Right-side TOC in docs layout. Auto-generated from h2/h3 headings. Active item tracked via IntersectionObserver. Uses **{colors.text-muted}** for inactive items, **{colors.accent}** for active.

### Cards (flat, bordered)

All card variants share the same pattern: `--card-bg` or `--bg-surface` background, 1px `--card-border` border, no box-shadow, **{rounded.xl}** border-radius. On hover: border shifts to **{colors.accent}**.

Uses **{components.card}** (light) and **{components.card-dark}** (dark) tokens. Hover variant: **{components.card-hover}** shifts border to accent.

- **`.release-card`**: Blog post cards in listings. **{rounded.xl}** border-radius. Contains `.release-card-meta` (date + badges), title, excerpt, `.read-more` link.
- **`.feature-card`**: Homepage feature grid. **{rounded.lg}** radius. Contains `.feature-icon` (40×40 accent-bg circle) + title + description.
- **`.recommendation-card`**: Testimonial quotes. **{rounded.xl}** radius. Contains `.recommendation-quote`, `.recommendation-author`, `.recommendation-role`.
- **`.about-profile`**: Author card on about page. **{rounded.xl}** radius. Flex layout with avatar + info.
- **`.stat-card`**: Homepage statistics. Large `.stat-card-num` + `.stat-card-label` + `.stat-card-desc`.

### Prev/Next Navigation (`.prev-next-nav`)

2-column grid at bottom of docs and blog posts. Each `.prev-next-card` has 1px border, **{rounded.lg}** radius, hover state shifts border to **{colors.accent}**. Contains `.prev-next-dir` (mono, **{typography.label.fontFamily}** uppercase label) and `.prev-next-title` (accent-colored). Single column on mobile.

### Callouts (`.callout`)

Blockquotes in `.prose` render as styled callouts. Amber-tinted background (`--accent-bg`), 1px `--accent-border`, **{rounded.lg}** radius. Contains `.callout-icon` (Phosphor icon in **{colors.accent}**) and `.callout-text`. Strong text inside callouts uses **{colors.accent}** color.

### Tags (`.tag-pill`, `.release-tag`, `.post-tag-label`)

Small mono-font labels. `.tag-pill` uses pill radius (**{rounded.pill}**), **{colors.surface-elevated}** background, 1px **{colors.border}** border. Badge variants use `--accent-bg` with **{colors.accent}** text and `--accent-border`.

Uses **{components.tag-pill}** (light) and **{components.tag-pill-dark}** (dark).

### Pagination (`.pagination`)

Centered flex row. `.page-link` uses mono font, **{rounded.md}** radius, **{colors.border}** outline. Active link gets **{colors.accent}** color and `--accent-bg` background on hover.

### Contact Form (`.contact-form`)

Left-aligned labels, 1px **{colors.border}** inputs with `--bg-surface` background, **{rounded.md}** radius. Error state uses **{colors.danger}** color. Submit button uses **{colors.hero-accent}** fill with **{colors.on-accent}** text (always-dark styling). Turnstile CAPTCHA widget below submit.

### Cookie Banner (`.cookie-banner`)

Fixed bottom bar. `--bg-elevated` background, **{colors.border}** top line. Accept button uses **{colors.hero-accent}** fill. Dismiss is a 28px bordered icon button.

### Search Overlay (`.search-overlay`)

Full-screen modal over content. Always-dark background. Pagefind integration. Input has **{colors.hero-accent}** focus border.

### Terminal Block (`.terminal`)

Always-dark, macOS-style terminal window used on the homepage to show example CLI output. Fixed palette independent of theme toggle — all colors reference `--terminal-*` tokens. Contains a `.terminal-bar` with three traffic-light dots (`.terminal-dot`) and a `.terminal-body` with monospaced command output. Command text uses **{colors.terminal-cmd}**; prompt / dimmed output uses **{colors.terminal-text}**.

### Hero (`.hero`)

Full-bleed, always-dark section. **{colors.nav-bg}** background, **{colors.hero-text}** for text. Decorative glow effect using **{colors.hero-glow}** gradients. Section headings use extra-bold weight (800) with `clamp()` responsive sizing.

### Section primitives (`.section`, `.section--surface`, `.section--dark`)

Layout sections with responsive padding (**{spacing.xxl}** → **{spacing.xl}** → **{spacing.lg}**). `.section--surface` uses `--bg-surface`. `.section--dark` overrides token variables to hero tokens so child components render correctly on dark backgrounds.

---

## Do's and Don'ts

- Do use CSS custom properties (`--accent`, `--bg`, `--text-muted`) — never hardcode hex values in templates or local SCSS overrides
- Do maintain WCAG AA contrast ratios (4.5:1 for normal text, 3:1 for large text)
- Do use the accent color sparingly — one primary CTA per screen
- Do keep nav, hero, and footer always-dark regardless of theme toggle
- Do use border and background contrast for card depth, not shadows
- Do use JetBrains Mono for metadata, dates, labels, and code — never for body text
- Don't use light-mode tokens on always-dark surfaces or dark-mode tokens on light surfaces
- Don't add `box-shadow` to cards — the design is flat by convention
- Prefer few font weights per screen; body copy stays regular (400), emphasis uses 600–700. (The homepage hero/stat sections deliberately span 400–800.)
- Don't mix Inter and JetBrains Mono in the same text block — each has its role
- Don't create new CSS classes in local overrides that duplicate component patterns — extend or override with custom properties

