<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

final readonly class ContentTreeBuilder implements ContentTreeBuilderInterface
{
    public function __construct(
        private MarkdownParserInterface $markdownParser,
        private SiteConfigServiceInterface $siteConfigService,
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
            $content = $file->getContents();

            $parsed = $this->markdownParser->parse($content);

            $isIndex = str_starts_with(basename($relativePath), '_index_');
            $directoryKey = basename(dirname($relativePath));

            $url = $this->computeUrl($parsed->frontMatter, $locale);

            $item = new ContentItem(
                frontMatter: $parsed->frontMatter,
                htmlContent: $parsed->html,
                locale: $locale,
                url: $url,
                sourcePath: $relativePath,
                isIndexItem: $isIndex,
                directoryKey: $directoryKey,
            );

            if ($isIndex) {
                $contentTree->addPage($item);
            } elseif (str_starts_with($relativePath, self::BLOG_CONTENT_PREFIX)) {
                $contentTree->addPost($item);
            } else {
                $contentTree->addPage($item);
            }
        }

        return $contentTree;
    }

    /**
     * @param array<string, mixed> $fm
     */
    private function computeUrl(array $fm, string $locale): string
    {
        $slug = $fm['slug'] ?? '';

        if ($this->siteConfigService->getDefaultLocale() !== $locale) {
            $slug = '' === $slug ? $locale : $locale.'/'.$slug;
        }

        if ('' === $slug) {
            return '/';
        }

        return '/'.ltrim((string) $slug, '/').'/';
    }
}
