<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;

interface RelatedPostsServiceInterface
{
    /** @return ContentItem[] */
    public function getRelatedPosts(ContentTree $contentTree, ContentItem $contentItem, int $limit = 3): array;
}
