<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

use NotACms\Content\ContentItem;

final readonly class AdjacentPosts
{
    public function __construct(
        public ?ContentItem $prev,
        public ?ContentItem $next,
    ) {
    }
}
