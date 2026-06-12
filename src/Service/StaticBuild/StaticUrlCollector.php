<?php

declare(strict_types=1);

namespace NotACms\Service\StaticBuild;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Content\ValueObject\StaticUrlCollection;
use NotACms\Service\Content\ContentTreeBuilderInterface;
use NotACms\Service\Content\ContentTreeProviderInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class StaticUrlCollector implements StaticUrlCollectorInterface
{
    public function __construct(
        private ContentTreeProviderInterface $contentTreeProvider,
        private SiteConfigServiceInterface $siteConfigService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function collect(): StaticUrlCollection
    {
        $urls = [];
        $notes = [];

        foreach ($this->siteConfigService->getLocales() as $locale) {
            $tree = $this->contentTreeProvider->getTree($locale);

            $urls[] = $this->route('home_'.$locale);
            $urls[] = $this->route('blog_list_'.$locale);

            $posts = $tree->getAllPosts();
            $totalPages = (int) ceil(count($posts) / $this->siteConfigService->getPostsPerPage());
            for ($page = 2; $page <= $totalPages; ++$page) {
                $urls[] = $this->route('blog_list_paginated_'.$locale, ['page' => $page]);
            }

            foreach ($posts as $post) {
                $urls[] = $post->url();
            }

            foreach ($tree->getScheduledPosts() as $post) {
                $urls[] = $post->url();
            }

            foreach ($tree->getAllCategories() as $categoryCount) {
                $urls[] = $this->route('blog_category_'.$locale, ['category' => $categoryCount->slug]);
            }

            foreach ($tree->getAllTags() as $tagCount) {
                $urls[] = $this->route('blog_tag_'.$locale, ['tag' => $tagCount->slug]);
            }

            foreach ($tree->getArchiveMonths() as $archiveMonth) {
                $urls[] = $this->route('blog_archive_'.$locale, [
                    'year' => $archiveMonth->year,
                    'month' => sprintf('%02d', $archiveMonth->month),
                ]);
            }

            foreach ($tree->getArchiveYears() as $archiveYear) {
                $urls[] = $this->route('blog_archive_year_'.$locale, ['year' => $archiveYear->year]);
            }

            $homeUrl = $this->route('home_'.$locale);
            foreach ($tree->getPublishableStaticPages($homeUrl) as $page) {
                if ($this->isEmptyCategoryIndex($page, $tree)) {
                    $notes[] = sprintf('skipped empty category index: %s', $page->url());

                    continue;
                }

                $urls[] = $page->url();
            }

            $urls[] = $this->route('search_'.$locale);
        }

        return new StaticUrlCollection(array_values(array_unique($urls)), $notes);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function route(string $name, array $parameters = []): string
    {
        return $this->urlGenerator->generate($name, $parameters);
    }

    private function isEmptyCategoryIndex(ContentItem $contentItem, ContentTree $contentTree): bool
    {
        if (!$contentItem->isIndex() || null === $contentItem->sourcePath || !str_starts_with($contentItem->sourcePath, ContentTreeBuilderInterface::BLOG_CONTENT_PREFIX)) {
            return false;
        }

        return [] === $contentTree->getPostsByCategory($contentItem->slug());
    }
}
