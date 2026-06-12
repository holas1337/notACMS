<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ValueObject\SidebarData;
use NotACms\Service\SiteSettingsInterface;

final readonly class SidebarDataProvider implements SidebarDataProviderInterface
{
    public function __construct(
        private ContentServiceInterface $contentService,
        private ContentTreeProviderInterface $contentTreeProvider,
        private SiteSettingsInterface $siteSettings,
    ) {
    }

    public function getData(string $locale): SidebarData
    {
        $contentTree = $this->contentTreeProvider->getTree($locale);

        return new SidebarData(
            recentPosts: $this->contentService->getRecentPosts($locale, $this->siteSettings->getRecentPostsLimit()),
            categories: $contentTree->getAllCategories(),
            tags: $contentTree->getAllTags(),
            archiveMonths: $contentTree->getArchiveMonths(),
        );
    }
}
