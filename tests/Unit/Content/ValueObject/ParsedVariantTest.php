<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\ParsedVariant;
use PHPUnit\Framework\TestCase;

final class ParsedVariantTest extends TestCase
{
    public function testConstruction(): void
    {
        $variant = new ParsedVariant('image.webp', 640);

        self::assertSame('image.webp', $variant->originalFilename);
        self::assertSame(640, $variant->variantWidth);
    }

    public function testConstructionWithNullWidth(): void
    {
        $variant = new ParsedVariant('image.webp', null);

        self::assertSame('image.webp', $variant->originalFilename);
        self::assertNull($variant->variantWidth);
    }
}
