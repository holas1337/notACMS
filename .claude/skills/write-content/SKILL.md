---
name: write-content
description: Create new content (blog posts, static pages, category indices) with correct frontmatter, slugs, locale parity, image directory, and trailing-newline guarantee. Use when the user asks to "write a post", "add a page", "create new content", "new blog post", "create category", or similar.
allowed-tools: Read, Glob, Grep, Bash, Write, Edit
---

# Skill: Write New Content

## Description

Create new notACMS content — blog posts, static pages, or category index files — with the correct frontmatter, URL slugs, locale parity, image directory, and trailing newline. Wraps the system rules in `docs/EDITOR_GUIDE.md`, the site-specific voice rules in `local/docs/EDITOR_GUIDE.md`, and the trailing-newline requirement enforced by the markdown parser.

## When to use

- "Write a blog post about X"
- "Add a new page for X"
- "Create a new category called X"
- "Add a new post to the {category} category"
- Anything that produces a new `.md` file under `local/content/`

## Usage Patterns

### Pattern 1: Blog Post

```
@write-content post {category}/{post-slug} "{title}"

Example: @write-content post general/my-first-post "My First Post"
Example: @write-content post releases/release-1-2-0 "Release 1.2.0"
```

Optional flags:
- `--locales=en,pl` (default: all configured locales)
- `--draft` (default: `false`)
- `--series={key} --order={n}`
- `--pinned={YYYY-MM-DD}`
- `--date={YYYY-MM-DD}` (default: today)

### Pattern 2: Static Page

```
@write-content page {page-slug} "{title}"

Example: @write-content page about-me "About Me"
Example: @write-content page projects "Projects"
```

Optional flags:
- `--locales=en,pl`
- `--menu-weight={n}` (omit to skip nav)
- `--template={name}` (default: `page/default`)

### Pattern 3: Category Index

```
@write-content category {category-slug} "{title}"

Example: @write-content category tutorials "Tutorials"
```

Creates `local/content/blog/{category-slug}/_index_{locale}.md` for every configured locale.

### Pattern 4: From Brief (Free-form)

```
@write-content "{free-form brief}"

Example: @write-content "blog post about how I picked the bare theme as the default, ~600 words, draft, category general"
```

Skill parses the brief to determine type, category, slug, and metadata.

---

## Phase 1: Setup & Discovery

### Step 1.1: Determine the active content directory

Always write to **`local/content/`** (which may be a symlink to `docs/{theme}/content/`).

```bash
# Verify content root
test -d local/content || { echo "ERROR: local/content not found"; exit 1; }

# Check if local/ is a symlink and to which theme
readlink local
```

### Step 1.2: Read site config

```bash
# Find configured locales and default locale
grep -A 20 '^site:' local/content/_site.yaml | grep -E 'default_locale|^\s+(en|pl|de|fr|es|it|ja):'
```

**Capture:**
- `default_locale` (drives locale-prefix-free slug for the default)
- All configured locales (each gets a `.md` file)

### Step 1.3: Read site-specific voice & metadata rules

```bash
# Site-specific writing guide (categories, tags, voice)
cat local/docs/EDITOR_GUIDE.md
```

**Capture:**
- Available categories and their localized slugs (EN ↔ PL, etc.)
- Approved tags
- Voice / register rules
- Image generation conventions

If `local/docs/EDITOR_GUIDE.md` does not exist, fall back to `docs/demo/docs/EDITOR_GUIDE.md`.

### Step 1.4: Read system rules (only when uncertain)

```bash
# System-level frontmatter reference
cat docs/EDITOR_GUIDE.md
```

---

## Phase 2: Resolve Target Path & Slugs

### Blog Post

```
local/content/blog/{category-dir}/{post-dir}/{locale}.md
```

- `{category-dir}` matches a directory under `local/content/blog/` (e.g. `general`, `releases`, `tutorials`).
- `{post-dir}` is kebab-case, locale-independent (the directory name does NOT include the locale).
- Each locale gets a separate `.md` file inside the same `{post-dir}`.

**Slug rules per locale:**

| Locale | Frontmatter `slug` | Resulting URL |
|---|---|---|
| Default (e.g. `en`) | `blog/my-post` | `/blog/my-post/` |
| Polish (`pl`) | `wpisy/moj-wpis` | `/pl/wpisy/moj-wpis/` |
| German (`de`) | `blog/mein-artikel` | `/de/blog/mein-artikel/` |
| French (`fr`) | `blog/mon-article` | `/fr/blog/mon-article/` |

