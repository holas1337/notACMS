<?php

declare(strict_types=1);

namespace NotACms\Command;

use NotACms\Content\ValueObject\RenderResult;
use NotACms\Service\Content\ContentCacheInterface;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Image\ImageResizerInterface;
use NotACms\Service\Image\ResponsiveImageServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:build', description: 'Build static HTML site')]
final class BuildStaticSiteCommand extends Command
{
    private const string PAGEFIND_OUTPUT = 'public/pagefind';

    public function __construct(
        private readonly HttpKernelInterface $httpKernel,
        private readonly ContentServiceInterface $contentService,
        private readonly ContentCacheInterface $contentCache,
        private readonly ImageResizerInterface $imageResizer,
        private readonly ResponsiveImageServiceInterface $responsiveImageService,
        private readonly SiteConfigServiceInterface $siteConfigService,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%notacms_content%')] private readonly string $contentDir,
        #[Autowire('%notacms_static_dir%')] private readonly string $staticDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'output-dir',
            'o',
            InputOption::VALUE_OPTIONAL,
            'Output directory',
            $this->staticDir,
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Building static site');

        $outputDir = $input->getOption('output-dir') ?? $this->staticDir;
        $filesystem = new Filesystem();

        $symfonyStyle->section('Clearing output directory');
        if (is_dir($outputDir)) {
            $filesystem->remove($outputDir);
        }

        $filesystem->mkdir($outputDir);

        foreach ($this->siteConfigService->getLocales() as $locale) {
            $this->contentCache->invalidateCache($locale);
        }

        $renderResult = $this->renderPages($outputDir, $filesystem, $symfonyStyle, $output);

        $this->copyMediaFiles($outputDir, $filesystem, $symfonyStyle);
        $optimized = $this->optimizeOriginals($outputDir, $symfonyStyle);
        $variants = $this->generateResponsiveImages($outputDir, $symfonyStyle);

        $symfonyStyle->newLine();
        $symfonyStyle->success(
            sprintf(
                'Generated %d pages (%d skipped), optimized %d images, generated %d responsive variants',
                $renderResult->pages,
                $renderResult->skipped,
                $optimized,
                $variants,
            ),
        );

        if ([] !== $renderResult->errors) {
            $symfonyStyle->warning('Errors encountered:');
            foreach ($renderResult->errors as $err) {
                $symfonyStyle->text('  - '.$err);
            }
        }

        $symfonyStyle->text(sprintf('Output: %s', $outputDir));
        $symfonyStyle->text(
            'Next step: npx pagefind --site '.$input->getOption('output-dir').' --output-path '.self::PAGEFIND_OUTPUT,
        );

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function collectRoutes(): array
    {
        $routes = [];

        foreach ($this->siteConfigService->getLocales() as $locale) {
            $tree = $this->contentService->getTree($locale);

            $routes[] = $this->route('home_'.$locale);
            $routes[] = $this->route('blog_list_'.$locale);

            $posts = $tree->getAllPosts();
            $totalPages = (int) ceil(count($posts) / $this->siteConfigService->getPostsPerPage());
            for ($page = 2; $page <= $totalPages; ++$page) {
                $routes[] = $this->route('blog_list_paginated_'.$locale, ['page' => $page]);
            }

            foreach ($posts as $post) {
                $routes[] = $post->url();
            }

            foreach ($tree->getScheduledPosts() as $post) {
                $routes[] = $post->url();
            }

            foreach ($tree->getAllCategories() as $categoryCount) {
                $routes[] = $this->route('blog_category_'.$locale, ['category' => $categoryCount->slug]);
            }

            foreach ($tree->getAllTags() as $tag) {
                $routes[] = $this->route('blog_tag_'.$locale, ['tag' => $tag->slug]);
            }

            foreach ($tree->getArchiveMonths() as $archive) {
                $routes[] = $this->route('blog_archive_'.$locale, [
                    'year' => $archive->year,
                    'month' => sprintf('%02d', $archive->month),
                ]);
            }

            foreach ($tree->getArchiveYears() as $archiveYear) {
                $routes[] = $this->route('blog_archive_year_'.$locale, ['year' => $archiveYear->year]);
            }

            $homeUrl = $this->route('home_'.$locale);
            foreach ($tree->getStaticPages() as $page) {
                if (
                    !$page->isDynamic()
                    && '' !== $page->url()
                    && $page->url() !== $homeUrl
                ) {
                    $routes[] = $page->url();
                }
            }

            $routes[] = $this->route('search_'.$locale);
        }

        return array_unique($routes);
    }

    private function renderPages(
        string $outputDir,
        Filesystem $filesystem,
        SymfonyStyle $symfonyStyle,
        OutputInterface $output,
    ): RenderResult {
        $routes = $this->collectRoutes();
        $symfonyStyle->section('Rendering pages');
        $symfonyStyle->text(sprintf('Collected %d URLs to render', count($routes)));

        $pages = 0;
        $skipped = 0;
        $errors = [];

        foreach ($routes as $url) {
            try {
                $html = $this->renderUrl($url);
                $this->writeHtml($outputDir, $url, $html, $filesystem);
                ++$pages;
                if ($output->isVerbose()) {
                    $symfonyStyle->text('  ✓ '.$url);
                }
            } catch (\Throwable $e) {
                $errors[] = $url.': '.$e->getMessage();
                ++$skipped;
                if ($output->isVerbose()) {
                    $symfonyStyle->text(sprintf('  ✗ %s: ', $url).$e->getMessage());
                }
            }
        }

        $feedUrls = [$this->route('robots'), $this->route('sitemap'), $this->route('llms_txt')];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            $feedUrls[] = $this->route('rss_'.$locale);
        }

        foreach ($feedUrls as $url) {
            try {
                $content = $this->renderUrl($url);
                $path = $outputDir.$url;
                if (str_ends_with($url, '/')) {
                    $filesystem->dumpFile(rtrim($path, '/').'/index.xml', $content);
                } else {
                    $filesystem->dumpFile($path, $content);
                }

                ++$pages;
            } catch (\Throwable $e) {
                $errors[] = $url.': '.$e->getMessage();
            }
        }

        $errorPages = [];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            $prefix = ltrim($this->siteConfigService->getUrlPrefix($locale), '/');
            $errorPages[$this->route('error_404_'.$locale)] = $prefix.'404.html';
            $errorPages[$this->route('error_500_'.$locale)] = $prefix.'500.html';
        }

