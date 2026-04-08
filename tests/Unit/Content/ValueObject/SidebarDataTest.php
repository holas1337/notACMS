<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ContentItem;
use NotACms\Content\ValueObject\SidebarData;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class SidebarDataTest extends TestCase
{
    public function testConstruction(): void
    {
        $recentPosts = [ContentItemFactory::publishedPost()];
        $categories = [['slug' => 'tutorials', 'count' => 5]];
        $tags = [['slug' => 'php', 'count' => 10]];
        $archiveMonths = [['year' => 2024, 'month' => 1, 'count' => 3]];

        $sidebar = new SidebarData(
            recentPosts: $recentPosts,
            categories: $categories,
            tags: $tags,
            archiveMonths: $archiveMonths,
        );

        self::assertSame($recentPosts, $sidebar->recentPosts);
        self::assertSame($categories, $sidebar->categories);
        self::assertSame($tags, $sidebar->tags);
        self::assertSame($archiveMonths, $sidebar->archiveMonths);
    }

    public function testConstructionWithEmptyArrays(): void
    {
        $sidebar = new SidebarData([], [], [], []);

        self::assertSame([], $sidebar->recentPosts);
        self::assertSame([], $sidebar->categories);
        self::assertSame([], $sidebar->tags);
        self::assertSame([], $sidebar->archiveMonths);
    }
}
