---
name: doc-alignment
description: Audit documentation and demo content for consistency across three dimensions: docs/demo/content vs ./docs, ./docs vs actual code, and demo content YAML/structure vs code. Use when the user asks to "check docs", "audit demo content", "verify documentation", "are docs in sync", "doc alignment", or similar.
allowed-tools: Read, Glob, Grep, Bash
---

Perform a comprehensive alignment audit across three dimensions. Read `AGENTS.md` first for project-specific conventions.

**Key principle:** Files in `docs/` describe how notACMS works — mechanisms, patterns, and configuration. They must be content-agnostic, using generic illustrative names (e.g. `my-page/`, `about/`), not enumerating the current demo content. Concrete listings and current page names belong in `docs/demo/` pages only.

---

## What to check

### A. `docs/demo/content` vs `./docs/`

Demo content pages should reflect the documentation for end users. The mapping per AGENTS.md:

| ./docs/ File | docs/demo/ Page(s) | What to verify |
|---|---|---|
| docs/ARCHITECTURE.md | pages/architecture/*.md | Content pipeline, routing, services, deployment |
| docs/CUSTOMIZATION.md | pages/customization/*.md | Override patterns, template system, SCSS variables |
| docs/EDITOR_GUIDE.md | pages/manual/*.md | Frontmatter fields, content structure, images |
| docs/LOCALES.md | pages/locales/*.md | Locale config, URL patterns, translations |
| docs/STYLEGUIDE.md | pages/styleguide/*.md | Design tokens, components, living styleguide |
| docs/TESTING.md / TESTS.md | pages/manual/*.md | Test commands, coverage info |

Check for:
- **Technical facts** mentioned in `./docs/` but missing or wrong in demo content
- **API signatures** in demo pages that don't match actual service interfaces
- **Namespace references** in demo examples (`Local\` vs `NotACms\Local\`)
- **URL derivation** — demo should explain that URLs come from `slug` frontmatter, not directory paths
- **Missing sections** — demo pages that don't cover documented features
- **Outdated commands, paths, class names, file names** in demo content
- **Locale examples** — demo should use the same languages as `_site.yaml` (currently en/de/pl, not fr)
- **Frontmatter fields** — demo manual must list all fields that `ContentItem.php` supports

### B. `./docs/` vs actual code

Cross-reference every technical claim in docs against the source code:

- **services.yaml / config** — every service, parameter, and path referenced in docs must exist
- **Service methods** — every method signature shown in docs must match the actual interface
- **ContentItem API** — every method, property, and type mentioned must match `src/Content/ContentItem.php`
- **Config keys** — every `_site.yaml` key documented must be read by `SiteConfigService.php`
- **Route definitions** — every route in docs must match a `#[LocalizedRoute]` attribute on a controller
- **Template paths** — every template reference must exist in `templates/`
- **SCSS tokens/variables** — every design token in docs must exist in `assets/styles/_tokens.scss` or `_variables.scss`
- **Test files** — every test file listed in `TESTS.md` / `TESTING.md` must exist in `tests/`
- **Seed directory references** — every `docs/demo/` path referenced must exist (replaced old `docs/examples/`)
- **Content directory tree** — category and page names in `docs/demo/content/` must be internally consistent. **Do NOT add or enumerate current demo page names to `docs/` reference files** — `docs/` files describe mechanisms and patterns, not the current content inventory. Use generic names like `my-page/` and `about/` as examples. Concrete page listings belong only in `docs/demo/content/` and demo content pages.

### C. Demo content YAML/structure vs code

- **`_site.yaml`** — every config key must be read by `SiteConfigService.php`; every key in the service must have a default or be in the demo yaml
- **`_routes.yaml`** — route names must match `#[LocalizedRoute]` `name` attributes in controllers
- **`_tags.yaml`** — tag slugs must be valid; format must match what `TagTranslationService` expects
- **Frontmatter fields** — every field used in demo `.md` files must be supported by `ContentItem.php` (check `frontMatter` array parsing in `ContentTreeBuilder`)
- **Template references** — every `template:` frontmatter value must resolve to a real template (e.g. `blog/post` → `templates/blog/post.html.twig`)
- **Category consistency** — categories used in demo posts must have `_index_{locale}.md` files

---

## How to verify

### Source code reference files

Read these files first to establish the ground truth:

1. `src/Content/ContentItem.php` — all supported frontmatter fields and methods
2. `src/Service/SiteConfigService.php` / `SiteConfigServiceInterface.php` — all `_site.yaml` keys
3. `src/Service/Content/ContentServiceInterface.php` — public API signatures
4. `src/Service/Content/ContentTreeBuilderInterface.php` — `BLOG_CONTENT_PREFIX` constant
5. `src/Routing/LocalizedRouteLoader.php` — route override loading from `_routes.yaml`
6. `src/Service/Content/TagTranslationService.php` — `_tags.yaml` format
7. `config/services.yaml` — service registrations, parameter definitions
8. `config/packages/twig.yaml` — template paths
9. `config/packages/translation.yaml` — translation paths
10. `config/packages/asset_mapper.yaml` — asset paths
11. `templates/` — all available templates (glob `templates/**/*.html.twig`)
12. `assets/styles/_tokens.scss`, `_variables.scss` — design tokens and SCSS variables
13. All controller files in `src/Controller/` — `#[LocalizedRoute]` attributes
14. `tests/` directory — actual test files vs what's listed in TESTS.md/TESTING.md

