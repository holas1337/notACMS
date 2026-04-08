# AGENTS.md

This file provides guidance to Claude Code (claude.ai/code) and other AI agents when working with code in this repository.

---

## Local overrides

The `local/` directory contains instance-specific overrides. If a `local/docs/` equivalent exists for any `docs/` file, **always read the `local/docs/` version** — it contains site-specific customizations that supersede the global documentation.

Examples:
- `local/docs/EDITOR_GUIDE.md` → site-specific writing guide (categories, tags, voice, image generation)
- `local/docs/STYLEGUIDE.md` → site-specific design tokens and variables

---

## Plans

Non-trivial tasks are tracked as plan files in `.plans/` at the project root.

**Naming:** `YYYY-MM-DD-HHMMSS-plan-name.md` — always run `date '+%Y-%m-%d-%H%M%S'` to get the exact current timestamp before creating the file (e.g. `2026-02-25-150641-contact-form-fix.md`). Never guess or make up times.

**Content:** The plan file must contain the **full plan** — this means ALL of the following sections are required:
- **Context** — why the change is being made, what the problem is, what prompted it
- **Scope / files to modify** — exact file paths and what changes to each
- **Technical details** — the actual code/approach (before/after snippets, variable names, design decisions)
- **Steps** — numbered checkboxes covering every implementation step
- **Verification** — how to confirm the change works

A stub with just a title, 2 bullet points, and 3 checkboxes is **not acceptable**. The plan file must be detailed enough that another agent could implement it from scratch using only that file.

**Timing:** Create the `.plans/` file **before** starting implementation — not after. The file is the source of truth; update checkboxes as steps complete. When using plan mode (Claude-internal `~/.claude/plans/`), **copy the plan to `.plans/` as the very first step of implementation**, before touching any code.

**Updates:** Check items off as they complete. If requirements change mid-task, update the plan file rather than abandoning it.

**Partial implementation:** When a plan is implemented in parts across multiple sessions, reorganize the plan file into two clear sections — **DONE** and **TO DO** — both in the issue list itself and in the summary table. Mark completed items with ✅ in the summary table. Do not leave status only at the bottom; every issue entry should visually reflect its current state.

**Completing:** When all steps are done, move the file to `.plans/done/` and prepend `DONE-` (e.g. `.plans/done/DONE-2026-02-25-083956-architecture-md.md`).

**Phased execution:** When executing a plan with multiple phases, **always stop after completing each phase** and ask the user for permission to continue. Never auto-proceed to the next phase.

**Resuming work:** If unfinished plans exist in `.plans/` that are relevant to the current task, list them and ask the user what to do — do not resume automatically.

---

## Code review

When the user asks for a **code review** (phrases like "review the code", "audit the codebase", "what can be improved"), follow this checklist in order. Create a plan file before starting any implementation.

### What to check

**Architecture / DRY/ KISS/ SOLID/ YAGNI/ TDA/ SCA**
- Duplicated logic across files (extract to shared service or interface method)
- Classes doing more than one job (split or extract)
- Interfaces that are too wide (split via ISP)
- Logic that belongs in the constructor but runs side-effectfully, or vice-versa (lazy init vs eager init)
- Unused public methods, interfaces, implemented interfaces (`YAGNI`)

**Hardcoded values**
- Hardcoded strings that should come from config, env, or `_site.yaml` (emails, URLs, role names)
- Magic numbers that should be named constants (`private const int RECENT_POST_LIMIT = 5`)
- Environment name strings (`'dev'`) — replace with `kernel.debug` boolean injection
- SCSS: hardcoded colors/sizes that have a variable — use the variable

**Code style (PHP)**
- Non-yoda comparisons: `$x === $y` where `$x` is a variable — flip to `$y === $x` (literal/stable value on the left)
- Missing blank line before `return` (except single-statement methods and returns at block start)
- Import order: App → PSR → Symfony → third-party (League, etc.)
- `private const` in a concrete class that should be `public const` in its interface (so callers can reference it)

**Security**
- User-controlled input passed to `glob()`, filesystem paths, or shell commands without sanitization — use `Finder` + `realpath()` guard instead

**Twig**
- Loose comparisons (`==`) — use `is same as()`
- Hardcoded URL strings — use `path('route_' ~ locale)` or `content_url()`
- Data passed to every render call that could be a global Twig variable instead

**SCSS**
- Hardcoded color/size values that have a `$variable` equivalent in `_variables.scss`
- `sg-*` classes in the styleguide that duplicate real component classes instead of reusing them

**Documentation**
- `docs/ARCHITECTURE.md` service/file tables out of sync with `src/`
- `docs/STYLEGUIDE.md` conventions out of sync with `assets/styles/` implementation
- `local/docs/STYLEGUIDE.md` variable tables out of sync with `assets/styles/_variables.scss`
- Component reference tables out of sync with `templates/components/`


