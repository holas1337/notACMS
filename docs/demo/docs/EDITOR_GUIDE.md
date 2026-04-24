# Content Guide

This is the site-specific writing guide for the notACMS promotional and documentation site. It covers the categories, tags, writing voice, and image conventions for this installation.

For the underlying content system documentation (frontmatter fields, URL structure, images, series, drafts, scheduled posts, etc.), see [`docs/EDITOR_GUIDE.md`](../../docs/EDITOR_GUIDE.md) in the repository.

---

## Available categories

There is currently 1 category. New categories can be added as directories under `local/content/blog/`.

| Directory | Slug | Use for |
|---|---|---|
| `local/content/blog/releases/` | `releases` | Release notes, feature announcements, project updates, origin story |

---

## Tags

**Aim for 2–4 tags per post.** Tags help readers find related content across releases and announcements.

**Approved tag list** (add new tags when genuinely needed):

`release`, `announcement`, `open-source`, `design`, `i18n`, `php`, `symfony`, `docs`, `performance`, `security`

**Example — release post with new design system:**
- Good: `release`, `design`
- Bad: `release`, `design`, `css`, `scss`, `dark-mode`, `light-mode`, `amber` (too granular)

---

## Writing for this site

### Who reads this site

PHP developers evaluating notACMS, existing users checking release notes, and open-source contributors looking for context. They know Symfony, understand static site generators, and want specifics — not marketing fluff.

### Voice

**Technical, concise, and direct.** Show what changed and why it matters:

| Instead of | Write |
|---|---|
| "We've made exciting improvements" | "v1.1.0 ships a new design system and German locale" |
| "many new features" | "Pagefind search, cookie consent, contact form validation" |
| "easy to use" | "one YAML file for all site config" |
| "powerful and flexible" | *(omit — demonstrate it through the feature description)* |

**No marketing language.** Avoid: revolutionary, game-changing, cutting-edge, seamless, empower, leverage, unlock. If it reads like a press release, rewrite it.

**Specific over general.** Name the class, the file, the command. A developer reading a release note wants to know what actually changed.

### Post titles

Concrete, versioned when applicable:

| Good | Bad |
|---|---|
| "notACMS v1.1.0 — New design, docs, and DE locale" | "Big update!" |
| "Why I built notACMS" | "The story behind the project" |

Rules:
- Include version number for release posts
- State what's in the release, not how you feel about it
- Keep under 70 characters when possible

### Descriptions (meta / listing excerpt)

One or two sentences. Appears in search results and post cards.

**Good:** "v1.1.0 ships the new amber phosphor design system, complete documentation, German locale, and the Design Reference styleguide page."

**Bad:** "This is an exciting new release with lots of great features."

Rules:
- Lead with the version and key changes
- Name specific features, not vague categories
- 120–160 characters ideal for meta description

### Post structure

Release notes should follow a consistent pattern:
1. **What's new** — headline features with brief descriptions
2. **Technical details** — classes, tokens, file changes for developers who want depth
3. **Migration notes** — if anything breaks or needs manual steps
4. **What's next** — optional, only if there's a concrete roadmap item

Use headings liberally — readers scan release notes, they don't read them top to bottom.

### EN, DE, and PL parity

All language versions must be equal in depth. The German and Polish versions are not summaries of the English one.

Rules:
- Same technical detail in all versions
- Code snippets, class names, and file paths stay unchanged across languages
- Translate prose naturally — don't produce word-for-word translations

---

## Featured image

Every post **must** have a featured image. It is used in:
- The post header
- Post cards on listings and the homepage
- `og:image` for social sharing

**Requirements:**
- 1280x720px, WebP, quality 82 (16:9)
- Place in `files/` subdirectory: `local/content/blog/{category}/{post-dir}/files/featured.webp`
- Reference in frontmatter: `image: /media/{post-dir}/featured.webp`
- Always add `image_alt:` with a descriptive visual description

If no custom image is available, use `assets/images/og-default.jpg` as a base and crop/resize with ImageMagick:
```bash
convert assets/images/og-default.jpg -resize 1280x720^ -gravity center -extent 1280x720 -quality 85 output.webp
```
