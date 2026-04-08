<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Fixtures;

use NotACms\Content\ContentItem;

final readonly class ContentItemFactory
{
    private const string DEFAULT_TITLE = 'Test Post';
    private const string DEFAULT_LOCALE = 'en';

    /**
     * Create a ContentItem with sensible defaults.
     *
     * @param array<string, mixed> $frontMatter
     */
    public static function create(array $frontMatter = [], string $htmlContent = '<p>Test content</p>', string $locale = self::DEFAULT_LOCALE, ?string $directoryKey = null, ?string $sourcePath = null, string $url = ''): ContentItem
    {
        return new ContentItem(
            frontMatter: array_merge(['title' => self::DEFAULT_TITLE], $frontMatter),
            htmlContent: $htmlContent,
            locale: $locale,
            url: $url,
            sourcePath: $sourcePath,
            isIndexItem: false,
            directoryKey: $directoryKey,
        );
    }

    /**
     * Create a published post (past date).
     */
    public static function publishedPost(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['date' => '2024-01-01'], $frontMatter),
            directoryKey: $directoryKey ?? 'test-post',
            url: $url,
        );
    }

    /**
     * Create a scheduled post (future date).
     */
    public static function scheduledPost(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['date' => '2099-12-31'], $frontMatter),
            directoryKey: $directoryKey ?? 'scheduled-post',
            url: $url,
        );
    }

    /**
     * Create a draft post.
     */
    public static function draftPost(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['draft' => true], $frontMatter),
            directoryKey: $directoryKey ?? 'draft-post',
            url: $url,
        );
    }

    /**
     * Create a pinned post (pinned until future date).
     */
    public static function pinnedPost(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['pinned' => '2099-01-01'], $frontMatter),
            directoryKey: $directoryKey ?? 'pinned-post',
            url: $url,
        );
    }

    /**
     * Create a page with menu weight.
     */
    public static function page(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['menu' => ['weight' => 10]], $frontMatter),
            directoryKey: $directoryKey ?? 'test-page',
            url: $url,
        );
    }

    /**
     * Create a ContentItem with specific tags.
     *
     * @param string ...$tags
     */
    public static function withTags(string ...$tags): ContentItem
    {
        return self::create(['tags' => $tags]);
    }

    /**
     * Create an index item (category listing).
     */
    public static function indexItem(array $frontMatter = [], ?string $directoryKey = null): ContentItem
    {
        return new ContentItem(
            frontMatter: array_merge(['title' => self::DEFAULT_TITLE], $frontMatter),
            htmlContent: '',
            locale: self::DEFAULT_LOCALE,
            url: '',
            sourcePath: null,
            isIndexItem: true,
            directoryKey: $directoryKey,
        );
    }

    /**
     * Create a ContentItem in Polish locale.
     */
    public static function polish(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: $frontMatter,
            htmlContent: '<p>Treść testowa</p>',
            locale: 'pl',
            directoryKey: $directoryKey,
            url: $url,
        );
    }

    /**
     * Create a featured post.
     */
    public static function featured(array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['featured' => true], $frontMatter),
            directoryKey: $directoryKey ?? 'featured-post',
            url: $url,
        );
    }

    /**
     * Create a ContentItem with a specific category.
     */
    public static function withCategory(string $category, array $frontMatter = [], ?string $directoryKey = null, string $url = ''): ContentItem
    {
        return self::create(
            frontMatter: array_merge(['category' => $category], $frontMatter),
            directoryKey: $directoryKey,
            url: $url,
        );
    }
}