### Output format

Present findings as a numbered issue list grouped by category. For each issue include:
- **File:line** reference
- One-sentence description of the problem
- Concrete fix (before/after snippet or instruction)

After presenting findings, **stop and wait** for the user to decide which issues to fix and in what order. Do not start implementing unless explicitly asked.

---

## Mockups

HTML mockups live in `.mockups/` at the project root. The folder is gitignored.

**Format:** Always create **standalone HTML files** that can be opened directly in a browser (`file://`). Inline all CSS — never reference project assets or dev server URLs.

**Naming:** If the mockup relates to a plan item, prefix with the plan item number: `{number}-{short-description}.html` (e.g. `18-sidebar-collapsible.html`, `17-reading-progress.html`). For standalone mockups not tied to a plan, use a descriptive name (e.g. `contact-form-redesign.html`).

**Content:** Match the site's real styles (dark terminal theme, colors, fonts, spacing) as closely as possible. Include before/after comparisons when proposing changes. Use a constrained viewport width when demonstrating mobile behavior.

---

## Site tone & content voice

When writing or editing any content (blog posts, pages, UI strings, descriptions), **read `local/docs/EDITOR_GUIDE.md`** before proceeding. It contains the site-specific reference for voice, categories, approved tags, titles, descriptions, intros, body structure, EN/PL parity, and image generation styles.

`local/docs/EDITOR_GUIDE.md` is seeded from `docs/examples/docs/EDITOR_GUIDE.md` on first bootstrap and is gitignored — operators customise it for their own site. System-level documentation (frontmatter fields, URL structure, series, drafts, etc.) lives in `docs/EDITOR_GUIDE.md`.

---

## Documentation review

The project has five documentation files: `README.md` (root) and four in `docs/`: `ARCHITECTURE.md`, `EDITOR_GUIDE.md`, `STYLEGUIDE.md`, `LOCALES.md`.

**After any implementation task, review whether the change affects any of these docs and update them.** Do not leave docs out of sync with the code.

| Changed area | Docs to review |
|---|---|
| Architecture, routing, services, content pipeline | `docs/ARCHITECTURE.md`, `README.md` |
| Locale config, translations, adding/removing languages | `docs/LOCALES.md`, `docs/ARCHITECTURE.md` |
| Content authoring, frontmatter, images, slugs | `docs/EDITOR_GUIDE.md` |
| SCSS, design tokens, Twig components | `local/docs/STYLEGUIDE.md`, `docs/STYLEGUIDE.md` |
| nginx, Docker, env vars | `README.md` |
| Build commands, deploy process, dev workflow | `README.md` |
| Adding or editing a blog post or page | *(no doc to update — CONTENT_ANALYSIS.md removed)* |

---

## Changelog

**Do not include `local/` directory content in CHANGELOG.md.** The changelog should describe the project itself — features, architecture, tech stack, dependencies, and technical changes — not instance-specific content.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## Code quality tools

When working with PHP code, use these commands:

```bash
# PHP CS Fixer — auto-fix code style
ddev exec vendor/bin/php-cs-fixer fix

# PHPStan — static type analysis
ddev exec vendor/bin/phpstan analyse

# Combined check (via DDEV custom command)
ddev code-check
ddev code-fix
```

Configuration:
- `.php-cs-fixer.php` — PHP CS Fixer rules (@Symfony + custom)
- `phpstan.neon` — PHPStan at level 6

---

## Code standards

### PHP Standards
- **Strict Types**: All files MUST have `declare(strict_types=1);` on line 3
  - **Exception**: Symfony-generated files (Kernel.php) are exempt from strict_types requirement
- **PHP Version**: 8.5 (DDEV uses 8.5)
- **PSR-4 Autoloading**: `NotACms\` namespace maps to `src/`

### Formatting
- Indent: 4 spaces (2 spaces for YAML)
- Line endings: LF
- Charset: UTF-8
- Trim trailing whitespace (except .md)
- Insert final newline

### Naming Conventions
- Classes: PascalCase (e.g., `ContentService`)
- Interfaces: PascalCase with Interface suffix (e.g., `ContentServiceInterface`)
- Methods/Variables: camelCase (e.g., `getRecentPosts`)
- Constants: UPPER_SNAKE_CASE
- Private properties: camelCase with type declarations

### Type Declarations
- Always use explicit type declarations for parameters and return types
- Use `?Type` for nullable types
- Use `array<Type>` in PHPDoc for typed arrays
- Return types: `: void`, `: int`, `: array`, `: ?ClassName`, etc.

### Code Patterns

**Class Structure:**
```php
<?php

declare(strict_types=1);

namespace NotACms\Service;

use NotACms\Content\ContentItem;  // Own namespace imports first
use Psr\...;                  // External interfaces
use Symfony\...;              // Framework imports
use League\...;               // Third-party

