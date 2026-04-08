# Contributing to notACMS

Thank you for your interest in contributing to notACMS. This is a Symfony-based static site generator designed to be customized via the `local/` override system — most customizations never touch core files.

## Development Environment

notACMS uses [DDEV](https://ddev.readthedocs.io/) for local development:

```bash
ddev start
ddev build   # bootstraps local/ with demo content
```

## Code Quality

All code quality checks are automated. Run them locally before submitting a PR:

```bash
ddev test         # run PHPUnit test suite
ddev code-check   # composer validate + audit + PHP CS Fixer (dry-run) + Rector (dry-run) + PHPStan + Twig lint
ddev code-fix     # auto-fix PHP CS Fixer and Rector issues, then re-run code-check
```

The CI pipeline (`.github/workflows/ci.yml`) runs these same checks on every push and pull request.

## Test Coverage

The project has ~80% test coverage. Write tests for any new functionality:

```bash
ddev test                    # run all tests
ddev test --filter Name      # run specific test class or method
ddev test --coverage-text    # generate coverage report
```

Tests live in `tests/` with `Unit/` for pure logic and `Integration/` for kernel-booted tests. See `docs/TESTS.md` for conventions and the `ContentItemFactory` helper.

## Pull Request Process

1. **Fork** the repository and create a feature branch (`feature/my-change`)
2. **Write tests** for new functionality (see `docs/TESTS.md`)
3. **Run** `ddev code-fix` and `ddev test` — both must pass
4. **Update docs** if your change affects architecture, configuration, or user-facing behavior
5. **Open a PR** with a clear description of what changed and why

### Commit Messages

Use descriptive, imperative-style messages:

```
add tag translation service for multi-language tag slugs
fix content URL computation for non-default locale
refactor sidebar data provider to use value objects
```

### Branch Naming

- `feature/` — new functionality
- `fix/` — bug fixes
- `docs/` — documentation only
- `chore/` — tooling, config, CI

## Customization vs. Contributing

If you only need to customize your own site (templates, styles, content, translations), you don't need to fork or contribute to core. The `local/` directory handles everything — see [docs/CUSTOMIZATION.md](docs/CUSTOMIZATION.md).

Core contributions (changes to `src/`, `templates/`, `assets/`) are for improvements that benefit all users.

## Code Style

- **PHP**: Symfony conventions with Yoda conditions, strict types, PHP CS Fixer + Rector
- **Twig**: strict comparisons (`is same as()`), named routes (`path('route_' ~ locale)`), no hardcoded URLs
- **SCSS**: all values via variables from `_variables.scss`, no hardcoded colors/sizes
- **Import order**: `NotACms\` → PSR → Symfony → third-party

See `AGENTS.md` for detailed coding standards.

## Reporting Issues

- **Bugs**: Use the bug report template — include PHP version, steps to reproduce, and expected vs actual behavior
- **Features**: Use the feature request template — describe the use case and proposed solution
- **Security**: See [SECURITY.md](SECURITY.md) — do not open public issues for vulnerabilities
