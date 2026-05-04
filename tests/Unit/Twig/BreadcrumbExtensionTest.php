<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Twig\BreadcrumbExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BreadcrumbExtensionTest extends TestCase
{
    private ContentServiceInterface $contentService;

    private UrlGeneratorInterface $urlGenerator;

    private BreadcrumbExtension $extension;

    protected function setUp(): void
    {
        $this->contentService = $this->createStub(ContentServiceInterface::class);
        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->extension = new BreadcrumbExtension($this->contentService, $this->urlGenerator);
    }

    private function defaultUrlMap(): array
    {
        return [
            'home_en' => '/',
            'blog_list_en' => '/blog/',
            'blog_category_en' => '/blog/category/',
        ];
    }

    private function setupUrlGenerator(): void
    {
        $routes = $this->defaultUrlMap();
        $this->urlGenerator->method('generate')
            ->willReturnCallback(static function (string $route) use ($routes): string {
                return $routes[$route] ?? '/';
            });
    }

    private function makePageItem(string $directoryKey, string $title, ?string $menuLabel = null): ContentItem
    {
        return new ContentItem(
            frontMatter: ['title' => $title, 'menu' => ['label' => $menuLabel, 'weight' => 10]],
            htmlContent: '',
            locale: 'en',
            directoryKey: $directoryKey,
        );
    }

    private function makePostItem(string $title, ?string $category = null, array $tags = []): ContentItem
    {
        return new ContentItem(
            frontMatter: [
                'title' => $title,
                'date' => '2024-01-15',
                'category' => $category,
                'tags' => $tags,
            ],
            htmlContent: '',
            locale: 'en',
            directoryKey: 'blog/' . $title,
        );
    }

    private function stubTreeWithHomeAndBlog(?ContentItem $home = null, ?ContentItem $blog = null): void
    {
        $tree = new ContentTree();
        if (null !== $home) {
            $tree->addPage($home);
        }
        if (null !== $blog) {
            $tree->addPage($blog);
        }
        $this->contentService->method('getTree')->willReturn($tree);
    }

    // ---- Home crumb ----

    public function testHomeCrumbUsesMenuLabel(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page', 'Start');
        $this->stubTreeWithHomeAndBlog($home);

        // Pass the home page itself — a static page gets home + page crumb
        $result = $this->extension->getBreadcrumbs($home, 'en');

        self::assertCount(2, $result);
        self::assertSame('Start', $result[0]['label']);
        self::assertSame('/', $result[0]['url']);
        self::assertSame('Home Page', $result[1]['label']);
        self::assertNull($result[1]['url']);
    }

    public function testStaticPageHomeCrumbFallsBackToTitleWhenNoMenuLabel(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $this->stubTreeWithHomeAndBlog($home);

        $page = $this->makePageItem('about', 'About Us');

        $result = $this->extension->getBreadcrumbs($page, 'en');

        self::assertSame('Home Page', $result[0]['label']);
    }

    public function testHomeCrumbOptionOverridesMenuLabel(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page', 'Start');
        $this->stubTreeWithHomeAndBlog($home);

        $page = $this->makePageItem('about', 'About Us');

        $result = $this->extension->getBreadcrumbs($page, 'en', ['home_label' => 'notACMS']);

        self::assertSame('notACMS', $result[0]['label']);
    }

    // ---- Blog list crumbs ----

    public function testBlogListCrumb(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page', 'Start');
        $blog = $this->makePageItem('blog', 'Blog Page', 'Articles');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $result = $this->extension->getBreadcrumbs(null, 'en');

        self::assertCount(2, $result);
        self::assertSame('Start', $result[0]['label']);
        self::assertSame('Articles', $result[1]['label']);
        self::assertSame('/blog/', $result[1]['url']);
    }

    public function testBlogListWithBlogContentItem(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Articles', 'Articles');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $result = $this->extension->getBreadcrumbs($blog, 'en');

        self::assertCount(2, $result);
        self::assertSame('Articles', $result[1]['label']);
    }

    public function testBlogListFilteredByCategory(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Blog');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $result = $this->extension->getBreadcrumbs(null, 'en', [
            'filter_type' => 'category',
            'filter_value' => 'tutorials',
        ]);

        self::assertCount(3, $result);
        self::assertSame('tutorials', $result[2]['label']);
        self::assertNull($result[2]['url']);
    }

    public function testBlogListFilteredByTag(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Blog');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $result = $this->extension->getBreadcrumbs(null, 'en', [
            'filter_type' => 'tag',
            'filter_value' => 'symfony',
        ]);

        self::assertCount(3, $result);
        self::assertSame('#symfony', $result[2]['label']);
    }

    public function testBlogListFilteredByArchive(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Blog');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $result = $this->extension->getBreadcrumbs(null, 'en', [
            'filter_type' => 'archive',
            'archive_date' => 'January 2024',
        ]);

        self::assertCount(3, $result);
        self::assertSame('January 2024', $result[2]['label']);
    }

    // ---- Blog post crumbs ----

    public function testBlogPostCrumb(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Blog');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $post = $this->makePostItem('My Post', 'tutorials');

        $result = $this->extension->getBreadcrumbs($post, 'en');

        self::assertCount(4, $result);
        self::assertSame('Home Page', $result[0]['label']);
        self::assertSame('Blog', $result[1]['label']);
        self::assertSame('/blog/', $result[1]['url']);
        self::assertSame('tutorials', $result[2]['label']);
        self::assertSame('/blog/category/', $result[2]['url']);
        self::assertSame('My Post', $result[3]['label']);
        self::assertNull($result[3]['url']);
    }

    public function testBlogPostWithoutCategory(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Blog');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        $post = $this->makePostItem('No Category Post');

        $result = $this->extension->getBreadcrumbs($post, 'en');

        self::assertCount(3, $result);
        self::assertSame('No Category Post', $result[2]['label']);
    }

    // ---- getLabel edge cases ----

    public function testBlogListUsesContentItemMenuLabelWhenPresent(): void
    {
        $this->setupUrlGenerator();
        $home = $this->makePageItem('home', 'Home Page');
        $blog = $this->makePageItem('blog', 'Blog');
        $this->stubTreeWithHomeAndBlog($home, $blog);

        // Create blog index with its own menu label
        $index = $this->makePageItem('blog', 'All Posts', 'All Posts');

        $result = $this->extension->getBreadcrumbs($index, 'en');

        self::assertCount(2, $result);
        self::assertSame('All Posts', $result[1]['label']);
    }
}
