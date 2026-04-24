---
name: add-locale
description: Add a completely new language/locale to any notACMS instance. Use when the user asks to "add locale", "add language", "add support for", or similar.
allowed-tools: Read, Glob, Grep, Bash, Write, Edit
---

# Skill: Add New Locale to notACMS

## Description

Add a completely new language/locale to any notACMS instance. This skill teaches the architectural patterns and mechanisms for i18n — applicable to bare wireframe or full demo setups.

## Usage

```
@add-locale {locale-code} {language-name}

Example: @add-locale es "Español"
Example: @add-locale it "Italiano"
Example: @add-locale ja "日本語"
```

## Prerequisites

Before adding a locale, understand your content structure:

- **Bare setup**: Core templates in `templates/`, content in `local/content/`
- **Demo/override setup**: Local templates in `local/templates/`, additional translations in `local/translations/`
- **Content inventory**: List existing pages, blog posts, index files that need translation

### Default Locale

The **first key** in `site.locales` becomes the default. Examples assume `{default}` — replace with your actual default locale code.

```yaml
site:
  locales:
    {default}:              # Default (first key) — could be pl, de, etc.
      label: "{Default Language}"
    es:                     # Additional locale
      label: "Español"
```

In this example, `pl` is default, paths are `/` (homepage) and `/page/` (not `/pl/page/`).

---

## Architecture: How notACMS i18n Works

### Component Flow

```
Request URL: /es/manual/
    ↓
LocalizedRouteLoader → matches route `static_page_es`
    ↓
PageController receives `$locale='es'`, `$slug='manual'`
    ↓
ContentService::findByUrl('/es/manual/', 'es') queries ContentTree
    ↓
ContentTreeBuilder (cached per locale) returns ContentItem
    ↓
Twig renders template with `translation_map` global for language switcher
    ↓
Browser: locale-redirect.js runs (if no cookie set)
```

### Key Services

| Service | Purpose |
|---------|---------|
| `SiteConfigService` | Reads `_site.yaml`, provides `getLocales()`, `getDefaultLocale()` |
| `LocalizedRouteLoader` | Generates `{route}_{locale}` routes dynamically |
| `ContentTreeBuilder` | Scans `local/content/` for `{locale}.md` files, builds per-locale tree |
| `TranslationMapBuilder` | Creates `translation_map` for cross-locale linking |
| `SiteRuntimeData` | Twig global providing `site_locales_list`, `translation_map` |

---

## Phase 1: Register the Locale

### Site Configuration (`_site.yaml`)

The **first key** under `site.locales` becomes the default locale.

```yaml
site:
  locales:
    {default}:                     # Default (first key)
      label: "{Default Language}"
      og_locale: "{default}_{REGION}"
      date_format: "M d, Y"
      tagline: "..."
    es:                     # Additional locale
      label: "Español"
      og_locale: es_ES
      date_format: "d/m/Y"
      tagline: "..."
```

**Fields:**
- `label` — Displayed in language switcher
- `og_locale` — Open Graph format (`{locale}_{REGION}`)
- `date_format` — PHP date format string
- `tagline` — Optional site tagline

### Route Configuration (`_routes.yaml`)

Controls URL patterns per locale. Routes **not listed** get auto-prefixed: `/blog/` → `/es/blog/`

```yaml
routes:
  blog_list:
    es: /articulos/        # Override auto-prefix
  search:
    es: /buscar/           # Override auto-prefix
```

**Pattern:** Translate semantic meaning, keep structure:
- `/blog/page/{page}/` → `/articulos/pagina/{page}/`
- Keep parameters (`{page}`, `{slug}`) untranslated

---

## Phase 2: Create Translation Files

### File Locations

Translations load from (priority order):
1. `translations/messages.{locale}.yaml` — Core (bare minimum)
2. `local/translations/messages.{locale}.yaml` — Site-specific extensions

### Translation Key Patterns

Keys use dot notation, grouped by feature:

```yaml
nav:
  home: "Inicio"                    # Top navigation
  blog: "Blog"
  
blog:
  read_more: "Leer más"             # Post cards
  published_on: "Publicado el"
  reading_time: "%count% min"         # Variables untranslated
  
contact:
  form:
    name: "Nombre"                   # Form fields
    email: "Correo"
    send: "Enviar"
  about_nudge: '¿No estás seguro? <a href="%about_url%">Más info</a>'
```

