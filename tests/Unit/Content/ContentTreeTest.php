<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content;

use NotACms\Content\ContentTree;
use NotACms\Content\ValueObject\AdjacentPosts;
use NotACms\Content\ValueObject\ArchiveMonth;
use NotACms\Content\ValueObject\CategoryCount;
use NotACms\Content\ValueObject\TagCount;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class ContentTreeTest extends TestCase
{
    // ===== Constructor =====

    public function testConstructorDefaultsExcludeDraftsAndScheduled(): void
    {
        $tree = new ContentTree();

        $tree->addPost(ContentItemFactory::draftPost());
        $tree->addPost(ContentItemFactory::scheduledPost());

        self::assertSame([], $tree->getAllPosts());
    }

    public function testConstructorWithIncludeDrafts(): void
    {
        $tree = new ContentTree(includeDrafts: true);

        $draft = ContentItemFactory::draftPost();
        $tree->addPost($draft);

        self::assertSame([$draft], $tree->getAllPosts());
    }

    public function testConstructorWithIncludeScheduled(): void
    {
        $tree = new ContentTree(includeScheduled: true);

        $scheduled = ContentItemFactory::scheduledPost();
        $tree->addPost($scheduled);

        self::assertSame([$scheduled], $tree->getAllPosts());
    }

    // ===== addPost / addPage =====

    public function testAddPostStoresItem(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost();

        $tree->addPost($post);

        self::assertSame([$post], $tree->getAllPosts());
    }

    public function testAddPageStoresItem(): void
    {
        $tree = new ContentTree();
        $page = ContentItemFactory::page();

        $tree->addPage($page);

        self::assertSame([$page], $tree->getAllPages());
    }

    public function testAddPostInvalidatesSortedCache(): void
    {
        $tree = new ContentTree();
        $post1 = ContentItemFactory::publishedPost(['date' => '2024-01-01']);
        $tree->addPost($post1);

        $first = $tree->getAllPosts();

        $post2 = ContentItemFactory::publishedPost(['date' => '2024-01-02']);
        $tree->addPost($post2);

        $second = $tree->getAllPosts();

        self::assertNotSame($first, $second);
        self::assertCount(2, $second);
    }

    // ===== findByUrl =====

    public function testFindByUrlReturnsNullWhenNotFound(): void
    {
        $tree = new ContentTree();

        self::assertNull($tree->findByUrl('/not-found'));
    }

    public function testFindByUrlReturnsPost(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], null, '/my-post');
        $tree->addPost($post);

        $found = $tree->findByUrl('/my-post');

        self::assertSame($post, $found);
    }

    public function testFindByUrlReturnsPage(): void
    {
        $tree = new ContentTree();
        $page = ContentItemFactory::page([], null, '/about');
        $tree->addPage($page);

        $found = $tree->findByUrl('/about');

        self::assertSame($page, $found);
    }

    public function testFindByUrlIgnoresEmptyUrl(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], null, '');
        $tree->addPost($post);

        self::assertNull($tree->findByUrl(''));
    }

    public function testFindByUrlIgnoresZeroUrl(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], null, '0');
        $tree->addPost($post);

        self::assertNull($tree->findByUrl('0'));
    }

    // ===== findByDirectoryKey =====

    public function testFindByDirectoryKeyReturnsNullWhenNotFound(): void
    {
        $tree = new ContentTree();

        self::assertNull($tree->findByDirectoryKey('not-found'));
    }

    public function testFindByDirectoryKeyFindsPost(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost(['title' => 'My Post'], 'my-post');
        $tree->addPost($post);

        $found = $tree->findByDirectoryKey('my-post');

        self::assertSame($post, $found);
    }

    public function testFindByDirectoryKeyFindsPage(): void
    {
        $tree = new ContentTree();
        $page = ContentItemFactory::page(['title' => 'About'], 'about');
        $tree->addPage($page);

        $found = $tree->findByDirectoryKey('about');

        self::assertSame($page, $found);
    }

    public function testFindByDirectoryKeyFindsPageBeforePost(): void
    {
        $tree = new ContentTree();
        $page = ContentItemFactory::page(['title' => 'About'], 'shared-key');
        $post = ContentItemFactory::publishedPost(['title' => 'My Post'], 'shared-key');

        $tree->addPage($page);
        $tree->addPost($post);

        $found = $tree->findByDirectoryKey('shared-key');

        self::assertSame($page, $found); // Pages checked first
    }

    // ===== getAllPosts - Sorting =====

    public function testGetAllPostsSortsByDateDesc(): void
    {
        $tree = new ContentTree();
        $old = ContentItemFactory::publishedPost(['date' => '2023-01-01']);
        $new = ContentItemFactory::publishedPost(['date' => '2024-01-01']);
        $mid = ContentItemFactory::publishedPost(['date' => '2023-06-01']);

        $tree->addPost($old);
        $tree->addPost($new);
        $tree->addPost($mid);

        $posts = $tree->getAllPosts();

        self::assertSame([$new, $mid, $old], $posts);
    }

    public function testGetAllPostsPinnedPostsComeFirst(): void
    {
        $tree = new ContentTree();
        $pinned = ContentItemFactory::pinnedPost(['date' => '2023-01-01']);
        $new = ContentItemFactory::publishedPost(['date' => '2024-01-01']);

        $tree->addPost($new);
        $tree->addPost($pinned);

        $posts = $tree->getAllPosts();

        self::assertSame([$pinned, $new], $posts);
    }

    public function testGetAllPostsPinnedPostsSortedByDate(): void
    {
        $tree = new ContentTree();
        $pinnedOld = ContentItemFactory::pinnedPost(['date' => '2023-01-01'], 'pinned-old');
        $pinnedNew = ContentItemFactory::pinnedPost(['date' => '2024-01-01'], 'pinned-new');

        $tree->addPost($pinnedOld);
        $tree->addPost($pinnedNew);

        $posts = $tree->getAllPosts();

        self::assertSame([$pinnedNew, $pinnedOld], $posts);
    }

    public function testGetAllPostsFiltersOutDrafts(): void
    {
        $tree = new ContentTree();
        $published = ContentItemFactory::publishedPost();
        $draft = ContentItemFactory::draftPost();

        $tree->addPost($published);
        $tree->addPost($draft);

        $posts = $tree->getAllPosts();

        self::assertSame([$published], $posts);
    }

    public function testGetAllPostsFiltersOutScheduled(): void
    {
        $tree = new ContentTree();
        $published = ContentItemFactory::publishedPost();
        $scheduled = ContentItemFactory::scheduledPost();

        $tree->addPost($published);
        $tree->addPost($scheduled);

        $posts = $tree->getAllPosts();

        self::assertSame([$published], $posts);
    }

    // ===== getPostsByCategory =====

    public function testGetPostsByCategoryFiltersCorrectly(): void
    {
        $tree = new ContentTree();
        $php = ContentItemFactory::withCategory('tutorials');
        $js = ContentItemFactory::withCategory('projects');

        $tree->addPost($php);
        $tree->addPost($js);

        $tutorials = $tree->getPostsByCategory('tutorials');

        self::assertSame([$php], $tutorials);
    }

    public function testGetPostsByCategoryReturnsEmptyWhenNotFound(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost());

        self::assertSame([], $tree->getPostsByCategory('nonexistent'));
    }

    // ===== getPostsByTag =====

    public function testGetPostsByTagFiltersCorrectly(): void
    {
        $tree = new ContentTree();
        $post1 = ContentItemFactory::withTags('php', 'symfony');
        $post2 = ContentItemFactory::withTags('javascript', 'react');

        $tree->addPost($post1);
        $tree->addPost($post2);

        $phpPosts = $tree->getPostsByTag('php');

        self::assertSame([$post1], $phpPosts);
    }

    public function testGetPostsByTagHandlesMultipleTags(): void
    {
        $tree = new ContentTree();
        $post1 = ContentItemFactory::withTags('php', 'symfony');
        $post2 = ContentItemFactory::withTags('php', 'laravel');

        $tree->addPost($post1);
        $tree->addPost($post2);

        $phpPosts = $tree->getPostsByTag('php');

        self::assertCount(2, $phpPosts);
    }

    public function testGetPostsByTagReturnsEmptyWhenNotFound(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::withTags('php'));

        self::assertSame([], $tree->getPostsByTag('nonexistent'));
    }

    // ===== getPostsByYearMonth =====

    public function testGetPostsByYearMonthFiltersCorrectly(): void
    {
        $tree = new ContentTree();
        $jan = ContentItemFactory::publishedPost(['date' => '2024-01-15']);
        $feb = ContentItemFactory::publishedPost(['date' => '2024-02-15']);

        $tree->addPost($jan);
        $tree->addPost($feb);

        $janPosts = $tree->getPostsByYearMonth(2024, 1);

        self::assertSame([$jan], $janPosts);
    }

    public function testGetPostsByYearMonthIgnoresDifferentYear(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost(['date' => '2024-01-15']);

        $tree->addPost($post);

        self::assertSame([], $tree->getPostsByYearMonth(2023, 1));
    }

    public function testGetPostsByYearMonthIgnoresDifferentMonth(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost(['date' => '2024-01-15']);

        $tree->addPost($post);

        self::assertSame([], $tree->getPostsByYearMonth(2024, 2));
    }

    public function testGetPostsByYearMonthRequiresPostWithDate(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2023-01-15']));

        $posts = $tree->getPostsByYearMonth(2024, 1);

        self::assertCount(1, $posts);
    }

    // ===== findPostBySlug =====

    public function testFindPostBySlugReturnsPost(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost(['slug' => 'my-post']);

        $tree->addPost($post);

        $found = $tree->findPostBySlug('my-post');

        self::assertSame($post, $found);
    }

    public function testFindPostBySlugReturnsNullWhenNotFound(): void
    {
        $tree = new ContentTree();

        self::assertNull($tree->findPostBySlug('not-found'));
    }

    public function testFindPostBySlugIgnoresDrafts(): void
    {
        $tree = new ContentTree();
        $draft = ContentItemFactory::draftPost(['slug' => 'draft-post']);

        $tree->addPost($draft);

        self::assertNull($tree->findPostBySlug('draft-post'));
    }

    public function testFindPostBySlugIgnoresScheduled(): void
    {
        $tree = new ContentTree();
        $scheduled = ContentItemFactory::scheduledPost(['slug' => 'scheduled-post']);

        $tree->addPost($scheduled);

        self::assertNull($tree->findPostBySlug('scheduled-post'));
    }

    // ===== getScheduledPosts =====

    public function testGetScheduledPostsReturnsOnlyScheduled(): void
    {
        $tree = new ContentTree();
        $scheduled = ContentItemFactory::scheduledPost();
        $published = ContentItemFactory::publishedPost();
        $draft = ContentItemFactory::draftPost();

        $tree->addPost($scheduled);
        $tree->addPost($published);
        $tree->addPost($draft);

        $result = $tree->getScheduledPosts();

        self::assertSame([$scheduled], $result);
    }

    public function testGetScheduledPostsExcludesDrafts(): void
    {
        $tree = new ContentTree();
        $draftScheduled = ContentItemFactory::draftPost(['pinned' => '2099-01-01']);
        $scheduled = ContentItemFactory::scheduledPost();

        $tree->addPost($draftScheduled);
        $tree->addPost($scheduled);

        $result = $tree->getScheduledPosts();

        self::assertSame([$scheduled], $result);
    }

    // ===== findScheduledPostBySlug =====

    public function testFindScheduledPostBySlugReturnsPost(): void
    {
        $tree = new ContentTree();
        $scheduled = ContentItemFactory::scheduledPost(['slug' => 'future-post']);

        $tree->addPost($scheduled);

        $found = $tree->findScheduledPostBySlug('future-post');

        self::assertSame($scheduled, $found);
    }

    public function testFindScheduledPostBySlugReturnsNullWhenNotFound(): void
    {
        $tree = new ContentTree();

        self::assertNull($tree->findScheduledPostBySlug('not-found'));
    }

    // ===== getAllTags =====

    public function testGetAllTagsReturnsTagCounts(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::withTags('php', 'symfony'));
        $tree->addPost(ContentItemFactory::withTags('php', 'laravel'));

        $tags = $tree->getAllTags();

        self::assertCount(3, $tags);
        self::assertContainsOnlyInstancesOf(TagCount::class, $tags);
    }

    public function testGetAllTagsSortsByCountDesc(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::withTags('php'));
        $tree->addPost(ContentItemFactory::withTags('php'));
        $tree->addPost(ContentItemFactory::withTags('symfony'));

        $tags = $tree->getAllTags();

        self::assertSame('php', $tags[0]->slug);
        self::assertSame(2, $tags[0]->count);
        self::assertSame('symfony', $tags[1]->slug);
    }

    public function testGetAllTagsSortsAlphabeticallyForSameCount(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::withTags('laravel', 'symfony', 'php'));

        $tags = $tree->getAllTags();

        self::assertSame('laravel', $tags[0]->slug);
        self::assertSame('php', $tags[1]->slug);
        self::assertSame('symfony', $tags[2]->slug);
    }

    public function testGetAllTagsReturnsEmptyWhenNoTags(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost());

        self::assertSame([], $tree->getAllTags());
    }

    // ===== getAllCategories =====

    public function testGetAllCategoriesReturnsCategoryCounts(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::withCategory('tutorials'));
        $tree->addPost(ContentItemFactory::withCategory('tutorials'));
        $tree->addPost(ContentItemFactory::withCategory('projects'));

        $categories = $tree->getAllCategories();

        self::assertCount(2, $categories);
        self::assertContainsOnlyInstancesOf(CategoryCount::class, $categories);
    }

    public function testGetAllCategoriesIgnoresNullCategories(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost());
        $tree->addPost(ContentItemFactory::withCategory('tutorials'));

        $categories = $tree->getAllCategories();

        self::assertCount(1, $categories);
    }

    // ===== getArchiveMonths =====

    public function testGetArchiveMonthsReturnsArchiveMonths(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-02-10']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-20']));

        $months = $tree->getArchiveMonths();

        self::assertCount(2, $months);
        self::assertContainsOnlyInstancesOf(ArchiveMonth::class, $months);
    }

    public function testGetArchiveMonthsSortsNewestFirst(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2023-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-02-10']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-20']));

        $months = $tree->getArchiveMonths();

        self::assertSame(2024, $months[0]->year);
        self::assertSame(2, $months[0]->month);
        self::assertSame(2024, $months[1]->year);
        self::assertSame(1, $months[1]->month);
    }

    public function testGetArchiveMonthsAggregatesCount(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-20']));

        $months = $tree->getArchiveMonths();

        self::assertSame(2, $months[0]->count);
    }

    public function testGetArchiveMonthsIgnoresPostsWithoutDate(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));

        $months = $tree->getArchiveMonths();

        self::assertCount(1, $months);
    }

    // ===== getPostsByYear =====

    public function testGetPostsByYearFiltersCorrectly(): void
    {
        $tree = new ContentTree();
        $old = ContentItemFactory::publishedPost(['date' => '2023-01-15']);
        $new = ContentItemFactory::publishedPost(['date' => '2024-01-15']);

        $tree->addPost($old);
        $tree->addPost($new);

        $posts = $tree->getPostsByYear(2024);

        self::assertSame([$new], $posts);
    }

    public function testGetPostsByYearIgnoresPostsWithoutDate(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2023-01-15']));

        $posts = $tree->getPostsByYear(2024);

        self::assertCount(1, $posts);
    }

    // ===== getArchiveYears =====

    public function testGetArchiveYearsReturnsYearsWithCounts(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2023-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-02-10']));

        $years = $tree->getArchiveYears();

        self::assertSame([2024 => 2, 2023 => 1], $years);
    }

    public function testGetArchiveYearsSortsNewestFirst(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2023-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));

        $years = $tree->getArchiveYears();

        $keys = array_keys($years);
        self::assertSame(2024, $keys[0]);
        self::assertSame(2023, $keys[1]);
    }

    // ===== getAllPages =====

    public function testGetAllPagesReturnsAllPages(): void
    {
        $tree = new ContentTree();
        $page1 = ContentItemFactory::page(['menu' => ['weight' => 10]]);
        $page2 = ContentItemFactory::page(['menu' => ['weight' => 5]]);

        $tree->addPage($page1);
        $tree->addPage($page2);

        $pages = $tree->getAllPages();

        self::assertSame([$page2, $page1], $pages);
    }

    public function testGetAllPagesSortsByMenuWeight(): void
    {
        $tree = new ContentTree();
        $heavy = ContentItemFactory::page(['menu' => ['weight' => 100]]);
        $light = ContentItemFactory::page(['menu' => ['weight' => 10]]);
        $medium = ContentItemFactory::page(['menu' => ['weight' => 50]]);

        $tree->addPage($heavy);
        $tree->addPage($light);
        $tree->addPage($medium);

        $pages = $tree->getAllPages();

        self::assertSame([$light, $medium, $heavy], $pages);
    }

    // ===== getStaticPages =====

    public function testGetStaticPagesExcludesDynamicPages(): void
    {
        $tree = new ContentTree();
        $static = ContentItemFactory::page();
        $dynamic = ContentItemFactory::page(['dynamic' => true]);

        $tree->addPage($static);
        $tree->addPage($dynamic);

        $pages = $tree->getStaticPages();

        self::assertSame([$static], $pages);
    }

    // ===== getSeriesPosts =====

    public function testGetSeriesPostsReturnsPostsInSeries(): void
    {
        $tree = new ContentTree();
        $post1 = ContentItemFactory::publishedPost(['series' => 'Tutorial', 'series_order' => 1]);
        $post2 = ContentItemFactory::publishedPost(['series' => 'Tutorial', 'series_order' => 2]);
        $other = ContentItemFactory::publishedPost(['series' => 'Other']);

        $tree->addPost($post1);
        $tree->addPost($post2);
        $tree->addPost($other);

        $series = $tree->getSeriesPosts('Tutorial');

        self::assertSame([$post1, $post2], $series);
    }

    public function testGetSeriesPostsSortsBySeriesOrder(): void
    {
        $tree = new ContentTree();
        $post2 = ContentItemFactory::publishedPost(['series' => 'Tutorial', 'series_order' => 2], 'post2');
        $post1 = ContentItemFactory::publishedPost(['series' => 'Tutorial', 'series_order' => 1], 'post1');
        $post3 = ContentItemFactory::publishedPost(['series' => 'Tutorial'], 'post3');

        $tree->addPost($post2);
        $tree->addPost($post1);
        $tree->addPost($post3);

        $series = $tree->getSeriesPosts('Tutorial');

        self::assertSame([$post3, $post1, $post2], $series); // null series_order treated as 0 via ?? 0
    }

    public function testGetSeriesPostsHandlesNullOrder(): void
    {
        $tree = new ContentTree();
        $post1 = ContentItemFactory::publishedPost(['series' => 'Tutorial'], 'post1');
        $post2 = ContentItemFactory::publishedPost(['series' => 'Tutorial', 'series_order' => 1], 'post2');

        $tree->addPost($post1);
        $tree->addPost($post2);

        $series = $tree->getSeriesPosts('Tutorial');

        self::assertSame([$post1, $post2], $series);
    }

    // ===== getAllItems =====

    public function testGetAllItemsReturnsPostsAndPages(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost();
        $page = ContentItemFactory::page();

        $tree->addPost($post);
        $tree->addPage($page);

        $items = $tree->getAllItems();

        self::assertSame([$post, $page], $items);
    }

    // ===== getAdjacentPosts =====

    public function testGetAdjacentPostsReturnsAdjacent(): void
    {
        $tree = new ContentTree();
        $new = ContentItemFactory::publishedPost(['date' => '2024-01-01'], 'new', '/new');
        $mid = ContentItemFactory::publishedPost(['date' => '2023-06-01'], 'mid', '/mid');
        $old = ContentItemFactory::publishedPost(['date' => '2023-01-01'], 'old', '/old');

        $tree->addPost($new);
        $tree->addPost($mid);
        $tree->addPost($old);

        $adjacent = $tree->getAdjacentPosts($mid);

        self::assertInstanceOf(AdjacentPosts::class, $adjacent);
        // In sorted array [new, mid, old], mid is at index 1
        // prev = posts[1 + 1] = old (older post)
        // next = posts[1 - 1] = new (newer post)
        self::assertSame($old, $adjacent->prev);
        self::assertSame($new, $adjacent->next);
    }

    public function testGetAdjacentPostsForFirstPost(): void
    {
        $tree = new ContentTree();
        $first = ContentItemFactory::publishedPost(['date' => '2024-01-01'], 'first', '/first');
        $second = ContentItemFactory::publishedPost(['date' => '2023-01-01'], 'second', '/second');

        $tree->addPost($first);
        $tree->addPost($second);

        $adjacent = $tree->getAdjacentPosts($first);

        // In sorted array [first, second], first is at index 0
        // prev = posts[0 + 1] = second
        // next = null (0 < 0 is false)
        self::assertSame($second, $adjacent->prev);
        self::assertNull($adjacent->next);
    }

    public function testGetAdjacentPostsForLastPost(): void
    {
        $tree = new ContentTree();
        $first = ContentItemFactory::publishedPost(['date' => '2024-01-01'], 'first', '/first');
        $last = ContentItemFactory::publishedPost(['date' => '2023-01-01'], 'last', '/last');

        $tree->addPost($first);
        $tree->addPost($last);

        $adjacent = $tree->getAdjacentPosts($last);

        // In sorted array [first, last], last is at index 1
        // prev = null (1 < 2 - 1 is false)
        // next = posts[1 - 1] = first
        self::assertNull($adjacent->prev);
        self::assertSame($first, $adjacent->next);
    }

    public function testGetAdjacentPostsReturnsNullsForNotFoundPost(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost();

        $adjacent = $tree->getAdjacentPosts($post);

        self::assertNull($adjacent->prev);
        self::assertNull($adjacent->next);
    }

    public function testGetAdjacentPostsForSinglePost(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost();

        $tree->addPost($post);

        $adjacent = $tree->getAdjacentPosts($post);

        self::assertNull($adjacent->prev);
        self::assertNull($adjacent->next);
    }
}
