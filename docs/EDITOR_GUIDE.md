# Editor Guide

How to add and edit content in notACMS — blog posts, static pages, and translations.

> **Site-specific writing guide:** categories, approved tags, voice, and image generation styles for *this* site are documented in `local/docs/EDITOR_GUIDE.md` (seeded from `docs/demo/docs/EDITOR_GUIDE.md` on first deploy).

---

## How it works

Content lives in Markdown files under `local/content/`. Each file has a YAML frontmatter block at the top followed by Markdown body. Blog posts and pages are co-located by directory with separate locale files (`pl.md` + `en.md`). URLs and categories are defined in frontmatter. The system reads these files and renders them to static HTML on `ddev build`.

No database. No CMS UI. Edit files, build, done.

---

## Directory structure

```
local/content/
├── _site.yaml              ← global site config (don't edit unless you know why)
├── blog/                   ← all blog posts and categories
│   ├── _index_en.md        ← /blog/ listing page (EN)
│   ├── _index_pl.md        ← /pl/wpisy/ listing page (PL)
│   ├── {category}/
│   │   ├── _index_en.md    ← EN category index
│   │   ├── _index_pl.md    ← PL category index
│   │   └── my-post/        ← a blog post directory
│   │       ├── en.md       ← EN version
│   │       ├── pl.md       ← PL version
│   │       └── files/      ← images for this post
│   │           └── photo.webp
└── pages/                  ← static pages
    ├── home/
    │   ├── en.md           ← EN homepage
    │   └── pl.md           ← PL homepage
    ├── about/
    │   ├── en.md           ← /about/ page
    │   └── pl.md           ← /pl/o-mnie/ page
    └── contact/
        ├── en.md           ← /contact/ page
        └── pl.md           ← /pl/kontakt/ page
```

**File types:**
- `en.md` / `pl.md` — locale-specific content (post or page). Both in the same directory = automatic translation link.
- `_index_en.md` / `_index_pl.md` — section/category index pages
- `files/` — images and assets co-located with content, served at `/media/{post-dir}/`

---

## Adding a blog post

### 1. Create the directory and files

Create a new directory in the appropriate category, with locale files inside:

```
local/content/blog/{category}/my-new-post/
    en.md       ← EN version
    pl.md       ← PL version (optional — not all posts need both)
    files/      ← images for this post (optional)
        photo.webp
```

### 2. Write the frontmatter

**EN file** (`en.md`):
```yaml
---
pinned: false
title: "My post title"
slug: "blog/my-new-post"
date: 2026-03-15
category: category-slug-en
description: "One or two sentences that appear in listings and as the meta description."
tags: [tag1, tag2, tag3]
image: /media/my-new-post/photo.webp
image_alt: "Descriptive alt text for the image."
draft: false
---
```

**PL file** (`pl.md`):
```yaml
---
pinned: false
title: "Tytuł mojego wpisu"
slug: "wpisy/moj-nowy-wpis"
date: 2026-03-15
category: category-slug-pl
description: "Jedno lub dwa zdania."
tags: [tag-pl1, tag-pl2, tag-pl3]
image: /media/my-new-post/photo.webp
image_alt: "Opisowy tekst alternatywny."
draft: false
---
```

Then write the body in Markdown below the closing `---`.

### 3. Resulting URLs

| File | URL |
|---|---|
| `local/content/blog/{category}/my-new-post/en.md` | `/blog/my-new-post/` |
| `local/content/blog/{category}/my-new-post/pl.md` | `/pl/wpisy/moj-nowy-wpis/` |

The `slug` field in frontmatter defines the full URL path. EN blog posts use the `blog/` prefix (e.g. `blog/my-new-post`); PL blog posts use `wpisy/` (e.g. `wpisy/moj-nowy-wpis`).

---

## Adding a static page

Static pages live in `local/content/pages/` with locale files in the same directory.

### 1. Create the directory and files

