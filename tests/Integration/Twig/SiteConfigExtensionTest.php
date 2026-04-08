<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Twig;

use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Twig\SiteConfigExtension;
use PHPUnit\Framework\TestCase;

final class SiteConfigExtensionTest extends TestCase
{
    public function testGetGlobalsReturnsAllKeys(): void
    {
        $config = $this->createStub(SiteConfigServiceInterface::class);
        $config->method('getSiteConfig')->willReturn([
            'name' => 'Test Site',
            'base_url' => 'https://test.dev',
            'description' => 'A test site',
            'social' => ['github' => 'https://github.com/test'],
            'author' => ['name' => 'Test Author'],
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);
        $config->method('getDefaultLocale')->willReturn('en');
        $config->method('getLocales')->willReturn(['en', 'pl']);
        $config->method('getImageVariantWidths')->willReturn([640, 960]);
        $config->method('getNewPostDays')->willReturn(90);
        $config->method('getComingSoonRevealDays')->willReturn(14);

        $extension = new SiteConfigExtension($config);
        $globals = $extension->getGlobals();

        self::assertArrayHasKey('site_name', $globals);
        self::assertArrayHasKey('site_base_url', $globals);
        self::assertArrayHasKey('site_description', $globals);
        self::assertArrayHasKey('site_social', $globals);
        self::assertArrayHasKey('site_author', $globals);
        self::assertArrayHasKey('site_locales', $globals);
        self::assertArrayHasKey('site_default_locale', $globals);
        self::assertArrayHasKey('site_locales_list', $globals);
        self::assertArrayHasKey('image_variant_widths', $globals);
        self::assertArrayHasKey('new_post_days', $globals);
        self::assertArrayHasKey('coming_soon_reveal_days', $globals);
    }

    public function testGetGlobalsReturnsEmptyDefaults(): void
    {
        $config = $this->createStub(SiteConfigServiceInterface::class);
        $config->method('getSiteConfig')->willReturn([]);
        $config->method('getDefaultLocale')->willReturn('en');
        $config->method('getLocales')->willReturn([]);
        $config->method('getImageVariantWidths')->willReturn([]);
        $config->method('getNewPostDays')->willReturn(90);
        $config->method('getComingSoonRevealDays')->willReturn(14);

        $extension = new SiteConfigExtension($config);
        $globals = $extension->getGlobals();

        self::assertSame('', $globals['site_name']);
        self::assertSame('', $globals['site_base_url']);
        self::assertSame([], $globals['site_social']);
    }
}