final class ContentService implements ContentServiceInterface
{
    public function __construct(
        private readonly ContentTreeBuilder $builder,
    ) {
    }
}
```

**Yoda Conditions:**
```php
// Preferred
if (0 === $count) { }
if (null !== $page) { }
if ([] === $posts) { }

// For instance checks, prefer positive logic
if ($item instanceof ContentItem) { }

// When negation is needed
if (!($object instanceof Type)) { }

// Comparison operators
true === $isDryRun
0 < $result->errors
```

**Strict Comparisons:**
- Use `===` and `!==` instead of `==` and `!=`

**Date comparisons:**
- Always put `$date` on the left and `now` on the right: `$date > new \DateTimeImmutable()`
- Use right-open intervals: the boundary value belongs to the next state, not the current one
  - `isScheduled`: `$date > now` — post with `date` exactly equal to now is already published
  - `isPinned`: `$until > today` — pin expires on the `until` date (that day it is no longer pinned)

**Blank Line Before Return Statements:**
Per Symfony coding standards (`@Symfony` ruleset), an empty line must precede all `return` statements:

```php
// BAD
public function getValue(): string
{
    $value = calculate();
    return $value;
}

// GOOD
public function getValue(): string
{
    $value = calculate();

    return $value;
}
```

**Exceptions:**
- Single-line methods (return only)
- Return statements at the start of a block with no preceding statements
- Ternary operators on a single line

**Rationale:** Improves readability by visually separating the computation logic from the return statement.

**Error Handling:**
```php
try {
    // operation
} catch (\Throwable $e) {
    // Return error state or re-throw
}
```

### Architecture Principles
- **Final Classes**: All classes must be `final` (no inheritance)
- **Interface Segregation**: Small, focused interfaces (e.g., `ContentServiceInterface`)
- **Dependency Injection**: Inject via interfaces, not concrete classes
- **Single Responsibility**: Each service has one job
- **DRY**: Don't duplicate logic — extract to shared service or interface method
- **KISS**: Keep It Simple — avoid over-engineering
- **YAGNI**: You Aren't Gonna Need It — avoid speculative features

### Interface Segregation (ISP)

**Each interface should have a single purpose.** Avoid "god interfaces" with multiple unrelated methods.

**Rule:** An interface should have 1-5 methods maximum. If more are needed, consider splitting.

### Value Objects over Associative Arrays

**Never return associative arrays for complex data.** Use typed Value Objects instead.

**Rule:** If a method returns more than one piece of related data, return a Value Object.

**Example - Good:**
```php
final readonly class TagCount
{
    public function __construct(
        public string $slug,
        public int $count,
    ) {}
}
```

**Why:** Type safety, IDE autocomplete, self-documenting code, immutability.

### Enums for Type-Safe Constants

**Use PHP enums instead of string/integer constants for type safety.**

### Service Naming Conventions

**Concrete service classes should match their interface name pattern.**

**Rule:** If interface is `XxxInterface`, concrete class should be `Xxx` (without "Interface" suffix).

**Examples:**
| Interface | Concrete Class |
|-----------|---------------|
| `ContentServiceInterface` | `ContentService` |
| `TurnstileValidatorInterface` | `TurnstileValidator` |
| `SidebarDataProviderInterface` | `SidebarDataProvider` |
| `ImageResizerInterface` | `ImageResizer` |
| `DraftPreviewServiceInterface` | `DraftPreviewService` |
| `ScheduledPreviewServiceInterface` | `ScheduledPreviewService` |
| `MarkdownParserInterface` | `MarkdownParser` |
| `ContentTreeBuilderInterface` | `ContentTreeBuilder` |

**Interface requirement:** Every injectable class in `src/Service/` (and subdirectories) must have a corresponding `XxxInterface`. The concrete class implements the interface; all injection points (controllers, commands, other services) type-hint against the interface, never the concrete class.

**Constants belong in the interface:** Any constant used by a service must be declared in its interface (as `public const`), not in the concrete class. The concrete class inherits and uses it via `self::CONSTANT_NAME`.

Current interfaces (grouped by subdirectory):
- `NotACms\Service\Content`: `ContentServiceInterface`, `ContentTreeBuilderInterface`, `MarkdownParserInterface`, `SidebarDataProviderInterface`, `TranslationMapBuilderInterface`
- `NotACms\Service\Image`: `ImageResizerInterface`, `MediaFileResolverInterface`, `ResponsiveImageServiceInterface`
- `NotACms\Service\Preview`: `DraftPreviewServiceInterface`, `ScheduledPreviewServiceInterface`
- `NotACms\Service` (root): `SiteConfigServiceInterface`, `TurnstileValidatorInterface`

### Configuration Principles
- **Attributes over YAML/XML**: Prefer PHP 8 attributes for service configuration where possible
- Use `#[Autowire]` attribute for constructor parameters requiring parameters or environment variables
- Keep third-party bundle configurations in YAML files
- Example:
  ```php
  use Symfony\Component\DependencyInjection\Attribute\Autowire;

  final class ContentTreeBuilder
  {
      public function __construct(
          #[Autowire('%kernel.project_dir%/content')]
          private readonly string $contentDir,
      ) {
      }
  }
  ```