```
local/content/pages/my-page/
    en.md       ← EN version
    pl.md       ← PL version
```

### 2. Write the frontmatter

**EN file** (`en.md`):
```yaml
---
title: "My Page"
slug: "my-page"
description: "What this page is about."
template: page/default
menu:
  weight: 50
  label: "My Page"
---
```

**PL file** (`pl.md`):
```yaml
---
title: "Moja strona"
slug: "moja-strona"
description: "O czym jest ta strona."
template: page/default
menu:
  weight: 50
  label: "Moja strona"
---
```

### 3. Resulting URLs

| File | URL |
|---|---|
| `local/content/pages/my-page/en.md` | `/my-page/` |
| `local/content/pages/my-page/pl.md` | `/pl/moja-strona/` |

### 4. Add to navigation

Set `menu.weight` and `menu.label` in frontmatter. Lower weight = appears earlier. `menu.label` is the text shown in the navigation; if omitted, the page `title` is used as fallback. Existing weights for reference:

| Page | Weight |
|---|---|
| Home | 10 |
| Blog / Wpisy | 20 |
| About / O mnie | 40 |
| Contact / Kontakt | 60 |

If you don't want the page in the navigation menu, omit the `menu` fields entirely.

---

## Frontmatter reference

> **Machine-readable:** JSON Schema files for all content types live in `config/schema/` — `post.frontmatter.schema.json`, `page.frontmatter.schema.json`, `category.frontmatter.schema.json`.

| Field | Type | Default | Required | Description |
|---|---|---|---|---|
| `title` | string | — | **yes** | Page/post title. Shown in `<h1>`, `<title>`, og:title. |
| `description` | string | — | recommended | One-two sentences. Used as meta description and in post cards. |
| `slug` | string | — | **yes** | Full URL path. Blog posts — EN: `blog/my-post`, PL: `wpisy/moj-wpis`. Pages — EN: `my-page`, PL: `moja-strona` (routing adds the `/pl/` prefix for PL pages automatically). |
| `date` | date (`YYYY-MM-DD`) | — | for posts | Publication date. Past or missing date = published. Future date = scheduled: excluded from listings/RSS/sitemap, but its URL renders a Coming Soon page (green terminal, `noindex`) — shows `[PLANNED]` badge in dev. |
| `updated` | date (`YYYY-MM-DD`) | — | no | Last meaningful update date. Shown on the single post page and triggers a **RECENTLY UPDATED** badge on post cards for 90 days. Set only for significant content changes — see note below. |
| `category` | string | — | for posts | Localized category slug. See `local/docs/EDITOR_GUIDE.md` for this site's available categories. |
| `tags` | list | `[]` | no | Tag list. Each tag gets its own listing page. See `local/docs/EDITOR_GUIDE.md` for this site's approved tag list. |
| `image` | string | — | no | Path to featured image, e.g. `/media/my-post/photo.webp`. Always WebP. |
| `image_alt` | string | title | **required with `image`** | Alt text for the featured image. Must visually describe the image — not repeat the post title. Improves accessibility (WCAG) and image SEO. |
| `draft` | bool | `false` | no | If `true`, the post is excluded from all listings and the static build. Shows only `[DRAFT]` badge in dev (suppresses all other badges). |
| `series` | string | — | no | Series key (kebab-case). Posts sharing this key form a series with prev/next nav. Must match in both locale files. |
| `series_order` | int | — | no | Position within the series (1-based). Determines display order in the series nav. |
| `featured` | bool | `false` | no | **Projects only.** If `true`, the post appears in the curated grid on the `/projects/` portfolio page. Has no effect on other post types. The grid should always show exactly **12 featured projects** — when adding a new project with `featured: true`, remove the flag from the least relevant existing one to keep the count at 12. |
| `pinned` | date or `false` | `false` | no | **Posts only.** Set to a `YYYY-MM-DD` date to pin until that date (inclusive). Post sorts to the top of all listings, gets the featured card + `[PINNED]` badge on homepage. Auto-unpins after the date passes. Stacks with `[NEW]` or `[RECENTLY UPDATED]`. |
| `dynamic` | bool | `false` | no | If `true`, the page is not pre-rendered -- always served live by Symfony. |
| `toc` | bool | `false` | no | If `true`, force-show the auto-generated table of contents. If `false`, suppress it. Default behavior (field omitted): show ToC on posts with 3+ headings. |
| `template` | string | `page/default` | no | Twig template to use (without `.html.twig`). |
| `menu` | object | — | no | Navigation entry. Set `weight` (lower = earlier) and `label` (defaults to `title`) to include this page in the nav. Example: `menu: { weight: 50, label: "My Page" }` |

