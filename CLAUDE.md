# Claude Code Guide

This repository uses AGENTS.md for detailed guidance.

## Quick Entry Points

- **Plans** — Execute tasks via `.plans/` files with full context, steps, verification
- **Code Review** — Use `/code-review` workflow with checklist and issue formatting
- **Mockups** — HTML files in `.mockups/` for UI designs (standalone, browser-ready)

## Development Commands

```bash
ddev start           # Start DDEV environment
ddev build           # Full build: cache clear + assets + app + pagefind
ddev code-check      # PHPStan + PHP CS Fixer checks
ddev code-fix        # Auto-fix code style issues
ddev exec <cmd>      # Run PHP/Composer commands in container
```

## Architecture Reference

- `AGENTS.md` — Complete guide (plans, code review, standards, patterns)
- `SECURITY.md` — Security model, CSP, Turnstile, hardening options
- `docs/ARCHITECTURE.md` — Content pipeline, routing, services
- `docs/CUSTOMIZATION.md` — Template overrides, SCSS, JS, PHP extensions, nginx
- `docs/EDITOR_GUIDE.md` — Frontmatter, images, series
- `docs/STYLEGUIDE.md` — Design tokens, components
- `docs/THEME_BUILDING.md` — Building a full theme (directory structure, registration, bare inheritance)
- `local/docs/EDITOR_GUIDE.md` — Site-specific voice and content rules
- `local/docs/STYLEGUIDE.md` — Local SCSS variables and component reference (if present)