**Template usage:**
```twig
{{ 'blog.reading_time'|trans({'%count%': minutes}) }}
{{ 'contact.about_nudge'|trans({'%about_url%': content_url('about', locale)})|raw }}
```

### Translation Rules

**Translate values, keep structure:**
- ✓ All labels, buttons, headings, descriptions
- ✓ Form field names, placeholders, validation messages
- ✓ Error messages, empty states, callouts

**Keep untranslated:**
- ✗ Variable placeholders (`%count%`, `%query%`, `%about_url%`)
- ✗ HTML tags inside values (preserve `<a href="...">`, `<em>`)
- ✗ File paths in examples (`local/content/`)
- ✗ Configuration keys (`_site.yaml`)
- ✗ CSS class names, PHP class names

---

## Phase 3: Content Translation

### URL Generation Algorithm

`ContentTreeBuilder::computeUrl()` determines the URL:

```php
$slug = $frontMatter['slug'] ?? '';          // From frontmatter

if ($locale !== $defaultLocale) {
    $slug = $slug === '' ? $locale : "$locale/$slug";
}

return "/$slug/";                            // Final URL
```

**Examples:**

| File | Slug | Default Locale | Generated URL |
|------|------|----------------|---------------|
| `manual/{default}.md` | `manual` | {default} | `/manual/` |
| `manual/es.md` | `manual` | {default} | `/es/manual/` |
| `manual/es.md` | `guia` | {default} | `/es/guia/` |
| `home/es.md` | `` (empty) | {default} | `/es/` |

**Key insight:** Translate `slug` to localize URLs, keep consistent to preserve paths.

### Content Matching (Translation Map)

The `TranslationMapBuilder` links content across locales by `directoryKey`:

```
pages/manual/
├── {default}.md    → directoryKey: "manual", url: "/manual/"
└── es.md           → directoryKey: "manual", url: "/es/guia/"
                     ↓
            translation_map['manual'] = [
                '{default}' => '/manual/',
                'es' => '/es/guia/',
            ]
```

**Requirement:** Both locales must have files in **same directory** for matching.

### Default Locale and Placeholder

Throughout this skill, `{default}` represents the default locale code (first key in `_site.yaml`). Determine your actual default:

```bash
# Get default locale
grep -A1 "^  [a-z][a-z]:" local/content/_site.yaml | head -2

# Or programmatically
php -r "echo yaml_parse_file('local/content/_site.yaml')['site']['locales'][0] ?? '{default}';"
```

**Examples in this skill use `{default}` — replace with your actual default locale code.**

**Consistent across locales:**
```yaml
template: page/doc              # Same template
menu:
  weight: 20                    # Same position (sort order)
```

**Translate per locale:**
```yaml
title: "Guía de Usuario"        # Translate
description: "Cómo instalar..." # Translate  
slug: "guia"                    # Optional: translate for localized URLs
menu:
  label: "Guía"                 # Translate
```

### Content Body Translation

**Translate prose, keep code:**

```markdown
## Configuración Rápida                  ← Translate heading

Para instalar, ejecuta estos comandos:   ← Translate paragraph

```bash
# Clone repository - comment in code stays English
git clone https://github.com/...       ← Keep code English
```

| Element | Action |
|---------|--------|
| Headings (`#`, `##`) | Translate |
| Paragraphs, lists | Translate |
| Table headers | Translate |
| Table cells (descriptions) | Translate |
| Table cells (field names) | Keep English (`slug`, `template`) |
| Code blocks | Keep English |
| File paths | Keep English (`local/content/`) |
| Configuration examples | Keep English (`_site.yaml`) |

---

## Phase 4: Browser Language Handling

### locale-redirect.js Mechanism

```javascript
// 1. Check 'lang' cookie for saved preference
// 2. No cookie? Detect browser language
// 3. Match browser lang against configured locales
// 4. Set cookie, redirect to matched locale
// 5. If cookie ≠ current URL locale: redirect to cookie locale
```

**Preventing loops:**
- Language switcher sets cookie BEFORE navigation: `cookie='lang=es; path=/'`
- After redirect, cookie matches URL → no further redirects

**Template:** Language switcher links must have class `lang-switch`:
```html
<a href="/es/manual/" class="lang-switch" hreflang="es">ES</a>
```

---

## Phase 5: Verification Patterns

### Check Locale Registration

