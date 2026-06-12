<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Command;

use NotACms\Command\BuildStaticSiteCommand;
use NotACms\Content\ContentTree;
use NotACms\Content\ValueObject\ArchiveMonth;
use NotACms\Content\ValueObject\CategoryCount;
use NotACms\Content\ValueObject\TagCount;
use NotACms\Service\Content\ContentCacheInterface;
use NotACms\Service\Content\ContentTreeProviderInterface;
use NotACms\Service\Image\ImageResizerInterface;
use NotACms\Service\Image\ResponsiveImageServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use NotACms\Tests\TmpDirTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BuildStaticSiteCommandTest extends TestCase
{
    use TmpDirTrait;

    private string $tmpOutputDir;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->tmpOutputDir = sys_get_temp_dir() . '/notacms_build_' . uniqid();
        mkdir($this->tmpOutputDir, 0755, true);

        $siteConfig = $this->createStub(SiteConfigServiceInterface::class);
        $siteConfig->method('getLocales')->willReturn(['en']);
        $siteConfig->method('getDefaultLocale')->willReturn('en');
        $siteConfig->method('getUrlPrefix')->willReturn('/');
        $siteConfig->method('getPostsPerPage')->willReturn(10);
        $siteConfig->method('getImageVariantWidths')->willReturn([]);

        $tree = new ContentTree();
        $tree->addPost(ContentItemFactory::publishedPost(['slug' => 'test-post'], 'test-post', '/test-post/'));
        $tree->addPage(ContentItemFactory::page([], 'about', '/about/'));

        $contentService = $this->createStub(ContentTreeProviderInterface::class);
        $contentService->method('getTree')->willReturn($tree);

        $cache = $this->createStub(ContentCacheInterface::class);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(function (string $name, array $params = []): string {
                $path = match ($name) {
                    'home_en' => '/',
                    'blog_list_en' => '/blog/',
                    'search_en' => '/search/',
                    'error_404_en' => '/404/',
                    'error_500_en' => '/500/',
                    'robots' => '/robots.txt',
                    'sitemap' => '/sitemap.xml',
                    'rss_en' => '/feed/',
                    default => '/' . $name . '/',
                };

                foreach ($params as $key => $value) {
                    $path = str_replace('{' . $key . '}', (string) $value, $path);
                }

                return $path;
            });

        $httpKernel = $this->createStub(\Symfony\Component\HttpKernel\HttpKernelInterface::class);
        $httpKernel->method('handle')
            ->willReturn(new \Symfony\Component\HttpFoundation\Response('<html></html>', 200));

        $imageResizer = $this->createStub(ImageResizerInterface::class);
        $responsiveImageService = $this->createStub(ResponsiveImageServiceInterface::class);
        $responsiveImageService->method('getVariantWidths')->willReturn([]);

        $contentDir = sys_get_temp_dir() . '/notacms_content_' . uniqid();
        mkdir($contentDir, 0755, true);

        $command = new BuildStaticSiteCommand(
            $contentService,
            $cache,
            $siteConfig,
            $urlGenerator,
            new \NotACms\Service\StaticBuild\StaticUrlCollector($contentService, $siteConfig, $urlGenerator),
            new \NotACms\Service\StaticBuild\StaticPageRenderer($httpKernel),
            new \NotACms\Service\StaticBuild\MediaPublisher($imageResizer, $responsiveImageService, $siteConfig, $contentDir),
            $this->tmpOutputDir,
        );

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpOutputDir);
    }

    public function testSuccessfulBuild(): void
    {
        $this->commandTester->execute(['--output-dir' => $this->tmpOutputDir]);

        self::assertSame(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Building static site', $output);
        self::assertStringContainsString('Generated', $output);
    }

    public function testCreatesOutputDirectory(): void
    {
        $this->commandTester->execute(['--output-dir' => $this->tmpOutputDir]);

        self::assertDirectoryExists($this->tmpOutputDir);
    }

    public function testVerboseOutputShowsUrls(): void
    {
        $this->commandTester->execute(
            ['--output-dir' => $this->tmpOutputDir],
            ['verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE],
        );

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('✓', $output);
    }
}
