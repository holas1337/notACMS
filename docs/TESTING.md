# Testing

This document covers how to run, write, and extend tests for the notACMS project.

## Running Tests

### Run all tests

```bash
ddev test
```

### Run with coverage

```bash
ddev test --coverage-text --coverage-html=var/coverage
```

Coverage HTML report will be generated in `var/coverage/`.

### Run specific test

```bash
ddev test --filter ContentItemTest
ddev test --filter testTitleReturnsFrontMatterTitle
```

### Verbose output

```bash
ddev test --testdox
```

## Test Organization

Tests are organized in `tests/` directory:

```
tests/
├── bootstrap.php              # PHPUnit bootstrap (loads Composer autoload + env)
├── TmpDirTrait.php            # Shared temp-directory fixture helper (used by filesystem-dependent tests)
├── Fixtures/                  # Test fixture data (isolated from local/ instance)
│   ├── content/               # _site.yaml, _routes.yaml, _tags.yaml, blog/ posts, pages/
│   └── templates/             # Empty — Twig falls through to core templates/ (drop overrides here as needed)
├── Unit/                      # Pure unit tests (no Symfony kernel)
│   ├── Content/
│   │   ├── ContentItemTest.php
│   │   ├── ContentTreeTest.php
│   │   ├── Enum/
│   │   │   └── CardLayoutTest.php
│   │   └── ValueObject/
│   │       └── *Test.php
│   ├── Service/
│   │   ├── Content/
│   │   │   ├── RelatedPostsServiceTest.php
│   │   │   ├── TagTranslationServiceTest.php
│   │   │   └── TranslationMapBuilderTest.php
│   │   ├── Image/
│   │   │   └── ResponsiveImageServiceTest.php
│   │   └── SiteConfigServiceTest.php
│   ├── Twig/
│   │   ├── LangSwitcherExtensionTest.php
│   │   └── SrcsetExtensionTest.php
│   └── Fixtures/
│       └── ContentItemFactory.php
├── Integration/               # Integration tests (kernel booted)
│   ├── Command/
│   │   └── BuildStaticSiteCommandTest.php
│   ├── Controller/
│   │   ├── BlogControllerTest.php
│   │   ├── ContactControllerTest.php
│   │   ├── DraftPreviewControllerTest.php
│   │   ├── ErrorControllerTest.php
│   │   ├── HomeControllerTest.php
│   │   ├── MediaControllerTest.php
│   │   ├── PageControllerTest.php
│   │   ├── ProjectsControllerTest.php
│   │   ├── ScheduledPreviewControllerTest.php
│   │   ├── SearchControllerTest.php
│   │   └── StyleguideControllerTest.php
│   ├── DataCollector/
│   │   ├── DraftPreviewDataCollectorTest.php
│   │   └── ScheduledPreviewDataCollectorTest.php
│   ├── EventListener/
│   │   └── LocaleListenerTest.php
│   ├── Form/
│   │   └── ContactTypeTest.php
│   ├── Routing/
│   │   └── LocalizedRouteLoaderTest.php
│   ├── Service/
│   │   ├── Content/
│   │   │   ├── ContentServiceTest.php
│   │   │   ├── ContentTreeBuilderTest.php
│   │   │   ├── MarkdownParserTest.php
│   │   │   └── SidebarDataProviderTest.php
│   │   ├── Image/
│   │   │   ├── ImageResizerTest.php
│   │   │   └── MediaFileResolverTest.php
│   │   ├── Preview/
│   │   │   ├── DraftPreviewServiceTest.php
│   │   │   ├── ScheduledPreviewServiceTest.php
│   │   │   └── SessionToggleServiceTest.php
│   │   └── TurnstileValidatorTest.php
│   └── Twig/
│       ├── ContentTwigExtensionTest.php
│       └── SiteConfigExtensionTest.php
```

### Unit Tests

- No Symfony kernel booted
- Fast execution
- Zero dependencies or mocked
- **Coverage target:** ~50%

### Integration Tests

- Symfony kernel booted via `KernelTestCase` or `WebTestCase`
- Test real services and their interactions
- Use test fixtures and environment overrides
- **Coverage target:** ~80%

## Test Conventions

### File Structure

```php
<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;

final class ContentItemTest extends TestCase
{
    public function testScenarioExpectedResult(): void
    {
        // Arrange
        $item = ContentItemFactory::publishedPost();

        // Act
        $result = $item->title();

        // Assert
        self::assertSame('Test Post', $result);
    }
}
```

### Coding Standards

- `declare(strict_types=1);` on line 3
- Yoda conditions: `null === $result`, `'' === $slug`
- Import order: `NotACms\` → PSR → Symfony → third-party
- Method names: `test{Scenario}{ExpectedResult}`

### Assertions

```php
self::assertSame($expected, $actual);   // Same value and type
self::assertNull($value);                // Null check
self::assertTrue($condition);           // Boolean true
self::assertContainsOnlyInstancesOf(ContentItem::class, $array);
self::assertCount(3, $array);
```

## ContentItemFactory

Use `ContentItemFactory` to create `ContentItem` test fixtures:

```php
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;

