---
title: "notACMS 1.1.2 — Canonical URLs and a structured-data builder"
slug: "blog/release-1-1-2"
description: "1.1.2 redirects default-locale-prefixed URLs to their canonical unprefixed form, replaces inline JSON-LD with a fluent Schema.org builder, and ships two security fixes."
date: 2026-05-04
category: releases
tags: [release, announcement]
template: blog/post
---

## One canonical URL per page

If your site's default locale is `en`, `/en/blog/` and `/blog/` were both reachable and rendered the same content. That's two indexable URLs for one page — and search engines pick whichever they like, which is rarely the one you want.

1.1.2 adds `DefaultLocaleRedirectListener`. Every request to `/<default-locale>/...` returns a `301` to the unprefixed URL:

```
GET /en/blog/         →  301 Location: /blog/
GET /en/about/?ref=x  →  301 Location: /about/?ref=x
GET /pl/blog/         →  200 (non-default locale, untouched)
```

The listener runs before Symfony's router, so the redirect happens before the framework even tries to match a route — no wasted work, no 404 noise in logs. Non-default locales (`pl`, `de`, `fr` on this site) are left alone.

This also closes a small SEO leak: backlinks that accidentally include the locale prefix now consolidate into the canonical URL instead of splitting authority.

## Schema.org JSON-LD as a fluent builder

Until 1.1.1, every template that needed structured data inlined a PHP array, piped it through `|json_encode|raw`, and wrapped the result in a `<script>` tag. Six templates, six near-identical blocks, and each one a fresh chance to forget `JSON_UNESCAPED_SLASHES` or to ship `<script>false</script>` if any frontmatter contained an invalid byte.

1.1.2 replaces all of that with a service and two Twig functions:

```twig
{% block structured_data %}{{ json_ld(structured_data().blogPosting(
    content.title(),
    site_base_url ~ content.url(),
    content.date(),
    content.htmlContent,
    site_author.name,
    content.image() ? site_base_url ~ content.image() : null
)) }}{% endblock %}
```

`structured_data()` returns the builder; chain a typed method (`webSite`, `webPage`, `blogPosting`, `collectionPage`, `contactPage`, `person`, `breadcrumbList`, `organization`, `imageObject`) and the result is a plain array. `json_ld(array)` encodes it and wraps it in a `<script type="application/ld+json">` tag. Empty/null fields are stripped recursively, so optional inputs (an unset author email, a missing image) simply don't appear in the output.

Encoding now uses `JSON_THROW_ON_ERROR` — bad UTF-8 in frontmatter surfaces as a real exception during render instead of silently shipping a broken `<script>` tag.

The bare core templates for `contact`, `default`, and `projects` pages now emit Schema.org markup too. Previously bare deploys had weaker SEO than every customisation example we ship; that gap is gone.

See the [Customisation guide → Structured data](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#structured-data-json-ld) for the full builder API and how to override the `structured_data` block in your own theme.

## Security fixes

Two bugs surfaced during the 1.1.2 review and were fixed before release:

- **Open-redirect via path normalisation.** Symfony's `Request::getPathInfo()` does not collapse repeated slashes, so a request to `/<default-locale>//evil.com` would otherwise produce `Location: //evil.com` — a protocol-relative redirect that browsers follow cross-origin. The new redirect listener now refuses to issue redirects to anything that isn't a single-slash-rooted local path.
- **XSS in search results.** `assets/search.js` interpolated the Pagefind `excerpt` field into innerHTML without escaping. The demo build was already correct; core had drifted. The two are now in sync. Pagefind's `<mark>` highlight tags are no longer rendered in core search results — a deliberate trade for the safer baseline.

## Also in 1.1.2

- **Internal Twig refactor.** Sidebar, breadcrumb, post-badge, og-image, and blog-filter logic moved out of templates into dedicated Twig extensions. Templates stay focused on layout; tests can target each helper directly. New customisation surface: see [Layout helpers](https://github.com/holas1337/notACMS/blob/main/docs/CUSTOMIZATION.md#layout-helpers) in the customisation guide.
- **`O(1)` directory-key lookups** on `ContentTree` — a small performance fix on sites with hundreds of pages.
- **`docs/customization/old-template/` is now deprecated** and will be removed in 1.2.0. It was a one-shot 1.0→1.1 transition aid; new customisation work should branch from `custom-footer/` or `self-hosted-fonts/` instead.
- **Dependency bumps:** Symfony 7.4.8 → 7.4.9 across the bundle, PHPStan 2.1.51 → 2.1.54, PHPUnit 13.1.7 → 13.1.8.

## Full changelog

Every change with its category: [CHANGELOG.md](https://github.com/holas1337/notACMS/blob/main/CHANGELOG.md#112---2026-05-04).
