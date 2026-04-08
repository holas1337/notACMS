<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\ArchiveMonth;
use PHPUnit\Framework\TestCase;

final class ArchiveMonthTest extends TestCase
{
    public function testConstruction(): void
    {
        $month = new ArchiveMonth(2024, 1, 15);

        self::assertSame(2024, $month->year);
        self::assertSame(1, $month->month);
        self::assertSame(15, $month->count);
    }

    public function testConstructionWithZeroCount(): void
    {
        $month = new ArchiveMonth(2023, 12, 0);

        self::assertSame(2023, $month->year);
        self::assertSame(12, $month->month);
        self::assertSame(0, $month->count);
    }
}
