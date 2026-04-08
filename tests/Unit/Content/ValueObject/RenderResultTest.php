<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\RenderResult;
use PHPUnit\Framework\TestCase;

final class RenderResultTest extends TestCase
{
    public function testConstruction(): void
    {
        $result = new RenderResult(10, 2, ['error1', 'error2']);

        self::assertSame(10, $result->pages);
        self::assertSame(2, $result->skipped);
        self::assertSame(['error1', 'error2'], $result->errors);
    }

    public function testConstructionWithEmptyErrors(): void
    {
        $result = new RenderResult(5, 0, []);

        self::assertSame(5, $result->pages);
        self::assertSame(0, $result->skipped);
        self::assertSame([], $result->errors);
    }
}
