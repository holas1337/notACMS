<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\ParsedMarkdown;
use PHPUnit\Framework\TestCase;

final class ParsedMarkdownTest extends TestCase
{
    public function testConstruction(): void
    {
        $parsed = new ParsedMarkdown(['title' => 'Test'], '<p>HTML</p>');

        self::assertSame(['title' => 'Test'], $parsed->frontMatter);
        self::assertSame('<p>HTML</p>', $parsed->html);
    }

    public function testConstructionWithEmptyData(): void
    {
        $parsed = new ParsedMarkdown([], '');

        self::assertSame([], $parsed->frontMatter);
        self::assertSame('', $parsed->html);
    }
}

