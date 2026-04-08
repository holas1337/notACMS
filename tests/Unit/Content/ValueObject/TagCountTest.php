<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\TagCount;
use PHPUnit\Framework\TestCase;

final class TagCountTest extends TestCase
{
    public function testConstruction(): void
    {
        $tag = new TagCount('php', 100);

        self::assertSame('php', $tag->slug);
        self::assertSame(100, $tag->count);
    }

    public function testConstructionWithZeroCount(): void
    {
        $tag = new TagCount('unused', 0);

        self::assertSame('unused', $tag->slug);
        self::assertSame(0, $tag->count);
    }
}
