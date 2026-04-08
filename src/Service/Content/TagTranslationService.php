<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final class TagTranslationService implements TagTranslationServiceInterface
{
    /** @var array<string, array<string, string>>|null */
    private ?array $tagTranslations = null;

    public function __construct(
        private readonly SiteConfigServiceInterface $siteConfigService,
        #[Autowire('%notacms_content%')]
        private readonly string $contentDir,
    ) {
    }

    public function translate(string $tag, string $fromLocale, string $toLocale): string
    {
        if ($toLocale === $fromLocale) {
            return $tag;
        }

        $map = $this->loadTagTranslations();
        $defaultLocale = $this->siteConfigService->getDefaultLocale();

        if ($defaultLocale === $fromLocale) {
            return $map[$tag][$toLocale] ?? $tag;
        }

        foreach ($map as $canonical => $translations) {
            if (($translations[$fromLocale] ?? null) === $tag) {
                if ($defaultLocale === $toLocale) {
                    return $canonical;
                }

                return $translations[$toLocale] ?? $tag;
            }
        }

        return $tag;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function loadTagTranslations(): array
    {
        if (null !== $this->tagTranslations) {
            return $this->tagTranslations;
        }

        $path = $this->contentDir.'/_tags.yaml';
        if (!file_exists($path)) {
            return $this->tagTranslations = [];
        }

        $data = Yaml::parseFile($path);

        return $this->tagTranslations = is_array($data) ? $data : [];
    }
}