### Import Organization
```php
// 1. App namespace imports
use NotACms\Content\ContentItem;
use NotACms\Service\ContentService;
// 2. PSR interfaces
use Psr\Log\LoggerInterface;
// 3. Symfony components
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
// 4. Third-party libraries
use League\CommonMark\MarkdownConverter;
```

### Value Objects
- Immutable DTOs with `public readonly` properties (no getters needed)
- Use constructor property promotion for cleaner code
- Access properties directly: `$valueObject->property`
- All data via constructor, no setters

### Constructor Guidelines
- **No Side Effects**: Constructors should not load data or perform I/O operations
- Use lazy initialization when possible
- Inject dependencies only
- Move initialization logic to private methods triggered on first use

### Return Values
- **Value Objects over Arrays**: Return typed Value Objects instead of associative arrays
- Use Value Objects for multi-value returns (e.g. result stats, operation results)
- Improves type safety and self-documentation

### Comments Policy

**Golden Rule:** Good code does not need comments. If you need to comment your code, refactor it.

**Allowed comments:**
1. **PHPDoc type annotations** - `@param`, `@return`, `@var` for static analysis (PHPStan requirement)
2. **"Why" comments** - Explaining complex business logic, compliance requirements, or non-obvious decisions

**NOT allowed comments:**
1. **"What" comments** - Explaining what the code does (code should be self-documenting)
   - Instead: Extract to private method with descriptive name
   - Instead: Rename variables/functions for clarity

---

## Feature Addition Patterns

When adding new features, follow these templates and patterns:

### Adding a New Service

**Step 1: Create Interface** (in the appropriate subdirectory: `src/Service/Content/`, `src/Service/Image/`, `src/Service/Preview/`, or `src/Service/` for standalone external services)
```php
<?php

declare(strict_types=1);

namespace NotACms\Service\Content; // or Image, Preview, or root NotACms\Service

interface NewServiceInterface
{
    public function performAction(string $input): ActionResult;
}
```

**Step 2: Create Implementation** (same directory)
```php
<?php

declare(strict_types=1);

namespace NotACms\Service\Content; // match the interface namespace

final class NewService implements NewServiceInterface
{
    public function __construct(
        // Add dependencies here
    ) {
    }

    public function performAction(string $input): ActionResult
    {
        return new ActionResult(/* ... */);
    }
}
```

**Step 3: Inject via Interface**
```php
public function __construct(
    private readonly NewServiceInterface $newService,
) {
}
```

### Adding a New Value Object

```php
<?php

declare(strict_types=1);

namespace NotACms\Content;

final readonly class TagCount
{
    public function __construct(
        public string $slug,
        public int $count,
    ) {
    }
}
```

### Adding a New Configuration Option

**Step 1: Add to `.env`:**
```bash
NEW_CONFIG_OPTION=default_value
```

**Step 2: Inject via Autowire:**
```php
public function __construct(
    #[Autowire('%env(NEW_CONFIG_OPTION)%')]
    private readonly string $newConfigOption,
) {
}
```

**Step 3: Use type casting for non-strings:**
```php
#[Autowire('%env(int:NEW_NUMERIC_OPTION)%')]
private readonly int $newNumericOption,
```

---

## Project architecture reference

For detailed architecture documentation (content pipeline, routing, templates, nginx, etc.), see:

- `docs/ARCHITECTURE.md` — Content layer, service layer, routing, static build, multi-language
- `docs/EDITOR_GUIDE.md` — Content authoring, frontmatter, images, series
- `docs/STYLEGUIDE.md` — Design tokens, components, living styleguide
- `README.md` — Development environment, commands, deployment

---

## AI Image Generation

When working with content images (blog post featured images, background image, any visual assets), **read `local/docs/EDITOR_GUIDE.md` → "AI image generation"** before proceeding. It contains the full reference for:

- Model selection and parameters (Draw Things models, when to use each)
- Model switching workflow (Draw Things app settings that must be changed manually)
- Generation batch rules (always 8 images in parallel)
- Preferred styles per series
- Saving generated files (`.generated/` session folders)
- Image format rules (WebP, exceptions)
- Featured image spec (1280×720, quality 82) and ImageMagick crop commands
- `image_alt` requirement (set in both EN and PL frontmatter after choosing a featured image)
- Fallback to Hugging Face if Draw Things is unavailable
