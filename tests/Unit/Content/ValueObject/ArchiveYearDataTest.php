<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\ArchiveYearData;
use PHPUnit\Framework\TestCase;

final class ArchiveYearDataTest extends TestCase
{
    public function testConstruction(): void
    {
        $year = new ArchiveYearData(2024, 42);

        self::assertSame(2024, $year->year);
        self::assertSame(42, $year->count);
    }

    public function testZeroCount(): void
    {
        $year = new ArchiveYearData(2024, 0);

        self::assertSame(2024, $year->year);
        self::assertSame(0, $year->count);
    }

    public function testNegativeYear(): void
    {
        $year = new ArchiveYearData(-500, 10);

        self::assertSame(-500, $year->year);
        self::assertSame(10, $year->count);
    }
}
