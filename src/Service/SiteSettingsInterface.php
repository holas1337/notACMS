<?php

declare(strict_types=1);

namespace NotACms\Service;

interface SiteSettingsInterface
{
    public const string SITE_CONFIG_FILENAME = '_site.yaml';

    public const int DEFAULT_POSTS_PER_PAGE = 10;

    public const int DEFAULT_RSS_LIMIT = 20;

    public const int DEFAULT_LLMS_LIMIT = 5;

    public const int DEFAULT_RECENT_POSTS_LIMIT = 6;

    public const int DEFAULT_RELATED_POSTS_LIMIT = 3;

    public const int DEFAULT_NEW_POST_DAYS = 90;

    public const int DEFAULT_COMING_SOON_REVEAL_DAYS = 14;

    public const int DEFAULT_META_DESCRIPTION_LENGTH = 160;

    /**
     * @return array<string, mixed> Raw _site.yaml "site" block
     */
    public function getSiteConfig(): array;

    public function getBaseUrl(): string;

    public function getPostsPerPage(): int;

    public function getRssLimit(): int;

    public function getLlmsLimit(): int;

    public function getRecentPostsLimit(): int;

    public function getRelatedPostsLimit(): int;

    public function getNewPostDays(): int;

    public function getComingSoonRevealDays(): int;

    public function getMetaDescriptionLength(): int;

    public function getContactFormConfig(): ContactFormConfig;
}
