---
name: notACMS Bare
version: alpha
description: Minimal, unopinionated wireframe theme for notACMS. Light-mode only, system fonts, no decorative chrome.
colors:
  accent: "#2563EB"
  accent-h: "#1d4ed8"
  white: "#ffffff"
  surface: "#f8f9fa"
  text: "#111827"
  text-muted: "#6b7280"
  border: "#e5e7eb"
  code-bg: "#f3f4f6"
  success: "#16a34a"
  danger: "#dc2626"
  card-bg: "#ffffff"
  card-border: "#e5e7eb"
typography:
  headline:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif"
    fontSize: 2rem
    fontWeight: 700
    lineHeight: 1.3
  body:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif"
    fontSize: 1rem
    fontWeight: 400
    lineHeight: 1.6
  caption:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif"
    fontSize: 0.875rem
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "ui-monospace, 'SF Mono', Monaco, Consolas, monospace"
    fontSize: 0.75rem
    fontWeight: 400
    lineHeight: 1.4
    letterSpacing: 0.02em
rounded:
  sm: 3px
  md: 4px
  lg: 6px
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
    textColor: "{colors.white}"
    rounded: "{rounded.md}"
    padding: 8px
  button-primary-hover:
    backgroundColor: "{colors.accent-h}"
    textColor: "{colors.white}"
  post-card:
    backgroundColor: "{colors.card-bg}"
    borderColor: "{colors.card-border}"
    rounded: "{rounded.sm}"
    padding: 16px
  contact-form-input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text}"
    borderColor: "{colors.border}"
    rounded: "{rounded.md}"
    padding: 8px
  alert-success:
    backgroundColor: "#dcfce7"
    textColor: "{colors.success}"
    borderColor: "#86efac"
    rounded: "{rounded.md}"
    padding: 12px
  alert-danger:
    backgroundColor: "#fee2e2"
    textColor: "{colors.danger}"
    borderColor: "#fca5a5"
    rounded: "{rounded.md}"
    padding: 12px
  cookie-banner:
    backgroundColor: "{colors.white}"
    textColor: "{colors.text}"
    borderColor: "{colors.border}"
    rounded: "{rounded.md}"
    padding: 12px
  tag-pill:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-muted}"
    borderColor: "{colors.border}"
    rounded: "9999px"
    padding: "2px 8px"
  badge:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-muted}"
    borderColor: "{colors.border}"
    rounded: "{rounded.sm}"
    padding: "2px 8px"
---

# Design System

## Overview

A minimal, unopinionated wireframe theme for notACMS. Light-mode only. System fonts. No decorative chrome. Every feature works out of the box, but the styling is intentionally bare so users can build their own design on top without fighting an existing aesthetic.

The design says: *"Here are all the pieces. Style them however you want."*

---

## Colors

The palette is deliberately small — two neutrals, one accent, two semantic colors.

