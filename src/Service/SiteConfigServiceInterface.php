<?php

declare(strict_types=1);

namespace NotACms\Service;

interface SiteConfigServiceInterface
{
    public const string FALLBACK_LOCALE = 'en';

    public const array DEFAULT_IMAGE_VARIANT_WIDTHS = [640, 960];

    public const string DEFAULT_IMAGE_MAGICK_FLAGS = '-strip';

    public const int DEFAULT_POSTS_PER_PAGE = 10;

    public const int DEFAULT_RSS_LIMIT = 20;

    public const int DEFAULT_LLMS_LIMIT = 5;

    public const int DEFAULT_RECENT_POSTS_LIMIT = 6;

    public const int DEFAULT_RELATED_POSTS_LIMIT = 3;

    public const int DEFAULT_IMAGE_QUALITY = 82;

    public const int DEFAULT_NEW_POST_DAYS = 90;

    public const int DEFAULT_COMING_SOON_REVEAL_DAYS = 14;

    public const int DEFAULT_META_DESCRIPTION_LENGTH = 160;

    /**
     * @return string[] Ordered locale codes, first = default
     */
    public function getLocales(): array;

    public function getDefaultLocale(): string;

    /**
     * @return array<string, mixed> Raw _site.yaml "site" block
     */
    public function getSiteConfig(): array;

    public function detectLocaleFromPath(string $path): string;

    public function getUrlPrefix(string $locale): string;

    public function getBaseUrl(): string;

    public function getPostsPerPage(): int;

    public function getRssLimit(): int;

    public function getLlmsLimit(): int;

    public function getRecentPostsLimit(): int;

    public function getRelatedPostsLimit(): int;

    /**
     * @return int[]
     */
    public function getImageVariantWidths(): array;

    public function getImageQuality(): int;

    public function getImageMagickFlags(): string;

    public function getNewPostDays(): int;

    public function getComingSoonRevealDays(): int;

    public function getMetaDescriptionLength(): int;

    public function getContactFormConfig(): ContactFormConfig;
}
