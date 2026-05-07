---
name: site-sweep
description: Audit the rendered site across all configured locales to verify routing, content, and link integrity. Use when the user asks to "sweep the site", "audit links", "check all pages", "verify routing", or similar.
allowed-tools: Read, Glob, Grep, Bash, chrome-devtools_navigate_page, chrome-devtools_take_screenshot, chrome-devtools_list_console_messages, chrome-devtools_list_network_requests, chrome-devtools_list_pages, chrome-devtools_new_page
---

# Site Sweep Skill

Audit the rendered site across all configured locales to verify routing, content, and link integrity. This skill is content and theme agnostic, designing a process to discover and verify any notACMS installation.

## Workflow

### 1. Initialization & Cleanup
- **Clean Slate**: Always start by clearing browser cache or using `ignoreCache: true` for the first navigation to avoid stale results.
- **Memory Reset**: Ignore previous snapshots to ensure the current state of the site is analyzed.
- **Profile switch note**: If a profile was just switched via `local-deploy.sh`, PHP-FPM's realpath cache still holds the old resolved symlink path. Always run `ddev restart` after switching profiles *before* starting the sweep — otherwise first page loads may return 500 errors even though the static build succeeded.

### 2. Discovery Phase
Use the following steps to map the site regardless of the theme or content:
- **Identify Locales**: Read `local/content/_site.yaml` to find the list of active locales.
- **Map Pages**: Use `glob` on `local/content/pages/**/*.md` to identify all static pages. Map their slugs across all languages.
- **Map Blog**: Use `glob` on `local/content/blog/**/*.md` to identify posts, categories, and tags.
- **Build URL List**: Construct a list of all expected URLs based on the slugs and locales discovered.

### 3. Comprehensive Verification
For every discovered URL in every locale:

#### A. Accessibility & Rendering
- Navigate to the URL.
- Verify the response is `HTTP 200 OK`.
- Ensure no `Symfony Exception` or `RuntimeError` (e.g., Twig variable errors) is displayed on the page.

#### B. Multi-language Integrity
- **Hreflang Check**: Verify that the language switcher is present and takes the user to the correct equivalent page in the target locale.
- **URL Pattern**: Ensure translated URLs follow the site's routing configuration. The default (first) locale must be **unprefixed** (e.g., `/blog/` for `en`), while other locales must include their code (e.g., `/pl/blog/`).
- **Default Locale Prefix Check**: Verify that URLs with the default locale code return a redirect or 404 (e.g., `/en/blog/` must not serve the same content as `/blog/`).

#### C. Functional Audit
- **Internal Links**: Check that primary navigation links and footer links point to the correct locale's version of the page.
- **Blog Taxonomy**: Verify that category and tag links render the filtered blog list without crashing.
- **Search**: If present, test a basic search query to ensure the search index is functional.

### 4. Technical Health Check
- **Console Errors**: Check for JavaScript errors in the browser console.
- **404 Detection**: Identify any "Page not found" errors on links that should be valid.

## Reporting Format

Present findings as a structured report:

### Locale: [Locale Code]
- [ ] **Home Page**: [Status/Issue]
- [ ] **Static Pages**: [List of pages verified]
- [ ] **Blog Index**: [Status]
- [ ] **Taxonomies**: [Categories/Tags status]
- [ ] **Cross-Linking**: [Language switcher status]

### Critical Issues
- List any `500` or `404` errors with the exact URL and the cause (if a template error).

### Recommendations
- Suggest fixes for broken links or missing translations.
