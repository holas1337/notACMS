<?php

declare(strict_types=1);

namespace NotACms\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final class SiteConfigService implements SiteConfigServiceInterface
{
    /** @var array<string, mixed>|null */
    private ?array $config = null;

    /** @var string[]|null */
    private ?array $locales = null;

    public function __construct(
        #[Autowire('%notacms_content%')]
        private readonly string $contentDir,
    ) {
    }

    public function getLocales(): array
    {
        if (null === $this->locales) {
            $locales = $this->load()['locales'] ?? [];
            if (!is_array($locales) || ([] !== $locales && array_is_list($locales))) {
                throw new \RuntimeException('_site.yaml: "locales" must be a map of locale code => settings (e.g. "en: { label: English }"), not a list');
            }

            $this->locales = array_map(strval(...), array_keys($locales));
        }

        return $this->locales;
    }

    public function getDefaultLocale(): string
    {
        $locales = $this->getLocales();

        return $locales[0] ?? self::FALLBACK_LOCALE;
    }

    /**
     * @return array<string, mixed> Raw _site.yaml "site" block
     */
    public function getSiteConfig(): array
    {
        return $this->load();
    }

    public function detectLocaleFromPath(string $path): string
    {
        $default = $this->getDefaultLocale();

        foreach ($this->getLocales() as $locale) {
            if ($default === $locale) {
                continue;
            }

            if (str_starts_with($path, '/'.$locale.'/') || '/'.$locale === $path) {
                return $locale;
            }
        }

        return $default;
    }

    public function getUrlPrefix(string $locale): string
    {
        if ($this->getDefaultLocale() === $locale) {
            return '/';
        }

        return '/'.$locale.'/';
    }

    public function getBaseUrl(): string
    {
        return (string) ($this->load()['base_url'] ?? '');
    }

    public function getPostsPerPage(): int
    {
        return max(1, (int) ($this->load()['posts_per_page'] ?? self::DEFAULT_POSTS_PER_PAGE));
    }

    public function getRssLimit(): int
    {
        return max(1, (int) ($this->load()['rss_limit'] ?? self::DEFAULT_RSS_LIMIT));
    }

    public function getLlmsLimit(): int
    {
        return max(1, (int) ($this->load()['llms_limit'] ?? self::DEFAULT_LLMS_LIMIT));
    }

    public function getRecentPostsLimit(): int
    {
        return max(1, (int) ($this->load()['recent_posts_limit'] ?? self::DEFAULT_RECENT_POSTS_LIMIT));
    }

    public function getRelatedPostsLimit(): int
    {
        return max(1, (int) ($this->load()['related_posts_limit'] ?? self::DEFAULT_RELATED_POSTS_LIMIT));
    }

    public function getImageVariantWidths(): array
    {
        $widths = $this->load()['image_variant_widths'] ?? self::DEFAULT_IMAGE_VARIANT_WIDTHS;

        return array_map(intval(...), (array) $widths);
    }

    public function getImageQuality(): int
    {
        return max(1, (int) ($this->load()['image_quality'] ?? self::DEFAULT_IMAGE_QUALITY));
    }

    public function getImageMagickFlags(): string
    {
        return (string) ($this->load()['image_magick_flags'] ?? self::DEFAULT_IMAGE_MAGICK_FLAGS);
    }

    public function getNewPostDays(): int
    {
        return max(0, (int) ($this->load()['new_post_days'] ?? self::DEFAULT_NEW_POST_DAYS));
    }

    public function getComingSoonRevealDays(): int
    {
        return max(0, (int) ($this->load()['coming_soon_reveal_days'] ?? self::DEFAULT_COMING_SOON_REVEAL_DAYS));
    }

    public function getMetaDescriptionLength(): int
    {
        return max(1, (int) ($this->load()['meta_description_length'] ?? self::DEFAULT_META_DESCRIPTION_LENGTH));
    }

    public function getContactFormConfig(): ContactFormConfig
    {
        $contactFormSettings = $this->load()['contact_form'] ?? [];

        return new ContactFormConfig(
            email: (string) ($contactFormSettings['email'] ?? ''),
            from: (string) ($contactFormSettings['from'] ?? ''),
            fromName: (string) ($contactFormSettings['from_name'] ?? ''),
            topic: (string) ($contactFormSettings['topic'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function load(): array
    {
        if (null === $this->config) {
            $path = $this->contentDir.'/'.self::SITE_CONFIG_FILENAME;

            try {
                $raw = Yaml::parseFile($path);
            } catch (\Throwable $throwable) {
                throw new \RuntimeException(sprintf('Cannot load site config "%s": %s', $path, $throwable->getMessage()), 0, $throwable);
            }

            $this->config = is_array($raw) ? ($raw['site'] ?? []) : [];
        }

        return $this->config;
    }
}
