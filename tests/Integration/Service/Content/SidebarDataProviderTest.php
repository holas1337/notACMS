<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Content;

use NotACms\Content\ValueObject\CategoryCount;
use NotACms\Content\ValueObject\SidebarData;
use NotACms\Content\ValueObject\TagCount;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\SidebarDataProvider;
use NotACms\Service\Content\SidebarDataProviderInterface;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class SidebarDataProviderTest extends TestCase
{
    private SidebarDataProviderInterface $provider;

    private ContentServiceInterface $contentService;

    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->contentService = $this->createStub(ContentServiceInterface::class);
        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getRecentPostsLimit')->willReturn(5);
        $this->provider = new SidebarDataProvider($this->contentService, $this->siteConfigService);
    }

    public function testReturnsSidebarDataWithRecentPosts(): void
    {
        $posts = [
            ContentItemFactory::publishedPost(['date' => '2024-01-01']),
            ContentItemFactory::publishedPost(['date' => '2024-01-02']),
        ];
        $this->contentService->method('getRecentPosts')->willReturn($posts);
        $this->contentService->method('getTree')->willReturn(new \NotACms\Content\ContentTree());

        $result = $this->provider->getData('en');

        self::assertInstanceOf(SidebarData::class, $result);
        self::assertSame($posts, $result->recentPosts);
    }

    public function testReturnsCategories(): void
    {
        $tree = new \NotACms\Content\ContentTree();
        $tree->addPost(ContentItemFactory::withCategory('tutorials'));
        $tree->addPost(ContentItemFactory::withCategory('tutorials'));
        $tree->addPost(ContentItemFactory::withCategory('projects'));

        $this->contentService->method('getTree')->willReturn($tree);
        $this->contentService->method('getRecentPosts')->willReturn([]);

        $result = $this->provider->getData('en');

        self::assertCount(2, $result->categories);
        self::assertContainsOnlyInstancesOf(CategoryCount::class, $result->categories);
    }

    public function testReturnsTags(): void
    {
        $tree = new \NotACms\Content\ContentTree();
        $tree->addPost(ContentItemFactory::withTags('php', 'symfony'));
        $tree->addPost(ContentItemFactory::withTags('php'));

        $this->contentService->method('getTree')->willReturn($tree);
        $this->contentService->method('getRecentPosts')->willReturn([]);

        $result = $this->provider->getData('en');

        self::assertCount(2, $result->tags);
        self::assertContainsOnlyInstancesOf(TagCount::class, $result->tags);
    }

    public function testReturnsArchiveMonths(): void
    {
        $tree = new \NotACms\Content\ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-15']));
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-02-10']));

        $this->contentService->method('getTree')->willReturn($tree);
        $this->contentService->method('getRecentPosts')->willReturn([]);

        $result = $this->provider->getData('en');

        self::assertCount(2, $result->archiveMonths);
    }

    public function testUsesConfiguredRecentPostsLimit(): void
    {
        $limit = 3;
        $siteConfig = $this->createStub(SiteConfigServiceInterface::class);
        $siteConfig->method('getRecentPostsLimit')->willReturn($limit);
        $contentService = $this->createMock(ContentServiceInterface::class);
        $contentService->method('getTree')->willReturn(new \NotACms\Content\ContentTree());
        $contentService->expects(self::once())
            ->method('getRecentPosts')
            ->with('en', $limit)
            ->willReturn([]);

        $provider = new SidebarDataProvider($contentService, $siteConfig);
        $provider->getData('en');
    }
}
