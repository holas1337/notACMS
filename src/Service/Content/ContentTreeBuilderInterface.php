<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentTree;

interface ContentTreeBuilderInterface
{
    public const string BLOG_CONTENT_PREFIX = 'blog/';

    public function build(string $locale, bool $includeDrafts = false, bool $includeScheduled = false): ContentTree;
}
