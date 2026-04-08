<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service\Content;

use NotACms\Content\ContentTree;
use NotACms\Service\Content\RelatedPostsService;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class RelatedPostsServiceTest extends TestCase
{
    private RelatedPostsService $service;

    protected function setUp(): void
    {
        $this->service = new RelatedPostsService();
    }

    // ===== Explicit Related Posts =====

    public function testReturnsExplicitlyRelatedPosts(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['related' => ['post-1', 'post-2']], 'current', '/current');
        $related1 = ContentItemFactory::publishedPost(['slug' => 'post-1'], 'post-1', '/post-1');
        $related2 = ContentItemFactory::publishedPost(['slug' => 'post-2'], 'post-2', '/post-2');

        $tree->addPost($current);
        $tree->addPost($related1);
        $tree->addPost($related2);

        $result = $this->service->getRelatedPosts($tree, $current);

        self::assertSame([$related1, $related2], $result);
    }

    public function testIgnoresNonexistentSlugs(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['related' => ['nonexistent']], 'current', '/current');
        $other = ContentItemFactory::publishedPost(['slug' => 'actual-post'], 'actual', '/actual');

        $tree->addPost($current);
        $tree->addPost($other);

        $result = $this->service->getRelatedPosts($tree, $current);

        // No explicit related posts, but falls back to any available post
        self::assertSame([$other], $result);
    }

    public function testRespectsLimitOnExplicitRelated(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['related' => ['post-1', 'post-2', 'post-3']], 'current', '/current');
        $related1 = ContentItemFactory::publishedPost(['slug' => 'post-1'], 'p1', '/p1');
        $related2 = ContentItemFactory::publishedPost(['slug' => 'post-2'], 'p2', '/p2');
        $related3 = ContentItemFactory::publishedPost(['slug' => 'post-3'], 'p3', '/p3');

        $tree->addPost($current);
        $tree->addPost($related1);
        $tree->addPost($related2);
        $tree->addPost($related3);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 2);

        self::assertSame([$related1, $related2], $result);
    }

    // ===== Scoring: Category =====

    public function testScoresBySameCategory(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::withCategory('tutorials', ['date' => '2024-01-01'], 'current', '/current');
        $sameCategory = ContentItemFactory::withCategory('tutorials', ['date' => '2024-01-02'], 'same', '/same');
        $differentCategory = ContentItemFactory::withCategory('projects', ['date' => '2024-01-03'], 'diff', '/diff');

        $tree->addPost($current);
        $tree->addPost($sameCategory);
        $tree->addPost($differentCategory);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 1);

        self::assertSame([$sameCategory], $result);
    }

    public function testCategoryScoreIsTwoPoints(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::withCategory('tutorials', ['tags' => ['php'], 'date' => '2024-01-01'], 'current', '/current');
        $sameCategory = ContentItemFactory::withCategory('tutorials', ['date' => '2024-01-02'], 'same', '/same');
        $oneSharedTag = ContentItemFactory::withCategory('other', ['tags' => ['php'], 'date' => '2024-01-03'], 'tag', '/tag');

        $tree->addPost($current);
        $tree->addPost($sameCategory);
        $tree->addPost($oneSharedTag);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 1);

        // Category (2) > tag (1)
        self::assertSame([$sameCategory], $result);
    }

    // ===== Scoring: Tags =====

    public function testScoresBySharedTags(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::create(['tags' => ['php', 'symfony'], 'date' => '2024-01-01'], '', 'en', 'current', null, '/current');
        $twoSharedTags = ContentItemFactory::create(['tags' => ['php', 'symfony'], 'date' => '2024-01-02'], '', 'en', 'two', null, '/two');
        $oneSharedTag = ContentItemFactory::create(['tags' => ['php', 'laravel'], 'date' => '2024-01-03'], '', 'en', 'one', null, '/one');

        $tree->addPost($current);
        $tree->addPost($twoSharedTags);
        $tree->addPost($oneSharedTag);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 1);

        self::assertSame([$twoSharedTags], $result);
    }

    public function testDoesNotScorePostsWithNoMatches(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::create(['category' => 'tutorials', 'tags' => ['php'], 'date' => '2024-01-01'], '', 'en', 'current', null, '/current');
        $noMatch = ContentItemFactory::create(['category' => 'projects', 'tags' => ['javascript'], 'date' => '2024-01-02'], '', 'en', 'no-match', null, '/no-match');

        $tree->addPost($current);
        $tree->addPost($noMatch);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 1);

        // Fallback to any post when no matches
        self::assertSame([$noMatch], $result);
    }

    // ===== Combined Scoring =====

    public function testScoresCombinationOfCategoryAndTags(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::create(['category' => 'tutorials', 'tags' => ['php'], 'date' => '2024-01-01'], '', 'en', 'current', null, '/current');
        $both = ContentItemFactory::create(['category' => 'tutorials', 'tags' => ['php'], 'date' => '2024-01-02'], '', 'en', 'both', null, '/both');
        $categoryOnly = ContentItemFactory::create(['category' => 'tutorials', 'tags' => ['laravel'], 'date' => '2024-01-03'], '', 'en', 'cat-only', null, '/cat-only');

        $tree->addPost($current);
        $tree->addPost($both);
        $tree->addPost($categoryOnly);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 1);

        // Both (3) > category only (2)
        self::assertSame([$both], $result);
    }

    public function testScoresMultipleTagsCorrectly(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::create(['tags' => ['php', 'symfony', 'doctrine'], 'date' => '2024-01-01'], '', 'en', 'current', null, '/current');
        $threeTags = ContentItemFactory::create(['tags' => ['php', 'symfony', 'doctrine', 'twig'], 'date' => '2024-01-02'], '', 'en', 'three', null, '/three');
        $twoTags = ContentItemFactory::create(['tags' => ['php', 'symfony'], 'date' => '2024-01-03'], '', 'en', 'two', null, '/two');

        $tree->addPost($current);
        $tree->addPost($threeTags);
        $tree->addPost($twoTags);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 1);

        // 3 shared tags > 2 shared tags
        self::assertSame([$threeTags], $result);
    }

    // ===== Fallback =====

    public function testFillsRemainingLimitWithAnyPosts(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['date' => '2024-01-01'], 'current', '/current');
        $other = ContentItemFactory::publishedPost(['date' => '2024-01-02'], 'other', '/other');

        $tree->addPost($current);
        $tree->addPost($other);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 3);

        self::assertSame([$other], $result);
    }

    public function testExcludesCurrentPostFromResults(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['category' => 'tutorials', 'date' => '2024-01-01'], 'current', '/current');

        $tree->addPost($current);

        $result = $this->service->getRelatedPosts($tree, $current);

        self::assertSame([], $result);
    }

    public function testExcludesExplicitlyRelatedFromScoring(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::withCategory('tutorials', ['related' => ['explicit'], 'date' => '2024-01-01'], 'current', '/current');
        $explicit = ContentItemFactory::publishedPost(['slug' => 'explicit', 'category' => 'tutorials', 'date' => '2024-01-02'], 'explicit', '/explicit');
        $other = ContentItemFactory::withCategory('tutorials', ['date' => '2024-01-03'], 'other', '/other');

        $tree->addPost($current);
        $tree->addPost($explicit);
        $tree->addPost($other);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 2);

        self::assertSame([$explicit, $other], $result);
    }

    // ===== Edge Cases =====

    public function testHandlesEmptyTree(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost([], 'current', '/current');

        $tree->addPost($current);

        $result = $this->service->getRelatedPosts($tree, $current);

        self::assertSame([], $result);
    }

    public function testHandlesZeroLimit(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['related' => ['other']], 'current', '/current');
        $other = ContentItemFactory::publishedPost(['slug' => 'other'], 'other', '/other');

        $tree->addPost($current);
        $tree->addPost($other);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 0);

        self::assertSame([], $result);
    }

    public function testHandlesDefaultLimit(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['category' => 'tutorials', 'date' => '2024-01-01'], 'current', '/current');
        $posts = [];
        for ($i = 0; $i < 5; $i++) {
            $posts[] = ContentItemFactory::withCategory('tutorials', ['date' => "2024-01-0" . ($i + 2)], "post-{$i}", "/post-{$i}");
        }

        $tree->addPost($current);
        foreach ($posts as $post) {
            $tree->addPost($post);
        }

        $result = $this->service->getRelatedPosts($tree, $current);

        self::assertCount(3, $result);
    }

    public function testTieBreakerPreservesOrder(): void
    {
        $tree = new ContentTree();
        $current = ContentItemFactory::publishedPost(['date' => '2024-01-01'], 'current', '/current');
        $first = ContentItemFactory::publishedPost(['date' => '2024-01-03'], 'first', '/first');
        $second = ContentItemFactory::publishedPost(['date' => '2024-01-02'], 'second', '/second');

        $tree->addPost($current);
        $tree->addPost($first);
        $tree->addPost($second);

        $result = $this->service->getRelatedPosts($tree, $current, limit: 2);

        // Posts are sorted by date descending in getAllPosts(), so newest comes first
        // in the fallback iteration
        self::assertSame([$first, $second], $result);
    }
}
