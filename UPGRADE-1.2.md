# UPGRADE FROM `1.1` TO `1.2`

v1.2.0 is the audit release: ~170 findings from a full code, security, and theme audit were fixed. Most changes are internal; this guide covers everything that can require action. Each section starts with **Breaking if you have …** — skip the ones that don't apply to you.

Also new in this release: [docs/THEME_BUILDING.md](docs/THEME_BUILDING.md) — the theme API reference. If you maintain a custom theme, read it once; everything it lists is now a supported contract.

---

## Directory keys are full content paths

**Breaking if you have …** code or templates comparing `directoryKey()` against a literal, or indexing `translation_map` with hand-written basenames.

`ContentItem::directoryKey()` now returns the full relative content path, fixing silent collisions between same-named directories (translation map, hreflang, lookups).

```twig
{# Before #}
{{ content.directoryKey() }}   {# 'about' #}
{# After #}
{{ content.directoryKey() }}   {# 'pages/about' #}
```

**No action needed** for `content_item('about', locale)` / `content_url('about', locale)` — bare basenames still resolve when unambiguous (ambiguous ones return null with a build warning telling you to use the full path). Templates that index `translation_map` via `content.directoryKey()` are also fine. Only literal comparisons need updating:

```twig
{# Before #}
{% if content.directoryKey() is same as('about') %}
{# After #}
{% if content.directoryKey() is same as('pages/about') %}
```

---

## `getTree()` moved to `ContentTreeProviderInterface`

**Breaking if you have …** `local/src` PHP code injecting `ContentServiceInterface` and calling `getTree()`.

```php
// Before
public function __construct(private ContentServiceInterface $contentService) {}
$tree = $this->contentService->getTree($locale);

// After
use NotACms\Service\Content\ContentTreeProviderInterface;
public function __construct(private ContentTreeProviderInterface $contentTreeProvider) {}
$tree = $this->contentTreeProvider->getTree($locale);
```

The facade gained direct query methods you may prefer instead: `findByDirectoryKey()`, `getPostsByTag()`, `getPostsByCategory()`, `getPostsByYearMonth()`, `getPostsByYear()`.

---

## `blogPosting()` takes a named map

**Breaking if you have …** a custom post template calling `structured_data().blogPosting(...)` with positional arguments.

```twig
{# Before — 13 positional arguments #}
{{ json_ld(structured_data().blogPosting(
    content.title(),
    site_base_url ~ app.request.pathInfo,
    locale,
    content.date()|date('c'),
    ...
)) }}

{# After — named hash (keys match the BlogPostingData value object) #}
{{ json_ld(structured_data().blogPosting({
    headline: content.title(),
    url: site_base_url ~ app.request.pathInfo,
    inLanguage: locale,
    datePublished: content.date() ? content.date()|date('c') : '',
    dateModified: content.updatedDate() ? content.updatedDate()|date('c') : null,
    description: content.description(),
    wordCount: content.wordCount(),
    articleSection: content.category() ?: null,
    keywords: content.tags(),
    author: structured_data().person(site_author.name|default(site_name), site_base_url),
    publisher: structured_data().organization(site_name, site_base_url)
})) }}
```

All keys except `headline`, `url`, and `inLanguage` are optional.

---

## `lang_switch_url` context key removed

**Breaking if you have …** a custom controller setting `lang_switch_url` in render context, or a template reading it directly.

The language switcher now consumes a typed `LangSwitchContext` (context key `lang_switch`), and tag pages resolve URLs for **all** other locales via `TagLangSwitchResolver` (previously only the first other locale — broken on 3+-locale sites). Theme templates calling `lang_switch_urls(other_locales)` are unaffected — the function signature is unchanged.

```php
// Custom controller, before
$context['lang_switch_url'] = $url;
// After
use NotACms\Content\ValueObject\LangSwitchContext;
$context['lang_switch'] = new LangSwitchContext(urlOverrides: ['pl' => $url]);
```

---

## Removed core translation keys

**Breaking if you have …** a custom theme using these keys without defining them in its own catalogs (core catalogs no longer provide the fallback).

| Removed key | If you use it |
|---|---|
| `blog.category` | define it in `local/translations/messages.*.yaml` |
| `blog.archive` | define it in `local/translations/messages.*.yaml` |
| `sidebar.archive` | define it in `local/translations/messages.*.yaml` |
| `about.cta_text` | define it in `local/translations/messages.*.yaml` |

New keys your catalogs should have (core templates use them): `blog.title`, `contact.form.unavailable`, `contact.about_nudge_link`, `contact.email.title`, `contact.email.heading`, `contact.email.footer`. The `contact.about_nudge` key is now plain text — the link markup moved into the template (no more HTML in catalogs).

---

## `old-template` compatibility package removed

**Breaking if you have …** a 1.0.x site planning to upgrade using `docs/customization/old-template/`.

The package shipped through v1.1.x only. Upgrade via a v1.1.x release first, or copy the directory from a v1.1.x tag:

```bash
git checkout v1.1.4 -- docs/customization/old-template
cp -r docs/customization/old-template/. local/
```

---

## Behavioral changes (intent-aligned bug fixes — verify, no migration needed)

- **Draft/scheduled pages are now actually excluded** from static builds, URL resolution, menus (`content_item()`), and `/llms.txt` — previously `draft: true` on a *page* was silently ignored. If a page disappears from your build, check its frontmatter.
- **`pinned:` is inclusive** — a post stays pinned *on* its `pinned:` date (as the editor guide always documented); `pinned: true` pins indefinitely.
- **Dated static pages no longer get blog-post breadcrumbs** — post detection uses content location (`blog/` prefix) instead of date presence.
- **Uppercase/spaced categories and tags are slugified** for URLs (with a build warning) instead of crashing URL generation.
- **Content files without a `slug:` key are skipped** with a warning instead of silently hijacking the homepage (the home page declares `slug: ""` explicitly).
- **Empty archives return 404** (consistent with tag/category pages); empty category indexes are skipped during builds.
- **`app:build -o <dir>`** refuses to clear a directory other than the configured static dir unless `--force` is passed. Plain `app:build` (as used by `./notACMS deploy` and `ddev build`) is unaffected.

---

## Non-breaking changes

Internal refactors with no user action required: the static build pipeline was decomposed into `StaticUrlCollector`/`StaticPageRenderer`/`MediaPublisher` services (same CLI); `SiteConfigServiceInterface` was split into `LocaleConfigInterface`/`SiteSettingsInterface`/`ImageConfigInterface` via composition (existing type-hints keep working); ImageMagick runs through `Symfony\Process` instead of `exec()`; `#[LocalizedRoute]` attributes are now discovered in `local/src/Controller/` too; the contact form validates `contact_form` config and degrades gracefully; Turnstile validates the response hostname and warns when the committed test keys are active in production; build diagnostics (duplicate URLs, ambiguous directory keys, invalid taxonomy values, media collisions) are reported as warnings instead of failing silently.