```bash
# Verify locale is in site config
grep -A3 "^    {locale}:" local/content/_site.yaml

# Check sitemap contains locale URLs
curl -s https://site/sitemap.xml | grep "/{locale}/"
```

### Check Content Resolution

```bash
# Test page exists
curl -s -o /dev/null -w "%{http_code}" https://site/{locale}/{page}/
# Expected: 200

# Check translation map (hreflang links)
curl -s https://site/{en-page}/ | grep 'hreflang="{locale}"'
# Expected: <link rel="alternate" hreflang="{locale}" href=".../{locale}/...">
```

### Clear Caches

```bash
# Content tree cache (most important)
ddev exec php bin/console cache:pool:clear app.content

# Full Symfony cache
ddev exec php bin/console cache:clear
```

---

## Phase 6: Common Issues

### 404 on Translated Page

**Symptoms:** URL returns 404, content not found.

**Check:**
1. File exists: `local/content/pages/{name}/{locale}.md`
2. Frontmatter valid YAML (no tabs, proper indentation)
3. Slug value matches expected URL path
4. Cache cleared

**Debug:**
```bash
# Check what content tree contains for locale
ddev exec php -r "
require 'vendor/autoload.php';
\$app = new \App\Kernel('dev', true);
\$app->boot();
\$cs = \$app->getContainer()->get(\NotACms\Service\Content\ContentServiceInterface::class);
\$tree = \$cs->getTree('{locale}');
foreach (\$tree->getAllPages() as \$p) {
    echo \$p->url() . ' => ' . \$p->frontMatter()['title'] ?? 'NO TITLE' . PHP_EOL;
}
"
```

### Language Switcher Links to Homepage

**Symptoms:** Switcher always links to `/{locale}/` instead of translated page.

**Cause:** Translation map missing entry — EN and {locale} files not matched.

**Fix:** Ensure both files in **same directory** with matching directory keys.

### Empty Page (Title Only, No Body)

**Symptoms:** Page loads but shows only title, no content.

**Cause:** File has frontmatter but no content after closing `---`.

**Fix:** Add content after frontmatter:

```markdown
---
title: "Page"
slug: page
---

This content appears.               ← Required: at least one paragraph
```

### Translation Keys Not Working

**Symptoms:** Text appears as key (e.g., `nav.home` instead of "Inicio").

**Check:**
1. Key exists in `messages.{locale}.yaml`
2. YAML indentation correct (2 spaces, no tabs)
3. No syntax errors in YAML (run `php -r "yaml_parse_file(...);"`)
4. Cache cleared

---

## Reference: Translate vs Keep

| Element | Action | Example |
|---------|--------|---------|
| **Configuration** |||
| `label` in `_site.yaml` | Translate | `"Español"` |
| `og_locale` | Keep format | `es_ES` |
| `date_format` | Keep pattern | `"d/m/Y"` |
| **Frontmatter** |||
| `title` | Translate | `"Guía"` |
| `description` | Translate | `"Cómo..."` |
| `slug` | Optional | `"guia"` or `"manual"` |
| `template` | Keep | `page/doc` |
| `menu.label` | Translate | `"Guía"` |
| `menu.weight` | Keep | `20` |
| **Translations** |||
| Key names | Keep | `nav.home` |
| Values | Translate | `"Inicio"` |
| Variables (`%count%`) | Keep | `%count%` |
| **Content Body** |||
| Headings | Translate | `## Configuración` |
| Paragraphs | Translate | `Para instalar...` |
| Code blocks | Keep | `git clone ...` |
| File paths | Keep | `local/content/` |
| CSS/PHP references | Keep | `.container`, `ContentService` |

---

## Summary Checklist

- [ ] Added locale to `_site.yaml` with required fields
- [ ] Added route overrides to `_routes.yaml` (translated paths)
- [ ] Created `messages.{locale}.yaml` in `translations/`
- [ ] Created `messages.{locale}.yaml` in `local/translations/` (if site-specific strings exist)
- [ ] All translation keys translated (no English values)
- [ ] Created `{locale}.md` for each content page in same directories as EN
- [ ] Slugs translated where localized URLs desired
- [ ] Menu weights match EN (sort order consistency)
- [ ] Created `_index_{locale}.md` for blog/section indices
- [ ] Index slugs translated to match route overrides
- [ ] Cleared content cache
- [ ] All URLs return 200
- [ ] Language switcher links to correct translated URLs
- [ ] No English text visible on translated pages