<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\LocaleConfigInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

final readonly class ContentTreeBuilder implements ContentTreeBuilderInterface
{
    public function __construct(
        private MarkdownParserInterface $markdownParser,
        private LocaleConfigInterface $localeConfig,
        private LoggerInterface $logger,
        #[Autowire('%notacms_content%')]
        private string $contentDir,
    ) {
    }

    public function build(string $locale, bool $includeDrafts = false, bool $includeScheduled = false): ContentTree
    {
        $contentTree = new ContentTree($includeDrafts, $includeScheduled);

        if (!is_dir($this->contentDir)) {
            return $contentTree;
        }

        $finder = new Finder()
            ->files()
            ->in($this->contentDir)
            ->name($locale.'.md')
            ->name('_index_'.$locale.'.md')
            ->sortByName();

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();

            try {
                $parsed = $this->markdownParser->parse($file->getContents());
            } catch (\Throwable $throwable) {
                $this->recordWarning($contentTree, sprintf('Skipped %s: %s', $relativePath, $throwable->getMessage()));

                continue;
            }

            if (!array_key_exists('slug', $parsed->frontMatter)) {
                $this->recordWarning($contentTree, sprintf('Skipped %s: missing "slug" in frontmatter (use slug: "" for the home page)', $relativePath));

                continue;
            }

            $this->warnOnInvalidTaxonomy($contentTree, $relativePath, $parsed->frontMatter);

            $isIndex = str_starts_with(basename($relativePath), '_index_');
            if (!$isIndex && !str_starts_with($relativePath, self::BLOG_CONTENT_PREFIX)) {
                $slugValue = is_scalar($parsed->frontMatter['slug']) ? trim((string) $parsed->frontMatter['slug'], '/') : '';
                if (str_contains($slugValue, '/')) {
                    $this->recordWarning($contentTree, sprintf('%s: page slug "%s" has multiple segments — the static page route only matches single-segment slugs, this page will 404', $relativePath, $slugValue));
                }
            }

            $directory = dirname($relativePath);
            $directoryKey = '.' !== $directory ? $directory : null;

            $url = $this->computeUrl($parsed->frontMatter, $locale);

            $isPost = !$isIndex && str_starts_with($relativePath, self::BLOG_CONTENT_PREFIX);

            $item = new ContentItem(
                frontMatter: $parsed->frontMatter,
                htmlContent: $parsed->html,
                locale: $locale,
                url: $url,
                sourcePath: $relativePath,
                isIndexItem: $isIndex,
                directoryKey: $directoryKey,
                isPostItem: $isPost,
            );

            if ($isPost) {
                $contentTree->addPost($item);
            } else {
                $contentTree->addPage($item);
            }
        }

        return $contentTree;
    }

    private function recordWarning(ContentTree $contentTree, string $warning): void
    {
        $this->logger->warning($warning);
        $contentTree->addWarning($warning);
    }

    /**
     * @param array<string, mixed> $frontMatter
     */
    private function warnOnInvalidTaxonomy(ContentTree $contentTree, string $relativePath, array $frontMatter): void
    {
        $values = [];
        if (isset($frontMatter['category']) && is_scalar($frontMatter['category'])) {
            $values[] = (string) $frontMatter['category'];
        }

        if (isset($frontMatter['tags']) && is_array($frontMatter['tags'])) {
            foreach ($frontMatter['tags'] as $tag) {
                if (is_scalar($tag)) {
                    $values[] = (string) $tag;
                }
            }
        }

        foreach ($values as $value) {
            if ('' !== $value && 1 !== preg_match('/^[a-z0-9-]+$/', $value)) {
                $warning = sprintf('%s: category/tag "%s" is not URL-safe ([a-z0-9-] only) — normalized for URLs, please fix the frontmatter', $relativePath, $value);
                $this->logger->warning($warning);
                $contentTree->addWarning($warning);
            }
        }
    }

    /**
     * @param array<string, mixed> $frontMatter
     */
    private function computeUrl(array $frontMatter, string $locale): string
    {
        $slug = $frontMatter['slug'] ?? '';

        if ($this->localeConfig->getDefaultLocale() !== $locale) {
            $slug = '' === $slug ? $locale : $locale.'/'.$slug;
        }

        if ('' === $slug) {
            return '/';
        }

        return '/'.ltrim((string) $slug, '/').'/';
    }
}