The locale prefix in the URL is added by routing — never include it in `slug`.

The `wpisy/` segment for Polish is the conventional Polish translation of `blog/`. Match the existing site's convention by inspecting other `pl.md` files in the same theme.

### Static Page

```
local/content/pages/{page-dir}/{locale}.md
```

**Slug rules per locale:**

| Locale | Frontmatter `slug` | Resulting URL |
|---|---|---|
| Default (e.g. `en`) | `my-page` | `/my-page/` |
| Polish (`pl`) | `pl/moja-strona` | `/pl/moja-strona/` |
| German (`de`) | `de/meine-seite` | `/de/meine-seite/` |

**Note for pages:** in PL/DE/FR pages, the locale prefix IS part of the slug (different from blog posts). Inspect existing pages to confirm the site's convention.

### Category Index

```
local/content/blog/{category-dir}/_index_{locale}.md
```

**Slug rules:**

| Locale | Frontmatter `slug` | URL |
|---|---|---|
| Default (e.g. `en`) | `blog/{category}` | `/blog/{category}/` |
| `pl` | `wpisy/{kategoria}` | `/pl/wpisy/{kategoria}/` |

Index files have **frontmatter only, no body**. End the file with the closing `---` followed by a newline.

---

## Phase 3: Build Frontmatter

### Required fields (always)

| Field | Required for | Example |
|---|---|---|
| `title` | all | `"Release 1.2.0"` |
| `slug` | all | `"blog/release-1-2-0"` (locale-aware, see Phase 2) |
| `description` | recommended (used in meta + listings) | `"What's new in 1.2.0..."` |

### Blog post additions

| Field | Required | Example |
|---|---|---|
| `date` | yes | `2026-04-30` |
| `category` | yes | `general` (use the **localized** slug per locale) |
| `tags` | recommended | `[release, php]` (3–6 from the approved list) |
| `image` | recommended | `/media/{post-dir}/featured.webp` |
| `image_alt` | required if `image` set | `"Terminal showing release notes"` |
| `template` | no (default `blog/post`) | `blog/post` |
| `draft` | no (default `false`) | `false` |
| `series` / `series_order` | no | `series: my-series`, `series_order: 1` |
| `pinned` | no | `2026-06-01` (date until pinned) |
| `updated` | no | only for meaningful content updates, not typos |

### Static page additions

| Field | Required | Example |
|---|---|---|
| `template` | no (default `page/default`) | `page/default` |
| `menu.weight` | no | `50` (lower = earlier in nav) |
| `menu.label` | no (defaults to `title`) | `"My Page"` |

### Category index additions

| Field | Required | Example |
|---|---|---|
| `description` | recommended | `"Posts in the tutorials category."` |
| `template` | yes | `blog/list` |

---

## Phase 4: Write the File(s)

### Step 4.1: Create directory structure

```bash
# Blog post
mkdir -p local/content/blog/{category}/{post-slug}/files

# Static page
mkdir -p local/content/pages/{page-slug}

# Category index — only the category dir
mkdir -p local/content/blog/{category-slug}
```

The `files/` subdirectory under a blog post is for images (WebP only).

### Step 4.2: Compose frontmatter + body per locale

Use the Write tool. For each locale:

```markdown
---
title: "{Title in target locale}"
slug: "{slug per Phase 2 rules}"
date: 2026-04-30
category: {localized-category-slug}
description: "{Description in target locale, ~150–160 chars}"
tags: [{localized-tags-from-approved-list}]
image: /media/{post-slug}/featured.webp
image_alt: "{Visual description of the image}"
draft: false
template: blog/post
---

{Body in target locale — Markdown}
```

### Step 4.3: Trailing newline guarantee

**Every content file MUST end with `\n`.** This is non-negotiable — the League CommonMark FrontMatter parser silently drops frontmatter without a trailing newline after the closing `---`, producing empty `title`, `slug='`'`, and `url='/'` (catastrophic for `_index_*.md` files which have no body).

The `Write` tool typically appends a final newline automatically when the content ends with `\n` in the `content` argument. **Always include `\n` at the end of the `content` string when calling `Write`.**

For files written via shell or programmatically, verify with:

```bash
[ "$(tail -c 1 path/to/file.md | od -An -tx1 | tr -d ' ')" = "0a" ] && echo OK || echo FIX
```

