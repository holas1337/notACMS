<?php

declare(strict_types=1);

namespace NotACms\Command;

use NotACms\Content\ValueObject\RenderResult;
use NotACms\Service\Content\ContentCacheInterface;
use NotACms\Service\Content\ContentTreeProviderInterface;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Service\StaticBuild\MediaPublisherInterface;
use NotACms\Service\StaticBuild\StaticPageRendererInterface;
use NotACms\Service\StaticBuild\StaticUrlCollectorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:build', description: 'Build static HTML site')]
final class BuildStaticSiteCommand extends Command
{
    private const string PAGEFIND_OUTPUT = 'public/pagefind';

    public function __construct(
        private readonly ContentTreeProviderInterface $contentTreeProvider,
        private readonly ContentCacheInterface $contentCache,
        private readonly SiteConfigServiceInterface $siteConfigService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly StaticUrlCollectorInterface $staticUrlCollector,
        private readonly StaticPageRendererInterface $staticPageRenderer,
        private readonly MediaPublisherInterface $mediaPublisher,
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
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Allow clearing an output directory other than the configured static dir',
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Building static site');

        $outputDir = (string) ($input->getOption('output-dir') ?? $this->staticDir);
        $filesystem = new Filesystem();

        $symfonyStyle->section('Clearing output directory');
        if (is_dir($outputDir)) {
            if (rtrim($outputDir, '/') !== rtrim($this->staticDir, '/') && true !== $input->getOption('force')) {
                $symfonyStyle->error(sprintf(
                    'Refusing to clear existing directory "%s" (configured static dir is "%s") — pass --force to override.',
                    $outputDir,
                    $this->staticDir,
                ));

                return Command::FAILURE;
            }

            $filesystem->remove($outputDir);
        }

        $filesystem->mkdir($outputDir);

        foreach ($this->siteConfigService->getLocales() as $locale) {
            $this->contentCache->invalidateCache($locale);
        }

        $renderResult = $this->renderPages($outputDir, $symfonyStyle, $output);

        $mediaPublishResult = $this->mediaPublisher->publish($outputDir);
        if ($symfonyStyle->isVerbose()) {
            foreach ($mediaPublishResult->notes as $note) {
                $symfonyStyle->text('  '.$note);
            }
        }

        $symfonyStyle->newLine();
        $symfonyStyle->success(
            sprintf(
                'Generated %d pages (%d skipped), optimized %d images, generated %d responsive variants',
                $renderResult->pages,
                $renderResult->skipped,
                $mediaPublishResult->optimized,
                $mediaPublishResult->variants,
            ),
        );

        $this->printWarnings($symfonyStyle, $renderResult, $mediaPublishResult->warnings);

        $symfonyStyle->text(sprintf('Output: %s', $outputDir));
        $symfonyStyle->text(
            'Next step: npx pagefind --site '.$outputDir.' --output-path '.self::PAGEFIND_OUTPUT,
        );

        return Command::SUCCESS;
    }

    private function renderPages(
        string $outputDir,
        SymfonyStyle $symfonyStyle,
        OutputInterface $output,
    ): RenderResult {
        $staticUrlCollection = $this->staticUrlCollector->collect();
        $symfonyStyle->section('Rendering pages');
        $symfonyStyle->text(sprintf('Collected %d URLs to render', count($staticUrlCollection->urls)));
        if ($output->isVerbose()) {
            foreach ($staticUrlCollection->notes as $note) {
                $symfonyStyle->text('  '.$note);
            }
        }

        $pages = 0;
        $skipped = 0;
        $errors = [];

        foreach ($staticUrlCollection->urls as $url) {
            try {
                $html = $this->staticPageRenderer->render($url);
                $this->staticPageRenderer->writePage($outputDir, $url, $html);
                ++$pages;
                if ($output->isVerbose()) {
                    $symfonyStyle->text('  ✓ '.$url);
                }
            } catch (\Throwable $throwable) {
                $errors[] = $url.': '.$throwable->getMessage();
                ++$skipped;
                if ($output->isVerbose()) {
                    $symfonyStyle->text(sprintf('  ✗ %s: ', $url).$throwable->getMessage());
                }
            }
        }

        $feedUrls = [
            $this->urlGenerator->generate('robots'),
            $this->urlGenerator->generate('sitemap'),
            $this->urlGenerator->generate('llms_txt'),
        ];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            $feedUrls[] = $this->urlGenerator->generate('rss_'.$locale);
        }

        foreach ($feedUrls as $url) {
            try {
                $content = $this->staticPageRenderer->render($url);
                $this->staticPageRenderer->writeFeed($outputDir, $url, $content);
                ++$pages;
            } catch (\Throwable $throwable) {
                $errors[] = $url.': '.$throwable->getMessage();
            }
        }

        foreach ($this->collectErrorPages() as $url => $filename) {
            $content = $this->staticPageRenderer->render($url, allowErrorStatus: true);
            $this->staticPageRenderer->writeFile($outputDir, $filename, $content);
            ++$pages;
            if ($output->isVerbose()) {
                $symfonyStyle->text('  ✓ error page → '.$filename);
            }
        }

        return new RenderResult($pages, $skipped, $errors);
    }

    /**
     * @return array<string, string> URL => output filename
     */
    private function collectErrorPages(): array
    {
        $errorPages = [];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            $prefix = ltrim($this->siteConfigService->getUrlPrefix($locale), '/');
            $errorPages[$this->urlGenerator->generate('error_404_'.$locale)] = $prefix.'404.html';
            $errorPages[$this->urlGenerator->generate('error_500_'.$locale)] = $prefix.'500.html';
        }

        return $errorPages;
    }

    /**
     * @param list<string> $mediaWarnings
     */
    private function printWarnings(SymfonyStyle $symfonyStyle, RenderResult $renderResult, array $mediaWarnings): void
    {
        $contentWarnings = [];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            foreach ($this->contentTreeProvider->getTree($locale)->getWarnings() as $contentWarning) {
                $contentWarnings[] = $contentWarning;
            }
        }

        $contentWarnings = array_unique(array_merge($contentWarnings, $mediaWarnings));
        if ([] !== $contentWarnings) {
            $symfonyStyle->warning('Content warnings:');
            foreach ($contentWarnings as $contentWarning) {
                $symfonyStyle->text('  - '.$contentWarning);
            }
        }

        if ([] !== $renderResult->errors) {
            $symfonyStyle->warning('Errors encountered:');
            foreach ($renderResult->errors as $error) {
                $symfonyStyle->text('  - '.$error);
            }
        }
    }
}