#### When to set `updated:`

Set `updated:` only when the change is **meaningful to readers** — corrected facts, rewritten sections, added critical information, or updated commands/versions that affect how readers use the content.

**Do set `updated:`:**
- A tutorial command no longer works and you rewrote the relevant steps
- You corrected a technical error or outdated recommendation
- You added a substantial new section

**Do NOT set `updated:`:**
- Fixing a typo or grammar mistake
- Light wording tweaks or rephrasing
- Adding a "see also" link or a reference to a followup post
- Changing the featured image

The reason: `updated:` triggers a visible **RECENTLY UPDATED** badge on post cards for 90 days. Using it for minor edits makes the signal meaningless.

---

## Categories and tags

Categories and the approved tag list are site-specific. See **`local/docs/EDITOR_GUIDE.md`** for:
- Available categories (EN/PL slugs and what to use each for)
- The approved tag list and tag rules

**How categories work:** Each blog post must specify a `category` in frontmatter. The value is a localized slug (EN and PL differ). Category index pages live at `local/content/blog/{category-dir}/_index_en.md` and `_index_pl.md`.

**How tags work:** Tags are free-form but should come from the approved list. Each tag creates a listing page at `/tag/{tag}/`. Aim for 3–6 tags per post.

---

## Linking translations (PL ↔ EN)

Translations are linked **automatically** by co-location. Place both `en.md` and `pl.md` in the same directory:

```
local/content/blog/{category}/my-post/
    en.md       ← EN version
    pl.md       ← PL version
```

This enables:
- Correct `hreflang` SEO tags on both pages
- The language switcher linking directly to the translated page instead of the other language's homepage

If a post has no translation (only one locale file exists), the language switcher will fall back to the other locale's homepage. No `translation_key` field is needed.

For adding a new language to the site, see [LOCALES.md](LOCALES.md).

---

## Images

Place images in a `files/` subdirectory alongside the content:

```
local/content/blog/{category}/my-post/
    en.md
    pl.md
    files/
        photo.webp
        detail.webp
```

**All images must be WebP.** Never commit JPG, PNG, or other formats. Convert before adding:

```bash
ddev exec convert input.jpg -quality 82 -strip -define webp:method=6 local/content/blog/<category>/<post-dir>/files/output.webp
```

Images are served at `/media/{post-dir}/filename`, e.g. `/media/my-post/photo.webp`.

Reference in frontmatter:
```yaml
image: /media/my-post/photo.webp
```

Reference in Markdown body:
```markdown
![Alt text](/media/my-post/diagram.webp)
```

The featured image is used as:
- Featured image on the post page
- Thumbnail in post cards on listings and homepage
- `og:image` for social sharing

**Recommended size:** 1280×720px, quality 82 (16:9, matches the featured image spec).

**Responsive variants** are generated automatically during `ddev build` — for every `.webp` image wider than 640px, the build creates `-640w.webp` and (if wider than 960px) `-960w.webp` variants. Templates use these variants via `srcset` to serve appropriately-sized images on mobile. You do not need to create these manually.

---

## Structured Data (JSON-LD)

