# Tests

## Running Tests

```bash
ddev test                          # Run all tests (testdox output)
ddev test --filter ContentItem     # Run specific test class
ddev test --filter testSlug        # Run specific test method
ddev test --coverage-text          # Generate coverage report
ddev test --coverage-html          # Generate HTML coverage report
```

## Test Structure

```
tests/
├── bootstrap.php
├── TmpDirTrait.php
├── Fixtures/
│   ├── content/               # _site.yaml, _routes.yaml, _tags.yaml, blog/ posts, pages/
│   └── templates/             # Empty — Twig falls through to core templates/
├── Unit/
│   ├── Fixtures/
│   │   └── ContentItemFactory.php
│   ├── Content/
│   │   ├── ContentItemTest.php
│   │   ├── ContentTreeTest.php
│   │   ├── Enum/CardLayoutTest.php
│   │   └── ValueObject/*Test.php
│   ├── Service/
│   │   ├── Content/
│   │   │   ├── RelatedPostsServiceTest.php
│   │   │   ├── TagTranslationServiceTest.php
│   │   │   └── TranslationMapBuilderTest.php
│   │   ├── Image/
│   │   │   └── ResponsiveImageServiceTest.php
│   │   ├── SiteConfigServiceTest.php
│   │   └── StructuredDataBuilderTest.php
│   └── Twig/
│       ├── SrcsetExtensionTest.php
│       └── TranslationMapTwigExtensionTest.php
└── Integration/
    ├── Command/
    │   └── BuildStaticSiteCommandTest.php
    ├── Controller/
    │   ├── BlogControllerTest.php
    │   ├── ContactControllerTest.php
    │   ├── DraftPreviewControllerTest.php
    │   ├── ErrorControllerTest.php
    │   ├── HomeControllerTest.php
    │   ├── MediaControllerTest.php
    │   ├── PageControllerTest.php
    │   ├── ProjectsControllerTest.php
    │   ├── ScheduledPreviewControllerTest.php
    │   ├── SearchControllerTest.php
    │   └── StyleguideControllerTest.php
    ├── DataCollector/
    │   ├── DraftPreviewDataCollectorTest.php
    │   └── ScheduledPreviewDataCollectorTest.php
    ├── EventListener/
    │   └── LocaleListenerTest.php
    ├── Form/
    │   └── ContactTypeTest.php
    ├── Routing/
    │   └── LocalizedRouteLoaderTest.php
    ├── Service/
    │   ├── Content/
    │   │   ├── ContentServiceTest.php
    │   │   ├── ContentTreeBuilderTest.php
    │   │   ├── MarkdownParserTest.php
    │   │   └── SidebarDataProviderTest.php
    │   ├── Image/
    │   │   ├── ImageResizerTest.php
    │   │   └── MediaFileResolverTest.php
    │   ├── Preview/
    │   │   ├── DraftPreviewServiceTest.php
    │   │   ├── ScheduledPreviewServiceTest.php
    │   │   └── SessionToggleServiceTest.php
    │   └── TurnstileValidatorTest.php
    └── Twig/
        ├── ContentTwigExtensionTest.php
        └── SiteConfigExtensionTest.php
```

## Conventions

