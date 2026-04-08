<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Content;

use NotACms\Content\ValueObject\ParsedMarkdown;
use NotACms\Service\Content\MarkdownParser;
use NotACms\Service\Content\MarkdownParserInterface;
use NotACms\Service\SiteConfigServiceInterface;
use PHPUnit\Framework\TestCase;

final class MarkdownParserTest extends TestCase
{
    private MarkdownParserInterface $parser;

    protected function setUp(): void
    {
        $siteConfig = $this->createStub(SiteConfigServiceInterface::class);
        $siteConfig->method('getBaseUrl')->willReturn('https://example.com');

        $this->parser = new MarkdownParser($siteConfig);
    }

    public function testParsesSimpleMarkdown(): void
    {
        $result = $this->parser->parse("# Hello\n\nThis is **bold** text.");

        self::assertInstanceOf(ParsedMarkdown::class, $result);
        self::assertStringContainsString('<h1>', $result->html);
        self::assertStringContainsString('Hello', $result->html);
        self::assertStringContainsString('<strong>bold</strong>', $result->html);
    }

    public function testExtractsFrontMatter(): void
    {
        $markdown = "---\ntitle: Test Post\ndate: 2024-01-15\n---\n\nContent here.";

        $result = $this->parser->parse($markdown);

        self::assertSame('Test Post', $result->frontMatter['title']);
        self::assertSame('2024-01-15', $result->frontMatter['date']);
        self::assertStringContainsString('<p>Content here.</p>', $result->html);
    }

    public function testHandlesNoFrontMatter(): void
    {
        $result = $this->parser->parse("Just plain content.");

        self::assertSame([], $result->frontMatter);
        self::assertStringContainsString('<p>Just plain content.</p>', $result->html);
    }

    public function testSupportsGfmFeatures(): void
    {
        $markdown = "- [ ] Task 1\n- [x] Task 2\n\n| A | B |\n|---|---|\n| 1 | 2 |";

        $result = $this->parser->parse($markdown);

        self::assertStringContainsString('Task 1', $result->html);
        self::assertStringContainsString('Task 2', $result->html);
        self::assertStringContainsString('<table>', $result->html);
    }

    public function testExternalLinksGetRelAttributes(): void
    {
        $markdown = "[External](https://example.org/page)";

        $result = $this->parser->parse($markdown);

        self::assertStringContainsString('rel="nofollow noopener noreferrer"', $result->html);
        self::assertStringContainsString('target="_blank"', $result->html);
    }

    public function testInternalLinksAreNotModified(): void
    {
        $markdown = "[Internal](https://example.com/page)";

        $result = $this->parser->parse($markdown);

        self::assertStringNotContainsString('target="_blank"', $result->html);
        self::assertStringNotContainsString('nofollow', $result->html);
    }

    public function testHeadingPermalinksAreGenerated(): void
    {
        $markdown = "## Section Title";

        $result = $this->parser->parse($markdown);

        self::assertStringContainsString('heading-anchor', $result->html);
        self::assertStringContainsString('href="#section-title"', $result->html);
    }

    public function testEmptyMarkdownReturnsEmptyHtml(): void
    {
        $result = $this->parser->parse('');

        self::assertSame([], $result->frontMatter);
        self::assertSame('', trim($result->html));
    }
}