Every page automatically includes JSON-LD structured data for search engines.
The `{% block structured_data %}` block in `base.html.twig` emits the default
`WebSite` schema. Specific page templates override it with the appropriate type.

**Built-in schema types:**

| Page | Schema.org Type | Template |
|---|---|---|
| Homepage | WebSite | `page/home.html.twig` |
| About page | Person | `page/about.html.twig` |
| Blog post | BlogPosting | `blog/post.html.twig` |
| Blog listing | CollectionPage | `blog/list.html.twig` |
| Contact page | ContactPage | `page/contact.html.twig` |

Two Twig functions drive the output:

- `json_ld(array)` — encodes a PHP array to JSON and wraps it in a
  `<script type="application/ld+json">` tag.
- `structured_data()` — returns the builder instance, letting you chain typed
  builder methods like `structured_data().person(...)`.

The builder automatically strips empty values (`null`, `''`, `[]`). If
`site_author.email` is not set in `_site.yaml`, the `email` key is absent from
the output rather than appearing as empty.

### Overriding structured data

To customize the JSON-LD for your site, override the `structured_data` block in
`local/templates/base.html.twig`. See [Customization](/customization/) for the
full example with `searchAction`, custom `author`, and `sameAs` social URLs.

For per-page overrides, extend the specific page template and override only its
`structured_data` block:

```twig
{# local/templates/page/about.html.twig #}
{% extends '@base/page/about.html.twig' %}

{% block structured_data %}
{{ json_ld(structured_data().person(
    site_author.name,
    site_base_url,
    'about.role'|trans,
    'about.headline'|trans,
    site_author.email ?? null,
    site_author.expertise|map(e => e.tags)|reduce((flat, tags) => flat|merge(tags), []),
    site_social|map(l => l.url)
)) }}
{% endblock %}
```

Content authors typically do not need to write or edit JSON-LD directly — the
templates handle it based on frontmatter fields and `_site.yaml` values.

---

## Table of Contents

Blog posts with **3 or more headings** (h2/h3) automatically get a collapsible Table of Contents injected at the top of the post body. No frontmatter needed — it is generated client-side by `table-of-contents.js`.

- h2 headings become top-level entries; h3 headings are nested under their preceding h2
- The ToC is open by default; clicking the summary collapses it
- Posts with fewer than 3 headings do not show a ToC

To suppress the ToC on a specific post, set `toc: false` in frontmatter. To force it on a post that doesn't meet the 3-heading threshold, set `toc: true`.

---

## Related posts

Each blog post automatically shows up to 3 related posts below the article. They are selected by algorithm: same category (+2 points) and shared tags (+1 point each). Posts with the highest score appear first; remaining slots are filled by recent posts.

**Manual override** — add `related:` to frontmatter with a list of post slugs (the last path segment of the URL, locale-independent):

```yaml
related:
  - my-first-post-slug
  - another-post-slug
  - third-post-slug
```

Manual entries fill slots first; the algorithm fills any remaining slots. So listing 2 slugs means 2 manual + 1 algorithm. Drafts and scheduled posts in the list are silently skipped — their slot is filled by the algorithm instead, and they appear automatically once published.

Both EN and PL files in the same directory should list their respective locale slugs — the system resolves them within the current locale automatically.

**External links open in a new tab automatically** — any link to an external domain gets `target="_blank" rel="noopener noreferrer"` added by the markdown parser. Internal links (starting with `/` or relative) open in the same tab. No special syntax needed.

**Linking to unpublished posts** — if a post body references another post that isn't published yet, do not link to it inline (it would show a Coming Soon page). Instead, add the slug to `related:` and remove the inline link. The algorithm fills the gap until the post publishes, then it appears in the related section automatically.

---

## Series

Multi-part posts can be linked into a series. A collapsible **"Part X of Y"** navigation bar appears before the post body on every post in the series, collapsed by default.