- `declare(strict_types=1);` on line 3
- Yoda conditions: `null === $result`, `'' === $slug`
- Import order: `NotACms\` → PSR → Symfony → third-party
- Mock **interfaces only** — never mock concrete classes
- Data providers: `public static function`, return `iterable`
- Method names: `test{Scenario}{ExpectedResult}` (e.g., `testSlugReturnsEmptyWhenNotSet`)
- Use `ContentItemFactory` for all `ContentItem` creation — never construct directly in tests
- Use `createStub()` for interfaces where you only need return values (no `expects()`)
- Use `createMock()` only when you need to verify method call counts with `expects()`

## ContentItemFactory

Located at `tests/Unit/Fixtures/ContentItemFactory.php`. All static methods return `ContentItem`.

| Method | Purpose |
|---|---|
| `create(array $frontMatter, string $html, string $locale, ?string $directoryKey, ?string $sourcePath, string $url)` | Base factory with sensible defaults |
| `publishedPost(array $frontMatter, ?string $directoryKey, string $url)` | Past date (2024-01-01) |
| `scheduledPost(array $frontMatter, ?string $directoryKey, string $url)` | Future date (2099-12-31) |
| `draftPost(array $frontMatter, ?string $directoryKey, string $url)` | `draft: true` |
| `pinnedPost(array $frontMatter, ?string $directoryKey, string $url)` | `pinned: '2099-01-01'` |
| `page(array $frontMatter, ?string $directoryKey, string $url)` | `menu.weight: 10` |
| `indexItem(array $frontMatter, ?string $directoryKey)` | Category index item |
| `featured(array $frontMatter, ?string $directoryKey, string $url)` | `featured: true` |
| `polish(array $frontMatter, ?string $directoryKey, string $url)` | Polish locale |
| `withTags(string ...$tags)` | Specific tags |
| `withCategory(string $category, array $frontMatter, ?string $directoryKey, string $url)` | Specific category |

## Coverage

| Phase | Type | Target | Status |
|---|---|---|---|
| Phase 0 | Infrastructure (PHPUnit, config, DDEV command) | — | ✅ Done |
| Phase 1 | Pure unit tests (no mocks, no kernel) | ~50% | ✅ Done |
| Phase 2 | Unit tests with mocking (interfaces) | ~78% | ✅ Done |
| Phase 3 | Integration tests (kernel booted) | ~80% | ✅ Done |

**Coverage note:** Final coverage is ~80% lines, ~82% methods. The remaining gap is in:
- **ContactController** (success email path) — requires kernel-level MailerInterface mock
- **BuildStaticSiteCommand** (media copy, image optimization, error paths) — requires real filesystem with images
- **ErrorController** (`__invoke` with HttpException) — hard to trigger via HTTP client
- **BlogController** (renderPost, renderComingSoon, series logic) — requires specific content setup

These are high-complexity, low-ROI tests. 80% covers all public APIs, main code paths, and edge cases thoroughly.

See `.plans/done/DONE-2026-04-03-013335-phpunit-tests.md` for the full plan.

## Adding a New Test

1. **Identify the type**: pure unit (no deps), mocked unit (interface deps), or integration (kernel needed)
2. **Create the test file** in the matching directory under `tests/Unit/` or `tests/Integration/`
3. **Follow naming**: `{ClassName}Test.php`
4. **Use the factory** for any `ContentItem` instances
5. **Run** `ddev test` to verify

## Fixture Strategy for Filesystem-Dependent Services

Services that read YAML files (`SiteConfigService`, `TagTranslationService`) use temp directories with the shared `TmpDirTrait` for cleanup:

```php
use NotACms\Tests\TmpDirTrait;

final class MyTest extends TestCase
{
    use TmpDirTrait;

    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/notacms_test_' . uniqid();
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }
}
```

The trait uses Symfony's `Filesystem` component for reliable cross-platform cleanup.

## Test Writing Best Practices

**PHPUnit 13 mock behavior:**
- Use `createStub()` in `setUp()` for mocks that won't have `expects()` calls
- Create local `createMock()` only in tests that use `expects(self::once()/method()->with()`
- This avoids "No expectations were configured" notices from PHPUnit 13

```php
protected function setUp(): void
{
    $this->dependency = $this->createStub(DependencyInterface::class);  // OK - no expects
}

public function testMethodCallsDependency(): void
{
    $mock = $this->createMock(DependencyInterface::class);  // OK - has expects
    $mock->expects(self::once())->method('doSomething')->willReturn('result');
    // ...
}
```

**DOM Crawler assertions:**
- `symfony/css-selector` is NOT installed — `filter()` with CSS selectors throws `LogicException`
- Use `filterXpath()` instead: `$crawler->filterXpath('//meta[@property="og:title"]/@content')`
- For existence checks: `self::assertGreaterThan(0, $node->count())`

```php
// Good
$meta = $crawler->filterXpath('//meta[@name="robots"]/@content');
self::assertGreaterThan(0, $meta->count());
self::assertStringContainsString('noindex', $meta->text());

// Bad - throws LogicException
$crawler->filter('meta[name="robots"]');
```

**Avoid weak assertions:**
- Never use `self::assertTrue(true)` — verify actual behavior instead
- Avoid `assertContains($statusCode, [200, 404])` — a test that passes regardless of outcome
- Use specific assertions: `assertResponseStatusCodeSame(200)`, `assertStringContainsString()`, XPath checks
- If testing multiple outcomes, split into separate tests

**ContentService cache variants:**
- Cache keys use locale variants: `en`, `en:drafts`, `en:scheduled`
- Drafts/scheduled posts require separate cache entries
- Cache is indefinite in prod, disabled in dev
- In tests: clear cache or use `ContentService` without caching logic

**Controller tests:**
- Use `WebTestCase::createClient()` to boot Symfony kernel
- Use `$this->client->request()` for HTTP calls, `$this->client->getResponse()->getStatusCode()` for status
- Use DOM Crawler for HTML assertions (see "DOM Crawler assertions" above)
