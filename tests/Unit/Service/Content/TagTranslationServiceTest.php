<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service\Content;

use NotACms\Service\Content\TagTranslationService;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\TmpDirTrait;
use PHPUnit\Framework\TestCase;

final class TagTranslationServiceTest extends TestCase
{
    use TmpDirTrait;
    private string $tmpDir;

    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/notacms_tag_trans_' . uniqid();
        mkdir($this->tmpDir);

        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getDefaultLocale')
            ->willReturn('en');
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    private function writeTagsYaml(array $data): void
    {
        $yaml = $this->arrayToYaml($data, 0);
        file_put_contents($this->tmpDir . '/_tags.yaml', $yaml);
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
            } else {
                $lines[] = $prefix . $key . ': "' . $value . '"';
            }
        }

        return implode("\n", $lines);
    }

    private function createService(): TagTranslationService
    {
        return new TagTranslationService($this->siteConfigService, $this->tmpDir);
    }

    // ===== Same Locale =====

    public function testReturnsTagUnchangedForSameLocale(): void
    {
        $service = $this->createService();

        self::assertSame('php', $service->translate('php', 'en', 'en'));
    }

    // ===== Default → Other Locale =====

    public function testTranslatesFromDefaultToOtherLocale(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl'],
        ]);

        $service = $this->createService();

        self::assertSame('php-pl', $service->translate('php', 'en', 'pl'));
    }

    public function testReturnsOriginalTagWhenNoTranslationExists(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl'],
        ]);

        $service = $this->createService();

        self::assertSame('javascript', $service->translate('javascript', 'en', 'pl'));
    }

    // ===== Other Locale → Default =====

    public function testTranslatesFromOtherToDefaultLocale(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl'],
        ]);

        $service = $this->createService();

        self::assertSame('php', $service->translate('php-pl', 'pl', 'en'));
    }

    // ===== Other Locale → Other Locale (two-hop) =====

    public function testTranslatesViaCanonicalForOtherToOther(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl', 'de' => 'php-de'],
        ]);

        $service = $this->createService();

        self::assertSame('php-de', $service->translate('php-pl', 'pl', 'de'));
    }

    public function testReturnsOriginalTagWhenTargetTranslationMissing(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl'],
        ]);

        $service = $this->createService();

        self::assertSame('php-pl', $service->translate('php-pl', 'pl', 'de'));
    }

    // ===== Missing File =====

    public function testReturnsOriginalTagWhenTagsFileMissing(): void
    {
        $service = $this->createService();

        self::assertSame('php', $service->translate('php', 'en', 'pl'));
    }

    // ===== Caching =====

    public function testCachesTranslationMap(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl'],
        ]);

        $service = $this->createService();
        $first = $service->translate('php', 'en', 'pl');

        // Mutate file — should not affect cached result
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl-changed'],
        ]);

        self::assertSame($first, $service->translate('php', 'en', 'pl'));
    }

    // ===== Multiple Tags =====

    public function testHandlesMultipleTagTranslations(): void
    {
        $this->writeTagsYaml([
            'php' => ['pl' => 'php-pl'],
            'symfony' => ['pl' => 'symfony-pl'],
        ]);

        $service = $this->createService();

        self::assertSame('php-pl', $service->translate('php', 'en', 'pl'));
        self::assertSame('symfony-pl', $service->translate('symfony', 'en', 'pl'));
    }
}