To repair in-place:

```bash
[ -n "$(tail -c 1 path/to/file.md)" ] && printf '\n' >> path/to/file.md
```

### Step 4.4: Locale parity

Both (or all) locale files MUST be co-located in the same directory — this is what links translations together (no `translation_key` field needed).

```
local/content/blog/general/my-post/
    en.md       ← directoryKey='my-post', locale='en'
    pl.md       ← directoryKey='my-post', locale='pl'
    de.md       ← directoryKey='my-post', locale='de'
    files/
        featured.webp
```

Keep these fields identical across locales: `date`, `category` (untranslated reference), `series`, `series_order`, `tags` (count), `image`, `template`, `draft`, `pinned`, `featured`, `dynamic`. Translate: `title`, `slug`, `description`, `image_alt`, `category` (localized slug), `tags` (localized values), `menu.label`, body content.

If only one locale is requested, that's fine — the language switcher falls back to the other locale's homepage. But default to all configured locales unless the user says otherwise.

---

## Phase 5: Verification

### Step 5.1: Trailing newline check

```bash
for f in path/to/new/files/*.md; do
    last=$(tail -c 1 "$f" | od -An -tx1 | tr -d ' ')
    [ "$last" = "0a" ] && echo "OK: $f" || echo "MISSING NEWLINE: $f"
done
```

### Step 5.2: Frontmatter parses

```bash
ddev exec bin/console cache:pool:clear app.content
ddev exec curl -s -o /dev/null -w "%{http_code}\n" http://localhost{url}
```

Expect `200`. Then:

```bash
ddev exec curl -s http://localhost{url} | grep -E '<title>|<h1>' | head -3
```

Title and h1 must match the frontmatter `title`. **Empty `<title>` or `<h1>` indicates the parser dropped the frontmatter — re-check the trailing newline.**

### Step 5.3: Translation linkage

```bash
ddev exec curl -s {default-locale-url} | grep 'hreflang='
```

Each created locale should appear as an `hreflang` link.

### Step 5.4: Build sanity check

```bash
ddev build 2>&1 | tail -20
```

Build must complete without errors. Specifically watch for "frontmatter not found", "slug missing", or "duplicate URL" warnings.

---

## Examples

### Example 1: New blog post in `general` (EN + PL)

```
User: @write-content post general/picking-bare-theme "Why bare is the default"
```

**Skill actions:**

1. Read `local/content/_site.yaml` → default `en`, locales `[en, pl]`.
2. Read `local/docs/EDITOR_GUIDE.md` → categories EN `general` ↔ PL `ogolne`, approved tags include `meta, theme`, voice = "direct, no marketing fluff".
3. Create directory `local/content/blog/general/picking-bare-theme/files/`.
4. Write `local/content/blog/general/picking-bare-theme/en.md`:
   ```
   ---
   title: "Why bare is the default"
   slug: "blog/picking-bare-theme"
   date: 2026-04-30
   category: general
   description: "..."
   tags: [meta, theme]
   image: /media/picking-bare-theme/featured.webp
   image_alt: "..."
   draft: false
   template: blog/post
   ---

   {body in EN}
   ```
5. Write `local/content/blog/general/picking-bare-theme/pl.md` with PL slug `wpisy/dlaczego-bare-jest-domyslny`, category `ogolne`, PL body.
6. Verify trailing newlines on both files.
7. Clear cache, curl `/blog/picking-bare-theme/` and `/pl/wpisy/dlaczego-bare-jest-domyslny/`, confirm `200` + correct `<title>`.

### Example 2: New static page (single locale)

```
User: @write-content page projects "My Projects" --locales=en --menu-weight=30
```

**Skill actions:**

1. Create directory `local/content/pages/projects/`.
2. Write `local/content/pages/projects/en.md`:
   ```
   ---
   title: "My Projects"
   slug: "projects"
   description: "Things I've shipped."
   template: page/default
   menu:
     weight: 30
     label: "Projects"
   ---

   {body}
   ```
3. Verify trailing newline.
4. Curl `/projects/`, confirm `200` and nav contains "Projects" at weight 30.

### Example 3: New category index (all locales)

```
User: @write-content category tutorials "Tutorials"
```

**Skill actions:**