        foreach ($errorPages as $url => $filename) {
            $request = Request::create($url, Request::METHOD_GET);
            $request->attributes->set('_static_build', true);
            $response = $this->httpKernel->handle(
                $request,
                HttpKernelInterface::SUB_REQUEST,
                false,
            );
            $filesystem->dumpFile(
                $outputDir.'/'.$filename,
                (string) $response->getContent(),
            );
            ++$pages;
            if ($output->isVerbose()) {
                $symfonyStyle->text('  ✓ error page → '.$filename);
            }
        }

        return new RenderResult($pages, $skipped, $errors);
    }

    private function optimizeOriginals(string $outputDir, SymfonyStyle $symfonyStyle): int
    {
        $mediaDir = $outputDir.'/media';
        if (!is_dir($mediaDir)) {
            return 0;
        }

        $finder = new Finder()->files()->in($mediaDir)->name('*.webp');
        $optimized = 0;

        foreach ($finder as $file) {
            $baseName = $file->getFilenameWithoutExtension();

            if ($this->isVariantFile($baseName)) {
                continue;
            }

            $this->imageResizer->optimize($file->getRealPath());
            ++$optimized;
            if ($symfonyStyle->isVerbose()) {
                $symfonyStyle->text(
                    '  optimized: '.
                        basename($file->getPath()).
                        '/'.
                        $file->getFilename(),
                );
            }
        }

        if (0 < $optimized) {
            $symfonyStyle->text(sprintf('Optimized %d original image(s)', $optimized));
        }

        return $optimized;
    }

    private function generateResponsiveImages(
        string $outputDir,
        SymfonyStyle $symfonyStyle,
    ): int {
        $mediaDir = $outputDir.'/media';
        if (!is_dir($mediaDir)) {
            return 0;
        }

        $finder = new Finder()->files()->in($mediaDir)->name('*.webp');
        $generated = 0;

        foreach ($finder as $file) {
            $baseName = $file->getFilenameWithoutExtension();

            if ($this->isVariantFile($baseName)) {
                continue;
            }

            $filePath = $file->getRealPath();
            $imageInfo = @getimagesize($filePath);

            if (false === $imageInfo) {
                continue;
            }

            $width = $imageInfo[0];
            $dir = $file->getPath();

            $variantWidths = $this->responsiveImageService->getVariantWidths($width);

            foreach ($variantWidths as $variantWidth) {
                $this->imageResizer->resize($filePath, $dir.'/'.$baseName.'-'.$variantWidth.'w.webp', $variantWidth);
                ++$generated;

                if ($symfonyStyle->isVerbose()) {
                    $symfonyStyle->text('  variant: '.basename($dir).'/'.$baseName.'-'.$variantWidth.'w.webp');
                }
            }
        }

        if (0 < $generated) {
            $symfonyStyle->text(
                sprintf('Generated %d responsive image variant(s)', $generated),
            );
        }

        return $generated;
    }

    private function copyMediaFiles(
        string $outputDir,
        Filesystem $filesystem,
        SymfonyStyle $symfonyStyle,
    ): void {
        $contentDir = $this->contentDir;
        $finder = new Finder()->directories()->in($contentDir)->name('files');

        $copied = 0;
        foreach ($finder as $dir) {
            $postDirName = basename($dir->getPath());
            $targetDir = $outputDir.'/media/'.$postDirName;
            $filesystem->mirror($dir->getRealPath(), $targetDir);
            ++$copied;
            if ($symfonyStyle->isVerbose()) {
                $symfonyStyle->text(sprintf('  media: %s/', $postDirName));
            }
        }

        if (0 < $copied) {
            $symfonyStyle->text(
                sprintf('Copied media from %d content directories', $copied),
            );
        }
    }

    private function renderUrl(string $url): string
    {
        $request = Request::create($url, Request::METHOD_GET);
        $request->attributes->set('_static_build', true);

        $response = $this->httpKernel->handle(
            $request,
            HttpKernelInterface::SUB_REQUEST,
            false,
        );

        if (Response::HTTP_BAD_REQUEST <= $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('HTTP %d for %s', $response->getStatusCode(), $url));
        }

        return (string) $response->getContent();
    }

    private function writeHtml(
        string $outputDir,
        string $url,
        string $html,
        Filesystem $filesystem,
    ): void {
        // '/' → /index.html, '/foo/' → /foo/index.html
        $path = '/' === $url ? '' : rtrim($url, '/');
        $filePath = $outputDir.$path.'/index.html';
        $filesystem->dumpFile($filePath, $html);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function route(string $name, array $params = []): string
    {
        return $this->urlGenerator->generate($name, $params);
    }

    private function isVariantFile(string $baseName): bool
    {
        return array_any($this->siteConfigService->getImageVariantWidths(), fn ($width): bool => str_ends_with($baseName, '-'.$width.'w'));
    }
}
