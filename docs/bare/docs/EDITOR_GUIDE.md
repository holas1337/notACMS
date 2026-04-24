# Content Guide

This is the site-specific writing guide for the notACMS bare theme installation. It covers categories, tags, writing conventions, and basic image handling.

For the underlying content system documentation (frontmatter fields, URL structure, images, series, drafts, scheduled posts, etc.), see [`docs/EDITOR_GUIDE.md`](../../docs/EDITOR_GUIDE.md) in the repository.

---

## Categories

The bare theme ships with two categories for demonstration:

| Directory | Slug | Use for |
|---|---|---|
| `local/content/blog/demo/` | `demo` | Getting started, feature walkthroughs, guides |
| `local/content/blog/general/` | `general` | Any blog post that isn't a demo or tutorial |

Add new categories by creating directories under `local/content/blog/`. Each category should have:
- `_index_en.md` and `_index_pl.md` with category listing frontmatter
- A key in `translations/messages.en.yaml` and `.pl.yaml` under `blog.categories.{slug}`

---

## Tags

Tags are freeform but keep them useful for filtering. Aim for 2–4 per post.

Examples from the demo content:
- `general` — used on placeholder posts
- `demo` — used on getting-started and feature demos

**Good practice:** Use broad, searchable tags. `php` or `symfony` are better than `php8.3-fpm`.

---

## Writing for this site

### Voice

**Clear, practical, and direct.** The bare theme audience is developers evaluating or using notACMS. They want facts, not hype.

| Instead of | Write |
|---|---|
| "We're thrilled to announce" | "Version 1.1.0 adds..." |
| "Cutting-edge static site generation" | "Build static HTML with one command" |
| "Seamlessly integrates with Symfony" | "Built on Symfony 7 — standard components, standard patterns" |

### Post titles

Keep them descriptive and searchable:

| Good | Bad |
|---|---|
| "Deploying notACMS to shared hosting" | "How I solved deployment" |
| "Multi-language routing in notACMS" | "A cool feature you should know about" |

### Descriptions (meta / listing excerpt)

One sentence that appears in search results and post cards. Lead with the takeaway:

**Good:** "A single command compiles Markdown content into static HTML with responsive images and full-text search."

**Bad:** "In this post I will discuss how notACMS works and what you can do with it."

---

## Reading time

Blog post templates automatically render an estimated reading time (e.g. `4 min read`) calculated from the post word count. No frontmatter key is required and no author action is needed — the value is computed and displayed by the template. The string comes from the `blog.reading_time` translation key.

---

## Images

The bare theme does not enforce featured images, but they improve social sharing and listing pages.

**If you add a featured image:**
- Preferred: 1280×720px, WebP format, quality 82
- Place in the post's `files/` subdirectory
- Reference in frontmatter: `image: /media/{post-slug}/featured.webp`
- Always add `image_alt:` for accessibility

**Fallback:** If no image is set, `og-default.jpg` is used for social previews.

---

## EN / PL parity

If you maintain both languages:
- Same technical detail in both versions
- Code snippets and file paths stay in English
- Translate prose naturally — not word-for-word

---

## Adding a new post

1. Create directory: `local/content/blog/{category}/{post-slug}/`
2. Add `en.md` (required) and optionally `pl.md`
3. Write frontmatter + Markdown body
4. Run `ddev exec php bin/console app:build` to test
5. Add images to `files/` subdirectory if needed

Example frontmatter:

```yaml
---
title: "My post title"
slug: "blog/my-post"
date: 2026-04-22
category: general
description: "A concise description for listings and search results."
tags: [symfony, static-site]
draft: false
---
```
