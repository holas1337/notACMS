<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\Enum;

use NotACms\Content\Enum\CardLayout;
use PHPUnit\Framework\TestCase;

final class CardLayoutTest extends TestCase
{
    public function testCycleReturnsAllLayoutValues(): void
    {
        $result = CardLayout::cycle();

        self::assertCount(4, $result);
        self::assertContains('layout-top', $result);
        self::assertContains('layout-right', $result);
        self::assertContains('layout-text', $result);
        self::assertContains('layout-left', $result);
    }

    public function testCyclePreservesEnumOrder(): void
    {
        $result = CardLayout::cycle();

        self::assertSame('layout-top', $result[0]);
        self::assertSame('layout-right', $result[1]);
        self::assertSame('layout-text', $result[2]);
        self::assertSame('layout-left', $result[3]);
    }
}
