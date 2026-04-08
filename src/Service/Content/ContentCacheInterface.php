<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

interface ContentCacheInterface
{
    public const string CACHE_KEY_PREFIX = 'content_tree_';

    public function invalidateCache(string $locale): void;
}
