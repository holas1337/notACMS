<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Service\SiteConfigServiceInterface;

interface ContentServiceInterface
{
    public function findByUrl(string $url, string $locale): ?ContentItem;

    public function findByDirectoryKey(string $directoryKey, string $locale): ?ContentItem;

    public function findPostBySlug(string $slug, string $locale): ?ContentItem;

    public function findScheduledPostBySlug(string $slug, string $locale): ?ContentItem;

    /**
     * @return ContentItem[]
     */
    public function getPosts(string $locale, int $page = 1, int $perPage = SiteConfigServiceInterface::DEFAULT_POSTS_PER_PAGE): array;

    public function getTotalPosts(string $locale): int;

    /**
     * @return ContentItem[]
     */
    public function getRecentPosts(string $locale, int $limit = SiteConfigServiceInterface::DEFAULT_RECENT_POSTS_LIMIT): array;

    /**
     * @return ContentItem[]
     */
    public function getPostsByCategory(string $category, string $locale): array;

    /**
     * @return ContentItem[]
     */
    public function getPostsByTag(string $tag, string $locale): array;

    /**
     * @return ContentItem[]
     */
    public function getPostsByYearMonth(int $year, int $month, string $locale): array;

    /**
     * @return ContentItem[]
     */
    public function getPostsByYear(int $year, string $locale): array;

    /**
     * @return array<string, array<string, string>>
     */
    public function getTranslationMap(): array;
}
