<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ContentItem;
use NotACms\Twig\LangSwitcherExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LangSwitcherExtensionTest extends TestCase
{
    private UrlGeneratorInterface $urlGenerator;

    private LangSwitcherExtension $extension;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->extension = new LangSwitcherExtension($this->urlGenerator);
    }

    public function testTranslationMapHit(): void
    {
        $content = new ContentItem([], '', 'pl', directoryKey: 'posts/hello');

        $context = [
            'content' => $content,
            'translation_map' => ['posts/hello' => ['en' => '/en/blog/hello/']],
        ];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/blog/hello/'], $result);
    }

    public function testLangSwitchUrlUsedForFirstLocaleOnly(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/home/');

        $context = ['lang_switch_url' => '/en/tags/foo/'];

        $result = $this->extension->langSwitchUrls($context, ['en', 'de']);

        self::assertSame('/en/tags/foo/', $result['en']);
        self::assertSame('/home/', $result['de']);
    }

    public function testArchiveFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/archive/2025/04/');

        $context = [
            'filter_type' => 'archive',
            'archive_year' => 2025,
            'archive_month' => 4,
        ];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/archive/2025/04/'], $result);
    }

    public function testPaginatedFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/blog/page/3/');

        $context = ['current_page' => 3];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/blog/page/3/'], $result);
    }

    public function testBlogListFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/blog/');

        $context = ['current_page' => 1];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/blog/'], $result);
    }

    public function testHomeFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/');

        $result = $this->extension->langSwitchUrls([], ['en']);

        self::assertSame(['en' => '/en/'], $result);
    }

    public function testMultipleLocales(): void
    {
        $this->urlGenerator->method('generate')
            ->willReturnMap([
                ['home_en', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/en/'],
                ['home_de', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/de/'],
                ['home_fr', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/fr/'],
            ]);

        $result = $this->extension->langSwitchUrls([], ['en', 'de', 'fr']);

        self::assertSame(['en' => '/en/', 'de' => '/de/', 'fr' => '/fr/'], $result);
    }
}
