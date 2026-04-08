<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\CategoryCount;
use PHPUnit\Framework\TestCase;

final class CategoryCountTest extends TestCase
{
    public function testConstruction(): void
    {
        $category = new CategoryCount('tutorials', 42);

        self::assertSame('tutorials', $category->slug);
        self::assertSame(42, $category->count);
    }

    public function testConstructionWithZeroCount(): void
    {
        $category = new CategoryCount('empty', 0);

        self::assertSame('empty', $category->slug);
        self::assertSame(0, $category->count);
    }
}
