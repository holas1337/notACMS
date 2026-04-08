<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

use NotACms\Content\ContentItem;

final readonly class SidebarData
{
    /**
     * @param ContentItem[]   $recentPosts
     * @param CategoryCount[] $categories
     * @param TagCount[]      $tags
     * @param ArchiveMonth[]  $archiveMonths
     */
    public function __construct(
        public array $recentPosts,
        public array $categories,
        public array $tags,
        public array $archiveMonths,
    ) {
    }
}
