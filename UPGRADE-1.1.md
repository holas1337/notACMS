# UPGRADE FROM `1.0` TO `1.1`

## Core template redesign

The default templates, styles, and assets have been significantly redesigned. If you use core templates without any `local/` overrides, your site will adopt the new look automatically after upgrading.

**If you want to keep the old look**, use the compatibility package included in this release:

```bash
cp -r docs/customization/old-template/. local/
ddev build
```

See `docs/customization/old-template/README.md` for details, including how to merge any previous customisations you had on top of the old template.

---

### SCSS local entrypoint renamed

**Breaking if you have `local/assets/styles/local.scss`.**

The SCSS entrypoint for local overrides has been renamed from `local.scss` to `app_local.scss` to match the importmap entrypoint name `app-local`.

```
# Before
local/assets/styles/local.scss

# After
local/assets/styles/app_local.scss
```

Rename the file and update any reference to it in your `local/assets/app.js`:

```js
// Before
import './styles/local.scss';

// After
import './styles/app_local.scss';
```

---

### Translation keys removed

**Breaking if your templates reference any of these keys directly.**

The following translation keys have been removed from core:

| Key | Reason |
|-----|--------|
| `header.tagline` | Tagline removed from header layout |
| `nav.projects` | Projects removed from main navigation |
| `nav.search` | Search moved to sidebar |
| `blog.published_on` | Replaced by date shown inline in meta line |
| `blog.comments_disabled` | Comments feature removed from templates |

If your `local/translations/messages.*.yaml` defines these keys for UI strings, the entries are harmless but unused — no action required. If your custom templates use `'...'|trans` on these keys, update them to remove or replace those calls.

**New keys added:**

| Key | Value |
|-----|-------|
| `nav.main` | `Main navigation` (ARIA label) |
| `nav.prev` | `← Previous` |
| `nav.next` | `Next →` |
| `sidebar.search` | `Search` |

Add these to your `local/translations/messages.*.yaml` if you override those files entirely.

---

### SCSS variables replaced with CSS custom properties

**Breaking if your custom SCSS uses `$color-*` variables from core.**

Core colour SCSS variables have been replaced with CSS custom properties (design tokens). If your `local/assets/styles/` references core colour variables, update them:

| Before | After |
|--------|-------|
| `$color-body` | `var(--text)` |
| `$color-body-bg` | `var(--bg)` |
| `$color-link` | `var(--accent)` |
| `$color-link-hover` | `var(--accent-h)` |
| `$color-dark` | `var(--heading)` |
| `$color-border` | `var(--border)` |
| `$color-muted` | `var(--muted)` |

SCSS compile-time variables (`$font`, `$fs-base`, `$lh`, spacing, breakpoints) are unchanged.

---

### New core SCSS partials

Two new partials have been added to core:

- `_tokens.scss` — CSS custom properties (`:root` colour tokens, dark mode)
- `_prose.scss` — `.prose` / `.prose--post` styles for rendered Markdown

If your `local/assets/styles/app_local.scss` imports all core partials manually (unusual), add these to your import order after `_variables.scss`.

---

### `docs/examples/` directory removed

**Breaking if you referenced `docs/examples/` paths from your own scripts or docs.**

The old `docs/examples/` tree has been removed. Its content was reorganised as follows:

- Demo content, templates, and translations → `docs/customization/old-template/` (use it as the compatibility package above).
- Scratch template-override examples (`block-override`, `full-override`, `material-cards`, `starter-extend`, `starter-full-override`) → removed; use the bare core as the baseline and copy `docs/demo/` files into `local/` selectively instead.

Update any bookmarks or scripts pointing at `docs/examples/` accordingly.

---

## Non-breaking changes

### Excerpt no longer includes heading anchor text

Heading anchors (`<a class="heading-anchor">`) are now stripped before generating post excerpts. Excerpts in blog listing pages will no longer contain `#` characters that were previously leaked from anchor links.

### Contact form labels use translation keys directly

`ContactType` no longer pre-translates form labels via `TranslatorInterface`. Labels are now passed as translation keys and resolved by Symfony's form rendering layer. Behaviour is identical; this is an internal refactor with no user-visible impact.
