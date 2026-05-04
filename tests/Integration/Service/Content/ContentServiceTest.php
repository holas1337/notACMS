<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Content;

use NotACms\Content\ContentTree;
use NotACms\Service\Content\ContentCacheInterface;
use NotACms\Service\Content\ContentService;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\ContentTreeBuilderInterface;
use NotACms\Service\Content\TranslationMapBuilderInterface;
use NotACms\Service\Preview\DraftPreviewServiceInterface;
use NotACms\Service\Preview\ScheduledPreviewServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ContentServiceTest extends TestCase
{
    private ContentServiceInterface $service;

    private ContentTreeBuilderInterface $contentTreeBuilder;

    private CacheInterface $cache;

    private TranslationMapBuilderInterface $translationMapBuilder;

    private DraftPreviewServiceInterface $draftPreviewService;

    private ScheduledPreviewServiceInterface $scheduledPreviewService;

    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->contentTreeBuilder = $this->createStub(ContentTreeBuilderInterface::class);
        $this->cache = $this->createStub(CacheInterface::class);
        $this->cache->method('get')
            ->willReturnCallback(function (string $key, callable $callback): ContentTree {
                return $callback($this->createStub(ItemInterface::class));
            });
        $this->cache->method('delete')->willReturn(true);
        $this->translationMapBuilder = $this->createStub(TranslationMapBuilderInterface::class);
        $this->draftPreviewService = $this->createStub(DraftPreviewServiceInterface::class);
        $this->scheduledPreviewService = $this->createStub(ScheduledPreviewServiceInterface::class);
        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getLocales')->willReturn(['en', 'pl']);

        $this->service = new ContentService(
            $this->contentTreeBuilder,
            $this->cache,
            $this->translationMapBuilder,
            $this->draftPreviewService,
            $this->scheduledPreviewService,
            $this->siteConfigService,
        );
    }

    public function testGetTreeReturnsContentTree(): void
    {
        $tree = new ContentTree();
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $result = $this->service->getTree('en');

        self::assertInstanceOf(ContentTree::class, $result);
    }

    public function testGetTreeCachesResult(): void
    {
        $tree = new ContentTree();
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $first = $this->service->getTree('en');
        $second = $this->service->getTree('en');

        self::assertSame($first, $second);
    }

    public function testFindByUrlDelegatesToTree(): void
    {
        $post = ContentItemFactory::publishedPost([], 'my-post', '/my-post/');
        $tree = new ContentTree();
        $tree->addPost($post);
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $result = $this->service->findByUrl('/my-post/', 'en');

        self::assertSame($post, $result);
    }

    public function testFindPostBySlugDelegatesToTree(): void
    {
        $post = ContentItemFactory::publishedPost(['slug' => 'my-post'], 'my-post', '/my-post/');
        $tree = new ContentTree();
        $tree->addPost($post);
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $result = $this->service->findPostBySlug('my-post', 'en');

        self::assertSame($post, $result);
    }

    public function testFindScheduledPostBySlugDelegatesToTree(): void
    {
        $post = ContentItemFactory::scheduledPost(['slug' => 'future-post'], 'future-post', '/future-post/');
        $tree = new ContentTree(includeScheduled: true);
        $tree->addPost($post);
        $this->contentTreeBuilder->method('build')->willReturn($tree);
        $this->scheduledPreviewService->method('isEnabled')->willReturn(true);

        $result = $this->service->findScheduledPostBySlug('future-post', 'en');

        self::assertSame($post, $result);
    }

    public function testFindScheduledPostBySlugReturnsNullWhenNotFound(): void
    {
        $tree = new ContentTree();
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $result = $this->service->findScheduledPostBySlug('nonexistent', 'en');

        self::assertNull($result);
    }

    public function testGetPostsReturnsPaginatedPosts(): void
    {
        $tree = new ContentTree();
        for ($i = 1; $i <= 15; $i++) {
            $tree->addPost(ContentItemFactory::publishedPost(['date' => "2024-01-{$i}"], "post-{$i}", "/post-{$i}/"));
        }
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $result = $this->service->getPosts('en', 1, 10);

        self::assertCount(10, $result);
    }

    public function testGetTotalPostsReturnsCount(): void
    {
        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost());
        $tree->addPost(ContentItemFactory::publishedPost(['date' => '2024-01-02'], 'post2', '/post2/'));
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        self::assertSame(2, $this->service->getTotalPosts('en'));
    }

    public function testGetRecentPostsReturnsLimitedPosts(): void
    {
        $tree = new ContentTree();
        for ($i = 1; $i <= 10; $i++) {
            $tree->addPost(ContentItemFactory::publishedPost(['date' => "2024-01-{$i}"], "post-{$i}", "/post-{$i}/"));
        }
        $this->contentTreeBuilder->method('build')->willReturn($tree);

        $result = $this->service->getRecentPosts('en', 3);

        self::assertCount(3, $result);
    }

    public function testInvalidateCacheClearsTreesAndTranslationMap(): void
    {
        $tree1 = new ContentTree();
        $tree2 = new ContentTree();
        $callCount = 0;
        $this->contentTreeBuilder->method('build')
            ->willReturnCallback(function () use ($tree1, $tree2, &$callCount): ContentTree {
                ++$callCount;

                return 1 === $callCount ? $tree1 : $tree2;
            });
        $this->cache->method('get')
            ->willReturnCallback(function (string $key, callable $callback): ContentTree {
                return $callback($this->createStub(ItemInterface::class));
            });

        $first = $this->service->getTree('en');
        $this->service->invalidateCache('en');
        $second = $this->service->getTree('en');

        self::assertNotSame($first, $second);
    }

    public function testGetTranslationMapBuildsFromAllLocales(): void
    {
        $tree = new ContentTree();
        $tree->addPage(ContentItemFactory::page([], 'about', '/about/'));
        $this->contentTreeBuilder->method('build')->willReturn($tree);
        $this->translationMapBuilder->method('build')->willReturn(
            ['about' => ['en' => '/about/', 'pl' => '/o-mnie/']],
        );

        $result = $this->service->getTranslationMap();

        self::assertArrayHasKey('about', $result);
    }
}