// Basic post
$post = ContentItemFactory::publishedPost();

// With custom frontmatter
$post = ContentItemFactory::publishedPost(['title' => 'Custom Title', 'tags' => ['php']]);

// Specific states
$draft = ContentItemFactory::draftPost();
$scheduled = ContentItemFactory::scheduledPost();
$pinned = ContentItemFactory::pinnedPost();
$page = ContentItemFactory::page();

// With URL (for ContentTree URL mapping tests)
$post = ContentItemFactory::publishedPost([], 'my-post', '/my-post');
```

### Factory Methods

| Method | Purpose |
|--------|---------|
| `create($frontMatter, $html, $locale, $dirKey, $sourcePath, $url)` | Base factory with all params |
| `publishedPost($frontMatter, $dirKey, $url)` | Published post (past date) |
| `scheduledPost($frontMatter, $dirKey, $url)` | Future-dated post |
| `draftPost($frontMatter, $dirKey, $url)` | Draft post |
| `pinnedPost($frontMatter, $dirKey, $url)` | Pinned post |
| `page($frontMatter, $dirKey, $url)` | Static page |
| `withTags(...$tags)` | Post with specific tags |
| `withCategory($category, $frontMatter, $dirKey, $url)` | Post with category |
| `polish($frontMatter, $dirKey, $url)` | Polish locale content |
| `featured($frontMatter, $dirKey, $url)` | Featured post |
| `indexItem($frontMatter, $dirKey)` | Category index item |

## Common Test Patterns

### Testing a value object

```php
final class TagCountTest extends TestCase
{
    public function testConstruction(): void
    {
        $tag = new TagCount('php', 100);

        self::assertSame('php', $tag->slug);
        self::assertSame(100, $tag->count);
    }
}
```

### Testing a service with mock

```php
final class ResponsiveImageServiceTest extends TestCase
{
    private ResponsiveImageService $service;
    private MockObject&SiteConfigServiceInterface $configService;

    protected function setUp(): void
    {
        $this->configService = $this->createMock(SiteConfigServiceInterface::class);
        $this->service = new ResponsiveImageService($this->configService);
    }

    public function testGetVariantWidths(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([640, 960]);

        $result = $this->service->getVariantWidths(1280);

        self::assertSame([640, 960], $result);
    }
}
```

### Testing collection behavior

```php
final class ContentTreeTest extends TestCase
{
    public function testGetAllPostsSortsByDateDesc(): void
    {
        $tree = new ContentTree();
        $old = ContentItemFactory::publishedPost(['date' => '2023-01-01']);
        $new = ContentItemFactory::publishedPost(['date' => '2024-01-01']);

        $tree->addPost($old);
        $tree->addPost($new);

        $posts = $tree->getAllPosts();

        self::assertSame([$new, $old], $posts);
    }
}
```

### Testing edge cases

```php
public function testReturnsEmptyWhenNoMatches(): void
{
    $tree = new ContentTree();
    $tree->addPost(ContentItemFactory::publishedPost());

    $result = $tree->findByDirectoryKey('nonexistent');

    self::assertNull($result);
}
```

## Writing New Tests

1. **Identify the class** to test and its dependencies
2. **Choose test type:** Unit (no kernel) or Integration (kernel booted)
3. **Create test file:** `tests/Unit/.../{ClassName}Test.php`
4. **Write test methods** following naming convention
5. **Run tests:** `ddev test --filter {ClassName}Test`

### Example: Adding a unit test for a service

```php
<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service\MyModule;

use NotACms\Service\MyModule\MyService;
use PHPUnit\Framework\TestCase;

final class MyServiceTest extends TestCase
{
    public function testCalculateReturnsCorrectValue(): void
    {
        $service = new MyService();

        $result = $service->calculate(5, 3);

        self::assertSame(8, $result);
    }
}
```

## Coverage Goals

| Phase | Target | Status |
|-------|--------|--------|
| Phase 0 | Infrastructure ready | ✅ Complete |
| Phase 1 | ~50% (pure unit tests) | ✅ Complete |
| Phase 2 | ~78% (unit with mocking) | ✅ Complete |
| Phase 3 | ~80% (integration tests) | ✅ Complete |

Check coverage:

```bash
ddev test --coverage-text
```

## Configuration

- **PHPUnit:** 13.1.0
- **Config:** `phpunit.dist.xml`
- **Bootstrap:** `tests/bootstrap.php`
- **Test environment:** `APP_ENV=test`

## Troubleshooting

### Tests fail with readonly property error

Don't modify readonly properties in tests. Use the factory methods or construct the object with the correct initial values.

### PHPUnit fails to find tests

Ensure test files are in `tests/` directory and class names end with `Test.php`.

### Deprecation warnings

The project runs PHPUnit with `failOnDeprecation="true"`. Fix deprecations before merging.
