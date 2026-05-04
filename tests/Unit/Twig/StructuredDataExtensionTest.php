<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Service\StructuredDataBuilder;
use NotACms\Service\StructuredDataBuilderInterface;
use NotACms\Twig\StructuredDataExtension;
use PHPUnit\Framework\TestCase;

final class StructuredDataExtensionTest extends TestCase
{
    private StructuredDataExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new StructuredDataExtension(new StructuredDataBuilder());
    }

    public function testWrapsDataInLdJsonScriptTag(): void
    {
        $html = $this->extension->schemaLd(['@type' => 'WebPage', 'name' => 'Home']);

        self::assertStringStartsWith('<script type="application/ld+json">', $html);
        self::assertStringEndsWith('</script>', $html);
    }

    public function testInjectsSchemaContextWhenMissing(): void
    {
        $html = $this->extension->schemaLd(['@type' => 'WebPage']);

        self::assertStringContainsString('"@context": "https://schema.org"', $html);
    }

    public function testInjectedContextIsFirstKey(): void
    {
        $html = $this->extension->schemaLd(['@type' => 'WebPage', 'name' => 'X']);

        $contextPos = strpos($html, '"@context"');
        $typePos = strpos($html, '"@type"');

        self::assertNotFalse($contextPos);
        self::assertNotFalse($typePos);
        self::assertLessThan($typePos, $contextPos, '@context must appear before @type');
    }

    public function testPreservesExistingContext(): void
    {
        $html = $this->extension->schemaLd([
            '@context' => 'https://example.org/custom',
            '@type' => 'WebPage',
        ]);

        self::assertStringContainsString('"@context": "https://example.org/custom"', $html);
        self::assertStringNotContainsString('schema.org', $html);
    }

    public function testUsesPrettyPrintFormatting(): void
    {
        $html = $this->extension->schemaLd(['@type' => 'WebPage', 'name' => 'Home']);

        // JSON_PRETTY_PRINT splits keys onto separate lines; collapsed output would be a single line.
        self::assertStringContainsString("\n", trim(str_replace(["<script type=\"application/ld+json\">\n", "\n</script>"], '', $html)));
    }

    public function testUsesUnescapedSlashesForUrls(): void
    {
        $html = $this->extension->schemaLd([
            '@type' => 'WebPage',
            'url' => 'https://example.com/foo/bar',
        ]);

        self::assertStringContainsString('https://example.com/foo/bar', $html);
        self::assertStringNotContainsString('https:\\/\\/example.com', $html);
    }

    public function testThrowsOnEncodingError(): void
    {
        // Invalid UTF-8 byte sequence triggers JSON_THROW_ON_ERROR.
        $this->expectException(\JsonException::class);

        $this->extension->schemaLd(['@type' => 'WebPage', 'name' => "\xB1\x31"]);
    }

    public function testGetBuilderReturnsInjectedInstance(): void
    {
        $builder = new StructuredDataBuilder();
        $extension = new StructuredDataExtension($builder);

        self::assertSame($builder, $extension->getBuilder());
        self::assertInstanceOf(StructuredDataBuilderInterface::class, $extension->getBuilder());
    }
}