1. Read `_site.yaml` → locales `[en, pl]`.
2. Create directory `local/content/blog/tutorials/`.
3. Write `local/content/blog/tutorials/_index_en.md`:
   ```
   ---
   title: "Tutorials"
   slug: "blog/tutorials"
   description: "Step-by-step guides."
   template: blog/list
   ---
   ```
4. Write `local/content/blog/tutorials/_index_pl.md`:
   ```
   ---
   title: "Tutoriale"
   slug: "wpisy/tutoriale"
   description: "Przewodniki krok po kroku."
   template: blog/list
   ---
   ```
5. **Critical:** both files end with `\n` after the closing `---`. Without it, `findByDirectoryKey('tutorials')` returns the item but `title` and `menuLabel` are empty (silent frontmatter parse failure).
6. Verify trailing newlines.
7. Clear cache, curl `/blog/tutorials/` and `/pl/wpisy/tutoriale/`, confirm `200` and `<title>Tutorials | …</title>`.

### Example 4: Free-form brief

```
User: @write-content "draft post for the releases category about the 1.2.0 release, mention the new write-content skill, ~400 words, EN only"
```

**Skill actions:**

1. Parse brief: type=blog post, category=releases, slug=release-1-2-0 (or similar), draft=true, locales=[en], target ~400 words.
2. Read `local/docs/EDITOR_GUIDE.md` for voice + tags.
3. Create `local/content/blog/releases/release-1-2-0/en.md` with `draft: true`, today's date, body covering the 1.2.0 release and the new skill.
4. Verify trailing newline + cache clear + curl `/blog/release-1-2-0/` → expect `200` (drafts render in dev) with `[DRAFT]` badge in dev mode.

---

## Pitfalls & Gotchas

### 1. Missing trailing newline on `_index_*.md`

By far the most damaging failure mode. An `_index_*.md` file with no body and no trailing newline after the closing `---` parses with empty frontmatter — and because empty `slug` resolves to `/`, the index page **collides with the homepage** in `directoryKeyMap`. Symptom: `/blog/` renders with an empty `<title>` and `<h1>`. Always end every content file with `\n`.

### 2. Slug must NOT include the locale prefix for blog posts

Blog post slugs:
- EN: `slug: "blog/my-post"` → `/blog/my-post/`
- PL: `slug: "wpisy/moj-wpis"` → routing prepends `/pl/` → `/pl/wpisy/moj-wpis/`

Static page slugs ARE prefixed manually:
- EN: `slug: "about"` → `/about/`
- PL: `slug: "pl/o-mnie"` → `/pl/o-mnie/`

This asymmetry comes from the routing config — match the convention by inspecting existing files.

### 3. Category in frontmatter must be the localized slug

```yaml
# en.md
category: general

# pl.md
category: ogolne   # NOT "general"
```

Each locale's category index page is the source of truth for that locale's category slug.

### 4. Image must be WebP

Never commit JPG, PNG, GIF. Convert before adding:

```bash
ddev exec convert input.jpg -quality 82 -strip -define webp:method=6 \
    local/content/blog/{category}/{post-slug}/files/featured.webp
```

Reference as `/media/{post-slug}/featured.webp` (the `files/` segment is rewritten to `/media/{post-slug}/` by the asset pipeline).

### 5. `image_alt` must describe the image visually

Not "My Post Title" — that's a duplicate of `title`. Use the alt text to describe what's actually in the image (improves accessibility + image SEO).

### 6. Tags from the approved list

Free-form tags pollute the tag index. Stick to the list in `local/docs/EDITOR_GUIDE.md`. Aim for 3–6 tags per post.

### 7. Site-specific voice

After writing the body, **review against `local/docs/EDITOR_GUIDE.md`** voice rules. Most sites enforce a register (e.g. "direct, no marketing copy", "first-person past tense for retrospectives"). Anglicism rules apply to non-English locales — see the `translate-content` skill for per-locale anglicism tables.

---

## Summary

This skill creates new content with:

- Correct directory structure (`blog/{category}/{post-dir}/{locale}.md` or `pages/{page-dir}/{locale}.md` or `blog/{category-dir}/_index_{locale}.md`)
- Locale-aware slug rules (blog posts use bare slug; pages prefix with locale)
- Localized category and tags per locale
- WebP-only image references
- **Mandatory trailing newline** to prevent silent frontmatter parse failures
- Automatic locale parity by co-location
- Verification via cache-clear + curl to confirm `<title>` / `<h1>` render correctly
