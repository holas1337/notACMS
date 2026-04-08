<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ValueObject\SidebarData;
use NotACms\Service\SiteConfigServiceInterface;

final readonly class SidebarDataProvider implements SidebarDataProviderInterface
{
    public function __construct(
        private ContentServiceInterface $contentService,
        private SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function getData(string $locale): SidebarData
    {
        $contentTree = $this->contentService->getTree($locale);

        return new SidebarData(
            recentPosts: $this->contentService->getRecentPosts($locale, $this->siteConfigService->getRecentPostsLimit()),
            categories: $contentTree->getAllCategories(),
            tags: $contentTree->getAllTags(),
            archiveMonths: $contentTree->getArchiveMonths(),
        );
    }
}
