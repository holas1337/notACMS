<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;

interface ContentServiceInterface
{
    public function getTree(string $locale): ContentTree;

    public function findByUrl(string $url, string $locale): ?ContentItem;

    public function findPostBySlug(string $slug, string $locale): ?ContentItem;

    public function findScheduledPostBySlug(string $slug, string $locale): ?ContentItem;

    /**
     * @return ContentItem[]
     */
    public function getPosts(string $locale, int $page = 1, int $perPage = 10): array;

    public function getTotalPosts(string $locale): int;

    /**
     * @return ContentItem[]
     */
    public function getRecentPosts(string $locale, int $limit = 5): array;

    /**
     * @return array<string, array<string, string>>
     */
    public function getTranslationMap(): array;
}