### Doc files to audit

Read ALL of these:
- `docs/ARCHITECTURE.md`
- `docs/CUSTOMIZATION.md`
- `docs/EDITOR_GUIDE.md`
- `docs/LOCALES.md`
- `docs/STYLEGUIDE.md`
- `docs/TESTING.md`
- `docs/TESTS.md`

### Demo content pages to audit

Read ALL EN versions at minimum:
- `docs/demo/content/pages/architecture/en.md`
- `docs/demo/content/pages/customization/en.md`
- `docs/demo/content/pages/manual/en.md`
- `docs/demo/content/pages/locales/en.md`
- `docs/demo/content/pages/styleguide/en.md`

Also check:
- `docs/demo/content/_site.yaml`
- `docs/demo/content/_routes.yaml`
- `docs/demo/content/_tags.yaml`
- Several blog post frontmatters (releases, the-idea)
- Several page frontmatters (home, about, contact)

---

## Output format

Present findings as a **numbered issue list grouped by the three dimensions** (A, B, C). For each issue include:
- **File:line** reference
- One-sentence description of the problem
- Concrete fix (before/after snippet or instruction)
- Severity: **Critical** (wrong API, non-existent path), **High** (missing feature, wrong behavior), **Medium** (incomplete example, wrong locale), **Low** (naming inconsistency)

After presenting findings, **stop and wait** for the user to decide which issues to fix and in what order. Do not start implementing unless explicitly asked.

---

## Self-contained test check (bonus)

When running this audit, also verify that the test suite is self-contained — i.e., PHPUnit can run without `local/content/` being seeded:

1. Check that `config/services_test.yaml` overrides `notacms_content` to `tests/Fixtures/content` and `notacms.local_dir` to `tests/Fixtures` (decouples Twig paths from instance templates)
2. Verify `tests/Fixtures/content/` has valid `_site.yaml`, `_routes.yaml`, `_tags.yaml`
3. Verify `tests/Fixtures/templates/` has `.gitkeep` (empty — Twig falls through to core `templates/`)
4. Verify that `importmap.php` and `symfonycasts_sass.php` use `file_exists()` guards for optional `local/` files
5. Confirm the CI seed step only needs to create `local/content/` for non-test commands (`sass:build`, `lint:twig`)

If any of these checks fail, report them as findings under a section **D. Test isolation issues**.