### Frontmatter fields

Add two fields to each post in the series:

```yaml
series: "series-key"
series_order: 1
```

| Field | Type | Description |
|---|---|---|
| `series` | string | Shared key identifying the series. Use a short kebab-case string (e.g. `my-series`). Must be identical across all posts in the series — and in both `en.md` and `pl.md`. |
| `series_order` | int | Position within the series. Determines the display order. Start at `1`. |

### Rules

- The series key is **locale-independent** — use the same value in both `en.md` and `pl.md`.
- **Draft and scheduled posts are excluded** from the series nav automatically, same as from all other listings.
- A series nav only renders when **2 or more published posts** share the same key. A lone post with a `series:` field shows no nav.
- There is no limit on series length, but keep series keys consistent once published — changing a key breaks the grouping.

---

## Drafts

Set `draft: true` to hide a post from all listings, the static build, RSS, and sitemap. In development the post appears in listings with a `[DRAFT]` badge (gray, dashed border). This badge suppresses all others — a draft post will never show `[PINNED]`, `[NEW]`, or `[RECENTLY UPDATED]`.

```yaml
draft: true
```

Remove `draft: true` (or set it to `false`) to publish.

---

## Scheduled posts

Set `date:` to a future date to schedule a post. It is automatically excluded from all listings, the static build, RSS, and sitemap until that date. The next `ddev build` run after the date passes will publish it.

```yaml
date: 2026-06-01
```

The post's URL is still accessible — it renders a **Coming Soon** page (green terminal style, `noindex, nofollow`) instead of a 404. If the publish date is within 14 days, the date is shown on the page. This page is pre-rendered as a static file by `ddev build`.

In development the post appears in listings with a `[PLANNED]` badge (yellow, dashed border). Use the **Scheduled** toggle in the Symfony toolbar to show/hide scheduled posts. This badge suppresses `[PINNED]`, `[NEW]`, and `[RECENTLY UPDATED]`.

---

## Pinned posts

Set `pinned:` to a `YYYY-MM-DD` date to pin a post until that date. The post sorts to the top of all listings and on the homepage gets the large featured card layout and a `[PINNED]` badge. After the date passes the post automatically reverts to its normal position — no manual action needed. Pinned stacks with `[NEW]` or `[RECENTLY UPDATED]` — both badges show simultaneously.

```yaml
pinned: 2026-06-01
```

Only pin one post at a time for a clean homepage. If multiple posts are pinned, they all appear at the top sorted by date descending.

---

## Build & preview

After adding or editing content:

```bash
# Development server (live, no build needed)
ddev start
# Visit https://notacms.ddev.site

# Full static build (what goes to production)
ddev build
```

In development (`ddev start`), all pages render dynamically — changes are visible immediately after a browser refresh. No build needed for content editing.

Run `ddev build` before deploying to check that the static build completes without errors.

---

## Checklist for a new post

- [ ] Directory created in the correct category under `local/content/blog/`
- [ ] Locale file(s) created (`en.md` and/or `pl.md`)
- [ ] `title` and `description` filled in
- [ ] `slug` set — EN: `blog/my-post` → `/blog/my-post/`; PL: `wpisy/moj-wpis` → `/pl/wpisy/moj-wpis/` (routing adds the `pl/` prefix)
- [ ] `date` set
- [ ] `category` set with localized slug (see `local/docs/EDITOR_GUIDE.md`)
- [ ] `tags` added from approved list (see `local/docs/EDITOR_GUIDE.md`)
- [ ] `image` set — **required**, using `/media/` path, minimum 1280×720px
- [ ] `image_alt` set — **required with `image`**, describes the image visually (not the post title)
- [ ] Images placed in `files/` subdirectory
- [ ] `draft: false` (or removed)
- [ ] `ddev build` passes without errors
- [ ] Writing style reviewed against `local/docs/EDITOR_GUIDE.md`
