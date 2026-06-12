<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content;

use NotACms\Content\ContentItem;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class ContentItemTest extends TestCase
{
    // ===== Constructor & Basic Properties =====

    public function testConstructorCreatesInstance(): void
    {
        $item = ContentItemFactory::create();

        self::assertInstanceOf(ContentItem::class, $item);
    }

    public function testTitleReturnsFrontMatterTitle(): void
    {
        $item = ContentItemFactory::create(['title' => 'My Test Title']);

        self::assertSame('My Test Title', $item->title());
    }

    public function testTitleReturnsEmptyStringWhenNotSet(): void
    {
        $item = ContentItemFactory::create(['title' => '']);

        self::assertSame('', $item->title());
    }

    public function testDescriptionReturnsFrontMatterDescription(): void
    {
        $item = ContentItemFactory::create(['description' => 'Test description']);

        self::assertSame('Test description', $item->description());
    }

    public function testDescriptionReturnsEmptyStringWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertSame('', $item->description());
    }

    public function testHtmlContentIsAccessible(): void
    {
        $html = '<p>Test content</p>';
        $item = ContentItemFactory::create([], $html);

        self::assertSame($html, $item->htmlContent);
    }

    public function testLocaleIsAccessible(): void
    {
        $item = ContentItemFactory::create([], '', 'pl');

        self::assertSame('pl', $item->locale);
    }

    public function testSourcePathIsAccessible(): void
    {
        $path = '/path/to/file.md';
        $item = ContentItemFactory::create([], '', 'en', null, $path);

        self::assertSame($path, $item->sourcePath);
    }

    public function testDirectoryKeyIsAccessible(): void
    {
        $item = ContentItemFactory::create([], '', 'en', 'my-post');

        self::assertSame('my-post', $item->directoryKey());
    }

    // ===== Slug =====

    public function testSlugExtractsLastSegmentFromPath(): void
    {
        $item = ContentItemFactory::create(['slug' => '2024/01/10/my-post']);

        self::assertSame('my-post', $item->slug());
    }

    public function testSlugHandlesTrailingSlash(): void
    {
        $item = ContentItemFactory::create(['slug' => '2024/01/10/my-post/']);

        self::assertSame('my-post', $item->slug());
    }

    public function testSlugHandlesSingleSegment(): void
    {
        $item = ContentItemFactory::create(['slug' => 'my-post']);

        self::assertSame('my-post', $item->slug());
    }

    public function testSlugReturnsEmptyWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertSame('', $item->slug());
    }

    // ===== Date Handling =====

    public function testDateParsesYMDString(): void
    {
        $item = ContentItemFactory::create(['date' => '2024-01-15']);

        $expected = new \DateTimeImmutable('2024-01-15 00:00:00');
        self::assertEquals($expected, $item->date());
    }

    public function testDateHandlesTimestampInt(): void
    {
        $timestamp = 1705334400; // 2024-01-15 00:00:00
        $item = ContentItemFactory::create(['date' => $timestamp]);

        $expected = new \DateTimeImmutable('@'.$timestamp);
        self::assertEquals($expected, $item->date());
    }

    public function testDateHandlesDateTimeImmutable(): void
    {
        $date = new \DateTimeImmutable('2024-01-15 14:30:00');
        $item = ContentItemFactory::create(['date' => $date]);

        self::assertSame($date, $item->date());
    }

    public function testDateHandlesMutableDateTime(): void
    {
        $date = new \DateTime('2024-01-15 14:30:00');
        $item = ContentItemFactory::create(['date' => $date]);

        $expected = \DateTimeImmutable::createFromMutable($date);
        self::assertEquals($expected, $item->date());
    }

    public function testDateReturnsNullWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertNull($item->date());
    }

    public function testDateReturnsNullForInvalidFormat(): void
    {
        $item = ContentItemFactory::create(['date' => 'invalid-date']);

        self::assertNull($item->date());
    }

    public function testUpdatedDateParsesCorrectly(): void
    {
        $item = ContentItemFactory::create(['updated' => '2024-01-20']);

        $expected = new \DateTimeImmutable('2024-01-20 00:00:00');
        self::assertEquals($expected, $item->updatedDate());
    }

    public function testUpdatedDateReturnsNullWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertNull($item->updatedDate());
    }

    // ===== Tags =====

    public function testTagsReturnsArray(): void
    {
        $item = ContentItemFactory::create(['tags' => ['php', 'symfony', 'testing']]);

        self::assertSame(['php', 'symfony', 'testing'], $item->tags());
    }

    public function testTagsReturnsEmptyArrayWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertSame([], $item->tags());
    }

    public function testTagsHandlesNonArrayValue(): void
    {
        $item = ContentItemFactory::create(['tags' => 'php']);

        self::assertSame([], $item->tags());
    }

    public function testTagsHandlesEmptyArray(): void
    {
        $item = ContentItemFactory::create(['tags' => []]);

        self::assertSame([], $item->tags());
    }

    // ===== Related Slugs =====

    public function testRelatedSlugsReturnsArray(): void
    {
        $item = ContentItemFactory::create(['related' => ['post-1', 'post-2']]);

        self::assertSame(['post-1', 'post-2'], $item->relatedSlugs());
    }

    public function testRelatedSlugsReturnsEmptyArrayWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertSame([], $item->relatedSlugs());
    }

    // ===== Image =====

    public function testImageReturnsFrontMatterImage(): void
    {
        $item = ContentItemFactory::create(['image' => '/media/post/image.webp']);

        self::assertSame('/media/post/image.webp', $item->image());
    }

    public function testImageReturnsNullWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertNull($item->image());
    }

    public function testImageAltReturnsTitleWhenNotSet(): void
    {
        $item = ContentItemFactory::create(['title' => 'My Post']);

        self::assertSame('My Post', $item->imageAlt());
    }

    public function testImageAltReturnsFrontMatterValue(): void
    {
        $item = ContentItemFactory::create(['title' => 'My Post', 'image_alt' => 'Alt text']);

        self::assertSame('Alt text', $item->imageAlt());
    }

    // ===== Template =====

    public function testTemplateReturnsDefaultWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertSame('page/default', $item->template());
    }

    public function testTemplateReturnsFrontMatterValue(): void
    {
        $item = ContentItemFactory::create(['template' => 'blog/post']);

        self::assertSame('blog/post', $item->template());
    }

    // ===== State Flags =====

    public function testIsDraftReturnsFalseByDefault(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertFalse($item->isDraft());
    }

    public function testIsDraftReturnsTrueWhenSet(): void
    {
        $item = ContentItemFactory::draftPost();

        self::assertTrue($item->isDraft());
    }

    public function testIsDraftHandlesFalseString(): void
    {
        $item = ContentItemFactory::create(['draft' => 'false']);

        self::assertTrue($item->isDraft()); // Non-empty string is truthy
    }

    public function testIsScheduledReturnsFalseForPastDate(): void
    {
        $item = ContentItemFactory::create(['date' => '2024-01-01']);

        self::assertFalse($item->isScheduled());
    }

    public function testIsScheduledReturnsTrueForFutureDate(): void
    {
        $item = ContentItemFactory::scheduledPost();

        self::assertTrue($item->isScheduled());
    }

    public function testIsScheduledReturnsFalseWhenNoDate(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertFalse($item->isScheduled());
    }

    public function testIsPinnedReturnsFalseByDefault(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertFalse($item->isPinned());
    }

    public function testIsPinnedReturnsTrueForFuturePinnedDate(): void
    {
        $item = ContentItemFactory::pinnedPost();

        self::assertTrue($item->isPinned());
    }

    public function testIsPinnedReturnsFalseForPastPinnedDate(): void
    {
        $item = ContentItemFactory::create(['pinned' => '2020-01-01']);

        self::assertFalse($item->isPinned());
    }

    public function testIsPinnedReturnsTrueForToday(): void
    {
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $item = ContentItemFactory::create(['pinned' => $today]);

        self::assertTrue($item->isPinned());
    }

    public function testIsPinnedReturnsTrueForBooleanTrue(): void
    {
        $item = ContentItemFactory::create(['pinned' => true]);

        self::assertTrue($item->isPinned());
    }

    public function testIsPinnedHandlesInvalidDate(): void
    {
        $item = ContentItemFactory::create(['pinned' => 'invalid']);

        self::assertFalse($item->isPinned());
    }

    public function testIsDynamicReturnsFalseByDefault(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertFalse($item->isDynamic());
    }

    public function testIsDynamicReturnsTrueWhenSet(): void
    {
        $item = ContentItemFactory::create(['dynamic' => true]);

        self::assertTrue($item->isDynamic());
    }

    public function testHasTocReturnsFalseByDefault(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertFalse($item->hasToc());
    }

    public function testHasTocReturnsTrueWhenSet(): void
    {
        $item = ContentItemFactory::create(['toc' => true]);

        self::assertTrue($item->hasToc());
    }

    public function testIsFeaturedReturnsFalseByDefault(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertFalse($item->isFeatured());
    }

    public function testIsFeaturedReturnsTrueWhenSet(): void
    {
        $item = ContentItemFactory::featured();

        self::assertTrue($item->isFeatured());
    }

    public function testIsFeaturedRequiresTrueNotTruthy(): void
    {
        $item = ContentItemFactory::create(['featured' => 'yes']);

        self::assertFalse($item->isFeatured()); // Only true === true
    }

    // ===== Series =====

    public function testSeriesReturnsNullWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertNull($item->series());
    }

    public function testSeriesReturnsFrontMatterValue(): void
    {
        $item = ContentItemFactory::create(['series' => 'Symfony Tutorial']);

        self::assertSame('Symfony Tutorial', $item->series());
    }

    public function testSeriesOrderReturnsNullWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertNull($item->seriesOrder());
    }

    public function testSeriesOrderReturnsIntWhenSet(): void
    {
        $item = ContentItemFactory::create(['series_order' => '3']);

        self::assertSame(3, $item->seriesOrder());
    }

    public function testSeriesOrderHandlesNull(): void
    {
        $item = ContentItemFactory::create(['series_order' => null]);

        self::assertNull($item->seriesOrder());
    }

    // ===== Menu =====

    public function testMenuWeightReturnsDefault(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertSame(50, $item->menuWeight());
    }

    public function testMenuWeightReturnsFrontMatterValue(): void
    {
        $item = ContentItemFactory::create(['menu' => ['weight' => '10']]);

        self::assertSame(10, $item->menuWeight());
    }

    public function testMenuLabelReturnsFrontMatterLabel(): void
    {
        $item = ContentItemFactory::create(['title' => 'Full Title', 'menu' => ['label' => 'Short']]);

        self::assertSame('Short', $item->menuLabel());
    }

    public function testMenuLabelFallsBackToTitle(): void
    {
        $item = ContentItemFactory::create(['title' => 'My Page']);

        self::assertSame('My Page', $item->menuLabel());
    }

    public function testMenuLabelReturnsEmptyStringWhenNeitherSet(): void
    {
        $item = ContentItemFactory::create(['title' => '']);

        self::assertSame('', $item->menuLabel());
    }

    // ===== Index =====

    public function testIsIndexReturnsFalseByDefault(): void
    {
        $item = ContentItemFactory::create();

        self::assertFalse($item->isIndex());
    }

    public function testIsIndexReturnsTrueForIndexItem(): void
    {
        $item = ContentItemFactory::indexItem();

        self::assertTrue($item->isIndex());
    }

    // ===== Category =====

    public function testCategoryReturnsNullWhenNotSet(): void
    {
        $item = ContentItemFactory::create([]);

        self::assertNull($item->category());
    }

    public function testCategoryReturnsFrontMatterValue(): void
    {
        $item = ContentItemFactory::create(['category' => 'tutorials']);

        self::assertSame('tutorials', $item->category());
    }

    // ===== URL =====

    public function testUrlReturnsEmptyStringByDefault(): void
    {
        $item = ContentItemFactory::create();

        self::assertSame('', $item->url());
    }

    // ===== Reading Time =====

    public function testReadingTimeCalculatesCorrectly(): void
    {
        $item = ContentItemFactory::create([], '<p>Word1 word2 word3</p>');

        self::assertSame(1, $item->readingTime());
    }

    public function testReadingTimeMinimumIsOne(): void
    {
        $item = ContentItemFactory::create([], '<p>.</p>');

        self::assertSame(1, $item->readingTime());
    }

    public function testReadingTimeWithCustomWordsPerMinute(): void
    {
        $item = ContentItemFactory::create([], '<p>'.str_repeat('word ', 400).'</p>');

        self::assertSame(2, $item->readingTime(200)); // 400 words / 200 wpm = 2
    }

    // ===== Word Count =====

    public function testWordCountCountsWords(): void
    {
        $item = ContentItemFactory::create([], '<p>Hello world this is a test</p>');

        self::assertSame(6, $item->wordCount());
    }

    public function testWordCountIgnoresHtmlTags(): void
    {
        $item = ContentItemFactory::create([], '<strong>Hello</strong> <em>world</em>');

        self::assertSame(2, $item->wordCount());
    }

    public function testWordCountReturnsZeroForEmpty(): void
    {
        $item = ContentItemFactory::create([], '');

        self::assertSame(0, $item->wordCount());
    }

    // ===== Excerpt =====

    public function testExcerptReturnsFullTextWhenShort(): void
    {
        $item = ContentItemFactory::create([], '<p>Short text</p>');

        self::assertSame('Short text', $item->excerpt());
    }

    public function testExcerptTruncatesLongText(): void
    {
        $longText = 'This is a very long text that should be truncated';
        $item = ContentItemFactory::create([], '<p>'.$longText.'</p>');

        $excerpt = $item->excerpt(20);
        self::assertStringEndsWith('…', $excerpt);
        self::assertLessThanOrEqual(23, mb_strlen($excerpt)); // 20 + '…'
    }

    public function testExcerptRemovesHtmlTags(): void
    {
        $item = ContentItemFactory::create([], '<p>Hello <strong>world</strong></p>');

        self::assertSame('Hello world', $item->excerpt());
    }

    public function testExcerptNormalizesWhitespace(): void
    {
        $item = ContentItemFactory::create([], '<p>Hello   world   test</p>');

        self::assertSame('Hello world test', $item->excerpt());
    }

    public function testExcerptHandlesMultipleParagraphs(): void
    {
        $item = ContentItemFactory::create([], '<p>First</p><p>Second</p>');

        self::assertSame('First Second', $item->excerpt());
    }

    public function testExcerptWithCustomLength(): void
    {
        $item = ContentItemFactory::create([], '<p>'.str_repeat('word ', 50).'</p>');

        $excerpt = $item->excerpt(100);
        self::assertLessThanOrEqual(103, mb_strlen($excerpt)); // 100 + '…'
    }
}
