<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Twig\BlogFilterExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BlogFilterExtensionTest extends TestCase
{
    private ContentServiceInterface $contentService;

    private TranslatorInterface $translator;

    private BlogFilterExtension $extension;

    protected function setUp(): void
    {
        $this->contentService = $this->createStub(ContentServiceInterface::class);
        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->extension = new BlogFilterExtension($this->contentService, $this->translator);
    }

    public function testReturnsCapitalizedCategoryName(): void
    {
        $result = $this->extension->blogFilterTitle('category', 'linux', null, null, 'en');

        self::assertSame('Linux', $result);
    }

    public function testReturnsHashtagForTag(): void
    {
        $result = $this->extension->blogFilterTitle('tag', 'devops', null, null, 'en');

        self::assertSame('#devops', $result);
    }

    public function testReturnsYearForArchiveYearOnly(): void
    {
        $result = $this->extension->blogFilterTitle('archive', null, 2026, null, 'en');

        self::assertSame('2026', $result);
    }

    public function testReturnsMonthYearForArchiveWithMonth(): void
    {
        $result = $this->extension->blogFilterTitle('archive', null, 2026, 3, 'en');

        self::assertSame('March 2026', $result);
    }

    public function testReturnsPolishMonthNameForPlLocale(): void
    {
        $result = $this->extension->blogFilterTitle('archive', null, 2026, 3, 'pl');

        self::assertSame('marca 2026', $result);
    }

    public function testReturnsBlogMenuLabelWhenPresent(): void
    {
        $blogItem = new ContentItem(
            ['menu' => ['label' => 'My Blog']],
            '',
            'en',
            '/blog',
            null,
            false,
            'blog',
        );
        $tree = new ContentTree();
        $tree->addPage($blogItem);
        $this->contentService->method('findByDirectoryKey')->willReturnCallback(static fn (string $directoryKey, string $locale) => $tree->findByDirectoryKey($directoryKey));

        $result = $this->extension->blogFilterTitle(null, null, null, null, 'en');

        self::assertSame('My Blog', $result);
    }

    public function testFallsBackToTranslationWhenNoBlogPage(): void
    {
        $tree = new ContentTree();
        $this->contentService->method('findByDirectoryKey')->willReturnCallback(static fn (string $directoryKey, string $locale) => $tree->findByDirectoryKey($directoryKey));
        $this->translator->method('trans')->willReturn('Blog');

        $result = $this->extension->blogFilterTitle(null, null, null, null, 'en');

        self::assertSame('Blog', $result);
    }
}
