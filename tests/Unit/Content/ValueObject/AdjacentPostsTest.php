<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ContentItem;
use NotACms\Content\ValueObject\AdjacentPosts;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class AdjacentPostsTest extends TestCase
{
    public function testConstruction(): void
    {
        $prev = ContentItemFactory::publishedPost([], 'prev', '/prev');
        $next = ContentItemFactory::publishedPost([], 'next', '/next');

        $adjacent = new AdjacentPosts(prev: $prev, next: $next);

        self::assertSame($prev, $adjacent->prev);
        self::assertSame($next, $adjacent->next);
    }

    public function testConstructionWithNulls(): void
    {
        $adjacent = new AdjacentPosts(prev: null, next: null);

        self::assertNull($adjacent->prev);
        self::assertNull($adjacent->next);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(AdjacentPosts::class);

        self::assertTrue($reflection->isReadonly());
    }
}
