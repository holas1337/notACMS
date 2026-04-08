<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service;

use NotACms\Service\ContactFormConfig;
use NotACms\Service\SiteConfigService;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\TmpDirTrait;
use PHPUnit\Framework\TestCase;

final class SiteConfigServiceTest extends TestCase
{
    use TmpDirTrait;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/notacms_site_config_' . uniqid();
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    private function writeConfig(array $config): void
    {
        $yaml = "site:\n" . $this->arrayToYaml($config, 2);
        file_put_contents($this->tmpDir . '/_site.yaml', $yaml);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function arrayToYaml(array $data, int $indent): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            $prefix = str_repeat(' ', $indent);
            if (is_array($value)) {
                $lines[] = $prefix . $key . ':';
                $lines[] = $this->arrayToYaml($value, $indent + 2);
            } elseif (is_bool($value)) {
                $lines[] = $prefix . $key . ': ' . ($value ? 'true' : 'false');
            } elseif (is_int($value)) {
                $lines[] = $prefix . $key . ': ' . $value;
            } else {
                $lines[] = $prefix . $key . ': "' . $value . '"';
            }
        }

        return implode("\n", $lines);
    }

    private function createService(): SiteConfigService
    {
        return new SiteConfigService($this->tmpDir);
    }

    // ===== Locales =====

    public function testGetLocalesReturnsOrderedKeys(): void
    {
        $this->writeConfig([
            'locales' => [
                'en' => ['label' => 'English'],
                'pl' => ['label' => 'Polski'],
            ],
        ]);

        $service = $this->createService();

        self::assertSame(['en', 'pl'], $service->getLocales());
    }

    public function testGetLocalesCachesResult(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English']],
        ]);

        $service = $this->createService();
        $first = $service->getLocales();

        // Mutate file — should not affect cached result
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'de' => ['label' => 'Deutsch']],
        ]);

        self::assertSame($first, $service->getLocales());
    }

    public function testGetDefaultLocaleReturnsFirstLocale(): void
    {
        $this->writeConfig([
            'locales' => [
                'pl' => ['label' => 'Polski'],
                'en' => ['label' => 'English'],
            ],
        ]);

        $service = $this->createService();

        self::assertSame('pl', $service->getDefaultLocale());
    }

    public function testGetDefaultLocaleFallsBackToEn(): void
    {
        $this->writeConfig([]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::FALLBACK_LOCALE, $service->getDefaultLocale());
    }

    public function testGetLocaleConfigReturnsConfigForLocale(): void
    {
        $this->writeConfig([
            'locales' => [
                'en' => ['label' => 'English', 'og_locale' => 'en_US'],
                'pl' => ['label' => 'Polski', 'og_locale' => 'pl_PL'],
            ],
        ]);

        $service = $this->createService();

        self::assertSame(['label' => 'Polski', 'og_locale' => 'pl_PL'], $service->getLocaleConfig('pl'));
    }

    public function testGetLocaleConfigReturnsEmptyForUnknownLocale(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English']],
        ]);

        $service = $this->createService();

        self::assertSame([], $service->getLocaleConfig('de'));
    }

    public function testGetSiteConfigReturnsFullConfig(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English']],
            'base_url' => 'https://example.com',
        ]);

        $service = $this->createService();

        $config = $service->getSiteConfig();
        self::assertSame(['en' => ['label' => 'English']], $config['locales']);
        self::assertSame('https://example.com', $config['base_url']);
    }

    // ===== URL & Locale Detection =====

    public function testDetectLocaleFromPathReturnsDefaultForRoot(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        $service = $this->createService();

        self::assertSame('en', $service->detectLocaleFromPath('/'));
    }

    public function testDetectLocaleFromPathReturnsLocaleForPrefixedPath(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        $service = $this->createService();

        self::assertSame('pl', $service->detectLocaleFromPath('/pl/wpisy/'));
    }

    public function testDetectLocaleFromPathReturnsDefaultForUnknownPrefix(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        $service = $this->createService();

        self::assertSame('en', $service->detectLocaleFromPath('/de/'));
    }

    public function testDetectLocaleFromPathMatchesExactLocalePath(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        $service = $this->createService();

        self::assertSame('pl', $service->detectLocaleFromPath('/pl'));
    }

    public function testGetUrlPrefixReturnsSlashForDefault(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        $service = $this->createService();

        self::assertSame('/', $service->getUrlPrefix('en'));
    }

    public function testGetUrlPrefixReturnsLocalePrefixForNonDefault(): void
    {
        $this->writeConfig([
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        $service = $this->createService();

        self::assertSame('/pl/', $service->getUrlPrefix('pl'));
    }

    public function testGetBaseUrlReturnsConfiguredUrl(): void
    {
        $this->writeConfig([
            'locales' => ['en' => []],
            'base_url' => 'https://myblog.dev',
        ]);

        $service = $this->createService();

        self::assertSame('https://myblog.dev', $service->getBaseUrl());
    }

    public function testGetBaseUrlReturnsEmptyWhenNotSet(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame('', $service->getBaseUrl());
    }

    // ===== Numeric Config =====

    public function testGetPostsPerPageReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'posts_per_page' => 5]);

        $service = $this->createService();

        self::assertSame(5, $service->getPostsPerPage());
    }

    public function testGetPostsPerPageReturnsDefault(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::DEFAULT_POSTS_PER_PAGE, $service->getPostsPerPage());
    }

    public function testGetRssLimitReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'rss_limit' => 5]);

        $service = $this->createService();

        self::assertSame(5, $service->getRssLimit());
    }

    public function testGetRecentPostsLimitReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'recent_posts_limit' => 3]);

        $service = $this->createService();

        self::assertSame(3, $service->getRecentPostsLimit());
    }

    public function testGetRelatedPostsLimitReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'related_posts_limit' => 5]);

        $service = $this->createService();

        self::assertSame(5, $service->getRelatedPostsLimit());
    }

    public function testGetImageVariantWidthsReturnsConfiguredValues(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'image_variant_widths' => [320, 640, 1280]]);

        $service = $this->createService();

        self::assertSame([320, 640, 1280], $service->getImageVariantWidths());
    }

    public function testGetImageVariantWidthsReturnsDefault(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::DEFAULT_IMAGE_VARIANT_WIDTHS, $service->getImageVariantWidths());
    }

    public function testGetImageQualityReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'image_quality' => 90]);

        $service = $this->createService();

        self::assertSame(90, $service->getImageQuality());
    }

    public function testGetImageQualityReturnsDefault(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::DEFAULT_IMAGE_QUALITY, $service->getImageQuality());
    }

    public function testGetImageMagickFlagsReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'image_magick_flags' => '-strip -quality 90']);

        $service = $this->createService();

        self::assertSame('-strip -quality 90', $service->getImageMagickFlags());
    }

    public function testGetImageMagickFlagsReturnsDefault(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::DEFAULT_IMAGE_MAGICK_FLAGS, $service->getImageMagickFlags());
    }

    public function testGetNewPostDaysReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'new_post_days' => 30]);

        $service = $this->createService();

        self::assertSame(30, $service->getNewPostDays());
    }

    public function testGetNewPostDaysReturnsDefault(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::DEFAULT_NEW_POST_DAYS, $service->getNewPostDays());
    }

    public function testGetComingSoonRevealDaysReturnsConfiguredValue(): void
    {
        $this->writeConfig(['locales' => ['en' => []], 'coming_soon_reveal_days' => 7]);

        $service = $this->createService();

        self::assertSame(7, $service->getComingSoonRevealDays());
    }

    public function testGetComingSoonRevealDaysReturnsDefault(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();

        self::assertSame(SiteConfigServiceInterface::DEFAULT_COMING_SOON_REVEAL_DAYS, $service->getComingSoonRevealDays());
    }

    // ===== Contact Form Config =====

    public function testGetContactFormConfigReturnsConfiguredValues(): void
    {
        $this->writeConfig([
            'locales' => ['en' => []],
            'contact_form' => [
                'email' => 'test@example.com',
                'from' => 'noreply@example.com',
                'from_name' => 'My Site',
                'topic' => 'Contact',
            ],
        ]);

        $service = $this->createService();
        $config = $service->getContactFormConfig();

        self::assertInstanceOf(ContactFormConfig::class, $config);
        self::assertSame('test@example.com', $config->email);
        self::assertSame('noreply@example.com', $config->from);
        self::assertSame('My Site', $config->fromName);
        self::assertSame('Contact', $config->topic);
    }

    public function testGetContactFormConfigReturnsEmptyWhenNotSet(): void
    {
        $this->writeConfig(['locales' => ['en' => []]]);

        $service = $this->createService();
        $config = $service->getContactFormConfig();

        self::assertSame('', $config->email);
        self::assertSame('', $config->from);
        self::assertSame('', $config->fromName);
        self::assertSame('', $config->topic);
    }
}