- **Accent (#2563EB):** Links, primary buttons, active nav, focus outlines
- **Accent hover (#1d4ed8):** Hover and active states
- **Surface (#ffffff):** Page background
- **Surface secondary (#f8f9fa):** Elevated surfaces — alerts, buttons, badges, code blocks
- **Text (#111827):** Headings, body copy, primary content
- **Text muted (#6b7280):** Metadata, captions, dates, secondary information
- **Border (#e5e7eb):** Dividers, input borders, card separators, table borders
- **Success (#16a34a):** Form validation success, positive indicators
- **Danger (#dc2626):** Validation errors, destructive actions
- **Code background (#f3f4f6):** Inline code and preformatted blocks

### Component tokens

Cards and elevated elements use **{colors.card-bg}** (#ffffff) and **{colors.card-border}** (#e5e7eb), identical to surface and border by default. Override these in `local/assets/styles/` to give cards distinct styling without touching component CSS.

---

## Typography

A single sans-serif stack for everything. Monospace reserved for code only.

- **Headline / body / UI font**: **{typography.headline.fontFamily}**
- **Code / metadata font**: **{typography.label.fontFamily}**
- **Heading sizes**: **{typography.headline.fontSize}** (H1), **1.5rem** (H2, section), **1.125rem** (H3, post titles)
- **Body size**: **{typography.body.fontSize}** with **{typography.body.lineHeight}** line height
- **Small**: **{typography.caption.fontSize}** (captions, dates, meta)
- **Extra small**: **{typography.label.fontSize}** (badges, tags, labels)

There is no custom font loading. System fonts render instantly and respect user preferences.

---

## Layout

The layout follows a **simple centered container** model. No grid system — components stack vertically with consistent spacing.

| Element | Max width | Notes |
|---|---|---|
| Default container | 720px | Centered, single column |
| Wide container | 960px | Used for pages that need more room |
| Sidebar | 220px | Hidden below **{spacing.lg}** breakpoint (768px) |
| Content padding | **{spacing.md}** | Horizontal padding on all containers |

Spacing is handled through utility classes (`.mt-5`, `.mb-3`) and component-level margins. No gap utilities are used in the base layout.

---

## Elevation & Depth

No shadows. Depth is conveyed through:
- **Border contrast** — 1px {colors.border} borders separate regions
- **Background hierarchy** — Surface (#ffffff) vs Surface-secondary (#f8f9fa)
- **Spacing** — larger gaps between sections instead of visual frames

This keeps the CSS small and avoids expensive shadow rendering.

---

## Shapes

All corners are minimally rounded:
- **Small (3px)**: Inputs, badges, small buttons — **{rounded.sm}**
- **Medium (4px)**: Buttons, alerts, cards — **{rounded.md}**
- **Large (6px)**: Modals, large cards, callouts — **{rounded.lg}**

The shape language is intentionally understated. Sharp enough to feel utilitarian, rounded enough to avoid looking broken.

---

## Components

### Layout

- **Container**: 720px max width centered, 960px for `--wide` variant
- **Content layout**: Flex row with main content (flex: 1) + 220px sidebar
- **Sidebar**: Hidden below 768px
- **Header**: Simple flex row with logo left, nav center/right, border-bottom separator
- **Footer**: Minimal — copyright + optional privacy policy link, border-top separator

### Navigation (`.site-header`)

- No glass effect, no blur, no backdrop-filter
- White background, 1px bottom border
- Logo is an inline SVG image (`height: 1.75rem`)
- Horizontal nav links on desktop, hamburger toggle on mobile
- No search overlay — search widget lives in sidebar only

### Post card (`.post-card`)

- Uses **{components.post-card}** tokens: white background, 1px border, minimal radius
- Composed with utility classes: `.border-bottom`, `.mb-3`, `.pb-3`
- No card background, no border-radius on the card itself, no shadow
- Meta line (category + date) in **{colors.text-muted}**
- Title is a plain text link (no underline until hover)
- Footer uses `.flex-between` to space "Read more" and tags

### Post navigation (`.post-navigation`)

- Composed with `.flex-between`, `.border-top`, `.pt-4`
- Two equal-width links with 1px border
- Direction label in `.text-xs`, muted color
- No custom component CSS beyond the anchor styling

### Sidebar widgets (`.widget`)

- No card styling — list of plain text links
- Section headings in small caps look via uppercase + small font
- Active link in **{colors.accent}** with bold weight
- Widgets separated by border-top on second and later widgets

### Alerts (`.alert`)

- Four variants using component tokens:
  - Success: **{components.alert-success}** — light green tint, green border
  - Danger: **{components.alert-danger}** — light red tint, red border
  - Warning, Info: Similar tint + border pattern
- No icon — text only
- Used for contact form success/error, draft/scheduled banners

### Contact form (`.contact-form`)

- Stacked labels + inputs, no multi-column layout
- Inputs use **{components.contact-form-input}** tokens: surface background, 1px border, medium radius
- Error text in **{colors.danger}** below each field
- Submit button uses **{components.button-primary}** — accent fill, white text

### Cookie Banner (`.cookie-banner`)

- Fixed bottom bar using **{components.cookie-banner}** tokens
- White background, 1px top border, medium radius
- Accept button uses accent fill
- Dismiss is a text-only close button

### Tags & Badges (`.tag-pill`, `.badge`)

- Both use pill-shaped rounded corners (**9999px**)
- **{components.tag-pill}**: surface background, muted text, 1px border
- **{components.badge}**: same tokens, slightly smaller
- Hover state: border shifts to **{colors.accent}**

### Buttons

Two variants exist:
- **Primary**: **{components.button-primary}** — accent background, white text
- **Primary hover**: **{components.button-primary-hover}** — darker accent
- **Outline**: Transparent background, text color, 1px border

Both use **{rounded.md}** (4px) corners.

---

## Do's and Don'ts

- **Do** use the utility classes in `_utilities.scss` before writing new component CSS
- **Do** override tokens in `local/assets/styles/` to recolor without editing core files
- **Do** keep the font stack as system fonts — bare theme has no external assets
- **Do** test on small screens — bare theme uses the same breakpoints but simpler layout
- **Don't** add dark mode to bare — create a custom theme in `local/` if you need it
- **Don't** use drop shadows — the design is flat by convention
- **Don't** import Google Fonts or custom icon sets — the minimal aesthetic depends on system rendering
- **Don't** create monolithic component CSS when utilities will do
