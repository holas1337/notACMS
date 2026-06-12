<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Twig;

use NotACms\Content\ContentTree;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use NotACms\Twig\ContentTwigExtension;
use NotACms\Twig\TranslationMapTwigExtension;
use PHPUnit\Framework\TestCase;

final class ContentTwigExtensionTest extends TestCase
{
    public function testContentUrlReturnsUrlForDirectoryKey(): void
    {
        $tree = new ContentTree();
        $tree->addPage(ContentItemFactory::page([], 'about', '/about/'));

        $contentService = $this->createStub(ContentServiceInterface::class);
        $contentService->method('findByDirectoryKey')->willReturnCallback(static fn (string $directoryKey, string $locale) => $tree->findByDirectoryKey($directoryKey));

        $extension = new ContentTwigExtension($contentService);

        self::assertSame('/about/', $extension->contentUrl('about', 'en'));
    }

    public function testContentUrlReturnsSlashForUnknownKey(): void
    {
        $tree = new ContentTree();

        $contentService = $this->createStub(ContentServiceInterface::class);
        $contentService->method('findByDirectoryKey')->willReturnCallback(static fn (string $directoryKey, string $locale) => $tree->findByDirectoryKey($directoryKey));

        $extension = new ContentTwigExtension($contentService);

        self::assertSame('/', $extension->contentUrl('unknown', 'en'));
    }
}

final class TranslationMapTwigExtensionTest extends TestCase
{
    public function testGetGlobalsReturnsTranslationMap(): void
    {
        $contentService = $this->createStub(ContentServiceInterface::class);
        $contentService->method('getTranslationMap')->willReturn([
            'about' => ['en' => '/about/', 'pl' => '/o-mnie/'],
        ]);

        $extension = new TranslationMapTwigExtension($contentService);
        $globals = $extension->getGlobals();

        self::assertArrayHasKey('translation_map', $globals);
        self::assertSame('/about/', $globals['translation_map']['about']['en']);
        self::assertSame('/o-mnie/', $globals['translation_map']['about']['pl']);
    }
}
