<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ContentItem;
use NotACms\Content\ValueObject\LangSwitchContext;
use NotACms\Service\Content\LangSwitchUrlResolver;
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
        $this->extension = new LangSwitcherExtension(new LangSwitchUrlResolver($this->urlGenerator));
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

    public function testUrlOverridesApplyPerLocale(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/home/');

        $context = ['lang_switch' => new LangSwitchContext(urlOverrides: [
            'en' => '/en/tags/foo/',
            'de' => '/de/tags/foo/',
        ])];

        $result = $this->extension->langSwitchUrls($context, ['en', 'de']);

        self::assertSame('/en/tags/foo/', $result['en']);
        self::assertSame('/de/tags/foo/', $result['de']);
    }

    public function testMissingOverrideFallsBackToHome(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/home/');

        $context = ['lang_switch' => new LangSwitchContext(urlOverrides: ['en' => '/en/tags/foo/'])];

        $result = $this->extension->langSwitchUrls($context, ['en', 'de']);

        self::assertSame('/en/tags/foo/', $result['en']);
        self::assertSame('/home/', $result['de']);
    }

    public function testArchiveFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/archive/2025/04/');

        $context = ['lang_switch' => new LangSwitchContext(filterType: 'archive', archiveYear: 2025, archiveMonth: 4)];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/archive/2025/04/'], $result);
    }

    public function testYearOnlyArchiveFallback(): void
    {
        $this->urlGenerator->method('generate')
            ->willReturnMap([
                ['blog_archive_year_en', ['year' => 2025], UrlGeneratorInterface::ABSOLUTE_PATH, '/en/archive/2025/'],
            ]);

        $context = ['lang_switch' => new LangSwitchContext(filterType: 'archive', archiveYear: 2025)];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/archive/2025/'], $result);
    }

    public function testPaginatedFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/blog/page/3/');

        $context = ['lang_switch' => new LangSwitchContext(currentPage: 3)];

        $result = $this->extension->langSwitchUrls($context, ['en']);

        self::assertSame(['en' => '/en/blog/page/3/'], $result);
    }

    public function testBlogListFallback(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/en/blog/');

        $context = ['lang_switch' => new LangSwitchContext(currentPage: 1)];

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
