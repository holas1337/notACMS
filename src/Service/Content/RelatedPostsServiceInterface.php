<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\SiteConfigServiceInterface;

interface RelatedPostsServiceInterface
{
    /** @return ContentItem[] */
    public function getRelatedPosts(ContentTree $contentTree, ContentItem $contentItem, int $limit = SiteConfigServiceInterface::DEFAULT_RELATED_POSTS_LIMIT): array;
}
