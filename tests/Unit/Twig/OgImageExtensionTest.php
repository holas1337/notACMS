<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ContentItem;
use NotACms\Twig\OgImageExtension;
use PHPUnit\Framework\TestCase;

final class OgImageExtensionTest extends TestCase
{
    private string $siteBaseUrl;

    private OgImageExtension $extension;

    protected function setUp(): void
    {
        $this->siteBaseUrl = 'https://example.com';
        $this->extension = new OgImageExtension();
    }

    public function testReturnsContentImageWhenPresent(): void
    {
        $content = new ContentItem(
            ['image' => '/content/images/hero.webp'],
            '',
            'en',
        );

        $result = $this->extension->ogImageUrl($content, $this->siteBaseUrl);

        self::assertSame('https://example.com/content/images/hero.webp', $result);
    }

    public function testReturnsDefaultImageWhenContentIsNull(): void
    {
        $result = $this->extension->ogImageUrl(null, $this->siteBaseUrl);

        self::assertSame('https://example.com/build/images/og-default.jpg', $result);
    }

    public function testReturnsDefaultImageWhenContentHasNoImage(): void
    {
        $content = new ContentItem([], '', 'en');

        $result = $this->extension->ogImageUrl($content, $this->siteBaseUrl);

        self::assertSame('https://example.com/build/images/og-default.jpg', $result);
    }

    public function testReturnsDefaultImageWhenContentImageIsNull(): void
    {
        $content = new ContentItem(['image' => null], '', 'en');

        $result = $this->extension->ogImageUrl($content, $this->siteBaseUrl);

        self::assertSame('https://example.com/build/images/og-default.jpg', $result);
    }

    public function testHandlesDifferentBaseUrl(): void
    {
        $content = new ContentItem(
            ['image' => '/images/test.webp'],
            '',
            'en',
        );

        $result = $this->extension->ogImageUrl($content, 'https://other.example.org');

        self::assertSame('https://other.example.org/images/test.webp', $result);
    }
}
